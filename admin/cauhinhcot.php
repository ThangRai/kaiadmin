<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'database/config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check database connection
if (!$pdo) {
    $_SESSION['toast_message'] = 'Không thể kết nối đến cơ sở dữ liệu!';
    $_SESSION['toast_type'] = 'error';
    header("Location: cauhinhcot.php");
    exit;
}

// Fetch all column settings
function getAllColumnSettings($pdo) {
    $stmt = $pdo->query("SELECT * FROM column_settings ORDER BY content_type ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle save configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content_type'])) {
    $content_type = $_POST['content_type'];
    $items_per_row_tiny = isset($_POST['items_per_row_tiny']) ? (int)$_POST['items_per_row_tiny'] : null;
    $items_per_row_sm = isset($_POST['items_per_row_sm']) ? (int)$_POST['items_per_row_sm'] : null;
    $items_per_row_md = isset($_POST['items_per_row_md']) ? (int)$_POST['items_per_row_md'] : null;
    $items_per_row_lg = isset($_POST['items_per_row_lg']) ? (int)$_POST['items_per_row_lg'] : null;
    $items_per_row_xl = isset($_POST['items_per_row_xl']) ? (int)$_POST['items_per_row_xl'] : null;

    try {
        $update_fields = [];
        $params = [];

        // Build update fields and parameters
        if ($items_per_row_tiny !== null) {
            $update_fields[] = "items_per_row_tiny = ?";
            $params[] = $items_per_row_tiny;
        }
        if ($items_per_row_sm !== null) {
            $update_fields[] = "items_per_row_sm = ?";
            $params[] = $items_per_row_sm;
        }
        if ($items_per_row_md !== null) {
            $update_fields[] = "items_per_row_md = ?";
            $params[] = $items_per_row_md;
        }
        if ($items_per_row_lg !== null) {
            $update_fields[] = "items_per_row_lg = ?";
            $params[] = $items_per_row_lg;
        }
        if ($items_per_row_xl !== null) {
            $update_fields[] = "items_per_row_xl = ?";
            $params[] = $items_per_row_xl;
        }

        // Only execute update if there are fields to update
        if (!empty($update_fields)) {
            $query = "UPDATE column_settings SET " . implode(", ", $update_fields) . ", updated_at = NOW() WHERE content_type = ?";
            $params[] = $content_type; // Add content_type for WHERE clause
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $_SESSION['toast_message'] = 'Cấu hình cột đã được cập nhật thành công!';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Không có thay đổi nào được thực hiện!';
            $_SESSION['toast_type'] = 'warning';
        }
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi lưu cấu hình: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }

    header("Location: cauhinhcot.php");
    exit;
}

// Fetch all settings for display
$column_settings = getAllColumnSettings($pdo);

// Available content types
$content_types = [
    'products' => 'Sản phẩm',
    'services' => 'Dịch vụ',
    'blog' => 'Blog',
    'projects' => 'Dự án',
    'partners' => 'Đối tác',
    'gallery' => 'Thư viện ảnh',
    'video' => 'Video',
    'customer_feedback' => 'Ý kiến khách hàng'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Cấu hình Website - Kaiadmin</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css" />
    <style>
        .form-group label { font-weight: 500; }
        .card-header { background-color: #f8f9fa; }
        .table-responsive { margin-bottom: 1rem; }
        .input-number { width: 80px; }
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
                        <h3 class="fw-bold mb-3">Cấu hình cột hiển thị</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="../index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Cấu hình cột</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Danh sách cấu hình cột</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Loại nội dung</th>
                                                    <th>576px</th>
                                                    <th>≥576px</th>
                                                    <th>≥768px</th>
                                                    <th>≥992px</th>
                                                    <th>≥1200px</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($column_settings as $setting): ?>
                                                    <tr>
                                                        <td><?php echo isset($content_types[$setting['content_type']]) ? $content_types[$setting['content_type']] : $setting['content_type']; ?></td>
                                                        <td>
                                                            <form method="POST" action="cauhinhcot.php">
                                                                <input type="hidden" name="content_type" value="<?php echo $setting['content_type']; ?>">
                                                                <input type="number" name="items_per_row_tiny" class="form-control input-number" value="<?php echo $setting['items_per_row_tiny']; ?>" min="1" max="6" onchange="this.form.submit()">
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <form method="POST" action="cauhinhcot.php">
                                                                <input type="hidden" name="content_type" value="<?php echo $setting['content_type']; ?>">
                                                                <input type="number" name="items_per_row_sm" class="form-control input-number" value="<?php echo $setting['items_per_row_sm']; ?>" min="1" max="6" onchange="this.form.submit()">
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <form method="POST" action="cauhinhcot.php">
                                                                <input type="hidden" name="content_type" value="<?php echo $setting['content_type']; ?>">
                                                                <input type="number" name="items_per_row_md" class="form-control input-number" value="<?php echo $setting['items_per_row_md']; ?>" min="1" max="6" onchange="this.form.submit()">
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <form method="POST" action="cauhinhcot.php">
                                                                <input type="hidden" name="content_type" value="<?php echo $setting['content_type']; ?>">
                                                                <input type="number" name="items_per_row_lg" class="form-control input-number" value="<?php echo $setting['items_per_row_lg']; ?>" min="1" max="6" onchange="this.form.submit()">
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <form method="POST" action="cauhinhcot.php">
                                                                <input type="hidden" name="content_type" value="<?php echo $setting['content_type']; ?>">
                                                                <input type="number" name="items_per_row_xl" class="form-control input-number" value="<?php echo $setting['items_per_row_xl']; ?>" min="1" max="6" onchange="this.form.submit()">
                                                            </form>
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
            <?php include 'include/footer.php'; ?>
                    <?php include 'include/custom-template.php'; ?>

        </div>
    </div>

    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/world.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
    <script src="assets/js/setting-demo.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/js/bootstrap-switch.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
                iziToast.<?php echo $_SESSION['toast_type']; ?>({
                    title: '<?php echo $_SESSION['toast_type'] === 'success' ? 'Thành công!' : ($_SESSION['toast_type'] === 'warning' ? 'Cảnh báo!' : 'Lỗi!'); ?>',
                    message: "<?php echo htmlspecialchars($_SESSION['toast_message']); ?>",
                    position: 'topRight',
                    timeout: 6000
                });
                <?php unset($_SESSION['toast_message']); unset($_SESSION['toast_type']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>