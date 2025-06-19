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
    header("Location: product.php");
    exit;
}

// Fetch existing settings
function getColumnSettings($content_type, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM column_settings WHERE content_type = ? LIMIT 1");
    $stmt->execute([$content_type]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        return [
            'id' => null,
            'content_type' => $content_type,
            'items_per_row_tiny' => 2,
            'items_per_row_sm' => 2,
            'items_per_row_md' => 2,
            'items_per_row_lg' => 3,
            'items_per_row_xl' => 6,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    return $settings;
}

// Handle save configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content_type'])) {
    $content_type = $_POST['content_type'];
    $items_per_row_tiny = isset($_POST['items_per_row_tiny']) ? (int)$_POST['items_per_row_tiny'] : 2;
    $items_per_row_sm = isset($_POST['items_per_row_sm']) ? (int)$_POST['items_per_row_sm'] : 2;
    $items_per_row_md = isset($_POST['items_per_row_md']) ? (int)$_POST['items_per_row_md'] : 2;
    $items_per_row_lg = isset($_POST['items_per_row_lg']) ? (int)$_POST['items_per_row_lg'] : 3;
    $items_per_row_xl = isset($_POST['items_per_row_xl']) ? (int)$_POST['items_per_row_xl'] : 6;

    try {
        $stmt = $pdo->prepare("INSERT INTO column_settings (content_type, items_per_row_tiny, items_per_row_sm, items_per_row_md, items_per_row_lg, items_per_row_xl) 
                               VALUES (?, ?, ?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE items_per_row_tiny = VALUES(items_per_row_tiny), 
                                                       items_per_row_sm = VALUES(items_per_row_sm), 
                                                       items_per_row_md = VALUES(items_per_row_md), 
                                                       items_per_row_lg = VALUES(items_per_row_lg), 
                                                       items_per_row_xl = VALUES(items_per_row_xl), 
                                                       updated_at = NOW()");
        $stmt->execute([$content_type, $items_per_row_tiny, $items_per_row_sm, $items_per_row_md, $items_per_row_lg, $items_per_row_xl]);

        $_SESSION['toast_message'] = 'Cấu hình cột đã được lưu thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi lưu cấu hình: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }

    header("Location: cauhinhcot.php");
    exit;
}

// Fetch settings for display
$column_settings = getColumnSettings('products', $pdo);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Cấu hình Website - Kaiadmin</title>
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
    <!-- Bootstrap Colorpicker CSS -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.4.0/css/bootstrap-colorpicker.min.css"> -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css" />
    <style>
        .form-group label { font-weight: 500; }
        .card-header { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'include/sidebar.php'; // Đường dẫn từ admin/ đến admin/include/ ?>
        <div class="main-panel">
            <?php include 'include/header.php'; // Đường dẫn từ admin/ lên thư mục gốc rồi vào include/ ?>
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
                                    <h4 class="card-title">Cấu hình cột cho Sản phẩm</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="cauhinhcot.php">
                                        <input type="hidden" name="content_type" value="products">
                                        <div class="row">
                                            <div class="col-md-2 form-group">
                                                <label for="items_per_row_tiny">576px</label>
                                                <input type="number" id="items_per_row_tiny" name="items_per_row_tiny" class="form-control" value="<?php echo $column_settings['items_per_row_tiny']; ?>" min="1" max="6" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label for="items_per_row_sm">≥576px</label>
                                                <input type="number" id="items_per_row_sm" name="items_per_row_sm" class="form-control" value="<?php echo $column_settings['items_per_row_sm']; ?>" min="1" max="6" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label for="items_per_row_md">≥768px</label>
                                                <input type="number" id="items_per_row_md" name="items_per_row_md" class="form-control" value="<?php echo $column_settings['items_per_row_md']; ?>" min="1" max="6" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label for="items_per_row_lg">≥992px</label>
                                                <input type="number" id="items_per_row_lg" name="items_per_row_lg" class="form-control" value="<?php echo $column_settings['items_per_row_lg']; ?>" min="1" max="6" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label for="items_per_row_xl">≥1200px</label>
                                                <input type="number" id="items_per_row_xl" name="items_per_row_xl" class="form-control" value="<?php echo $column_settings['items_per_row_xl']; ?>" min="1" max="6" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <button type="submit" class="btn btn-primary mt-4">Lưu cấu hình</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                        <?php include 'include/footer.php'; ?>

        </div>
    </div>

    <!-- Core JS Files -->
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
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/js/bootstrap-switch.min.js"></script>
<script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
                iziToast.<?php echo $_SESSION['toast_type']; ?>({
                    title: '<?php echo $_SESSION['toast_type'] === 'success' ? 'Thành công!' : 'Lỗi!'; ?>',
                    message: "<?php echo htmlspecialchars($_SESSION['toast_message']); ?>",
                    position: 'topRight',
                    timeout: 6000
                });
                <?php
                unset($_SESSION['toast_message']);
                unset($_SESSION['toast_type']);
                ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>