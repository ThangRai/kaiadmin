<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'database/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin phiên bản hiện tại
$current_version = '1.0.0'; // Phải khớp với tag Git
$db_version = $pdo->query("SELECT version FROM migrations ORDER BY id DESC LIMIT 1")->fetchColumn() ?: '0.0.0';

// Hàm chạy migration
function runMigration($pdo, $version, $sql, $description) {
    try {
        $pdo->exec($sql);
        $stmt = $pdo->prepare("INSERT INTO migrations (version, description) VALUES (?, ?)");
        $stmt->execute([$version, $description]);
        return ['success' => true, 'message' => "Áp dụng phiên bản $version thành công!"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Lỗi khi áp dụng $version: " . $e->getMessage()];
    }
}

// Danh sách migrations (thêm các phiên bản mới vào đây)
$migrations = [
    '1.0.1' => [
        'sql' => "
            ALTER TABLE settings ADD COLUMN site_mode VARCHAR(20) DEFAULT 'production';
            INSERT INTO settings (`key`, value) VALUES ('site_version', '1.0.1');
        ",
        'description' => 'Thêm cột site_mode vào bảng settings và cập nhật site_version.'
    ],
    '1.0.2' => [
        'sql' => "
            CREATE TABLE logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                action VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ",
        'description' => 'Tạo bảng logs để ghi nhật ký hành động.'
    ]
];

// Xử lý áp dụng migration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_migration'])) {
    $version = $_POST['version'];
    if (isset($migrations[$version])) {
        $result = runMigration($pdo, $version, $migrations[$version]['sql'], $migrations[$version]['description']);
        $_SESSION['result_message'] = $result['message'];
        $_SESSION['result_type'] = $result['success'] ? 'success' : 'error';
        header("Location: version.php");
        exit;
    }
}

// Lấy lịch sử migrations
$stmt = $pdo->query("SELECT * FROM migrations ORDER BY id DESC");
$migration_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy thông tin Git (nếu có)
$git_version = '';
$git_branch = '';
if (is_dir(__DIR__ . '/.git')) {
    $git_version = trim(shell_exec('git describe --tags --abbrev=0 2> /dev/null')) ?: 'N/A';
    $git_branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD 2> /dev/null')) ?: 'N/A';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phiên bản - Kaiadmin</title>
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon">
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: {"families":["Public Sans:300,400,500,600,700"]},
            custom: {"families":["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['assets/css/fonts.min.css']},
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <style>
        .table-responsive {
            margin-top: 20px;
        }
        .version-info {
            margin-bottom: 20px;
        }
        .migration-btn {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'include/sidebar.php'; ?>
        <div class="main-panel">
            <?php include 'include/header.php'; ?>
            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Quản lý phiên bản</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="index.php">
                                    <i class="icon-home"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Phiên bản</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Thông tin phiên bản</h4>
                                </div>
                                <div class="card-body">
                                    <div class="version-info">
                                        <p><strong>Phiên bản website:</strong> <?php echo htmlspecialchars($current_version); ?></p>
                                        <p><strong>Phiên bản database:</strong> <?php echo htmlspecialchars($db_version); ?></p>
                                        <p><strong>Git Tag:</strong> <?php echo htmlspecialchars($git_version); ?></p>
                                        <p><strong>Git Branch:</strong> <?php echo htmlspecialchars($git_branch); ?></p>
                                    </div>
                                    <h5>Các bản cập nhật có sẵn</h5>
                                    <?php foreach ($migrations as $version => $migration): ?>
                                        <?php if (version_compare($version, $db_version, '>')): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="version" value="<?php echo htmlspecialchars($version); ?>">
                                                <span>Phiên bản <?php echo htmlspecialchars($version); ?>: <?php echo htmlspecialchars($migration['description']); ?></span>
                                                <button type="submit" name="apply_migration" class="btn btn-primary btn-sm migration-btn">Áp dụng</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <div class="table-responsive">
                                        <h5>Lịch sử cập nhật database</h5>
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Phiên bản</th>
                                                    <th>Mô tả</th>
                                                    <th>Thời gian</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($migration_history as $migration): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($migration['version']); ?></td>
                                                        <td><?php echo htmlspecialchars($migration['description']); ?></td>
                                                        <td><?php echo date('d/m/Y H:i:s', strtotime($migration['applied_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'include/custom-template.php'; ?>
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
            },
            });

            <?php if (isset($_SESSION['result_message']) && isset($_SESSION['result_type'])): ?>
                iziToast.<?php echo $_SESSION['result_type']; ?>({
                    title: '<?php echo $_SESSION['result_type'] === 'success' ? 'Thành công' : 'Lỗi'; ?>',
                    message: '<?php echo $_SESSION['result_message']; ?>',
                    position: 'topRight',
                    timeout: 6000
                });
                <?php
                unset($_SESSION['result_message']);
                unset($_SESSION['result_type']);
                ?>
            <?php endif; ?>
        });
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>