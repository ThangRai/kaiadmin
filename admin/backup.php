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

// Đường dẫn thư mục sao lưu
$backup_dir = __DIR__ . '/backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Hàm tạo file sao lưu
function createBackup($pdo, $backup_dir, $download = false) {
    try {
        // Lấy tên database
        $db_name = $pdo->query("SELECT DATABASE()")->fetchColumn();
        $backup_file = $backup_dir . 'backup_' . $db_name . '_' . date('Ymd_His') . '.sql';
        $zip_file = str_replace('.sql', '.zip', $backup_file);

        // Tạo nội dung file SQL
        $output = "-- Database Backup\n";
        $output .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: $db_name\n\n";
        $output .= "SET NAMES utf8mb4;\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        // Lấy danh sách bảng
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            // Lấy cấu trúc bảng
            $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            $output .= "-- Table structure for `$table`\n";
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $create_table[1] . ";\n\n";

            // Lấy dữ liệu bảng
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $output .= "-- Dumping data for `$table`\n";
                $output .= "INSERT INTO `$table` (`" . implode('`, `', array_keys($rows[0])) . "`) VALUES\n";
                $row_count = count($rows);
                foreach ($rows as $i => $row) {
                    $values = array_map(function($value) use ($pdo) {
                        return is_null($value) ? 'NULL' : $pdo->quote($value);
                    }, array_values($row));
                    $output .= "(" . implode(", ", $values) . ")";
                    $output .= ($i < $row_count - 1) ? ",\n" : ";\n\n";
                }
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        // Lưu file SQL
        file_put_contents($backup_file, $output);

        // Nén thành file ZIP nếu có ZipArchive
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($backup_file, basename($backup_file));
                $zip->close();
                unlink($backup_file); // Xóa file SQL sau khi nén
                $final_file = $zip_file;
            } else {
                $final_file = $backup_file;
            }
        } else {
            $final_file = $backup_file;
        }

        // Tải file trực tiếp nếu yêu cầu
        if ($download) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($final_file) . '"');
            header('Content-Length: ' . filesize($final_file));
            readfile($final_file);
            exit;
        }

        return ['success' => true, 'message' => 'Sao lưu thành công!', 'file' => $final_file];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi khi sao lưu: ' . $e->getMessage()];
    }
}

// Xử lý yêu cầu sao lưu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_backup'])) {
    $download = isset($_POST['download']) && $_POST['download'] == '1';
    $result = createBackup($pdo, $backup_dir, $download);

    if (!$download) {
        $_SESSION['result_message'] = $result['message'];
        $_SESSION['result_type'] = $result['success'] ? 'success' : 'error';
        header("Location: backup.php");
        exit;
    }
}

// Xử lý xóa file sao lưu
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $file_path = $backup_dir . $file;
    if (file_exists($file_path)) {
        unlink($file_path);
        $_SESSION['result_message'] = 'Xóa file sao lưu thành công!';
        $_SESSION['result_type'] = 'success';
    } else {
        $_SESSION['result_message'] = 'File không tồn tại!';
        $_SESSION['result_type'] = 'error';
    }
    header("Location: backup.php");
    exit;
}

// Lấy danh sách file sao lưu
$backup_files = array_diff(scandir($backup_dir), ['.', '..']);
usort($backup_files, function($a, $b) use ($backup_dir) {
    return filemtime($backup_dir . $b) - filemtime($backup_dir . $a);
});
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Sao lưu Cơ sở dữ liệu - Kaiadmin</title>
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon">
    <!-- Fonts and icons -->
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
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <!-- iZitoast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <style>
        .table-responsive {
            margin-top: 20px;
        }
        .backup-btn {
            margin-bottom: 20px;
        }
        .file-size {
            color: #6c757d;
        }
        .action-btns a {
            margin-right: 5px;
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
                        <h3 class="fw-bold mb-3">Sao lưu Cơ sở dữ liệu</h3>
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
                                <a href="#">Sao lưu</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Quản lý sao lưu</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="backup-btn">
                                        <div class="form-group">
                                            <label>Tùy chọn sao lưu</label>
                                            <div class="form-check">
                                                <input type="checkbox" name="download" value="1" class="form-check-input" id="download">
                                                <label class="form-check-label" for="download">Tải file sao lưu ngay</label>
                                            </div>
                                        </div>
                                        <button type="submit" name="submit_backup" class="btn btn-primary">Sao lưu ngay</button>
                                    </form>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Tên file</th>
                                                    <th>Ngày tạo</th>
                                                    <th>Kích thước</th>
                                                    <th>Hành động</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($backup_files as $file): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($file); ?></td>
                                                        <td><?php echo date('d/m/Y H:i:s', filemtime($backup_dir . $file)); ?></td>
                                                        <td class="file-size"><?php echo round(filesize($backup_dir . $file) / 1024, 2); ?> KB</td>
                                                        <td class="action-btns">
                                                            <a href="backups/<?php echo urlencode($file); ?>" class="btn btn-sm btn-success" download>Tải về</a>
                                                            <a href="backup.php?delete=<?php echo urlencode($file); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa file này?')">Xóa</a>
                                                        </td>
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
    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo DataTable
            $('table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
                }
            });

            // iZitoast notification
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