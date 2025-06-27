<?php
ob_start();
session_start();
require 'database/config.php';
require_once 'include/functions.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check database connection
if (!$pdo) {
    log_debug("Database connection failed");
    $_SESSION['toast_message'] = 'Không thể kết nối đến cơ sở dữ liệu!';
    $_SESSION['toast_type'] = 'error';
    header("Location: danhmuc_type.php");
    exit;
}

// Handle toggle status
if (isset($_POST['toggle_status'])) {
    $module_id = $_POST['module_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE modules SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $module_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Toggle status error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: danhmuc_type.php");
    exit;
}

// Handle delete module
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $_SESSION['toast_message'] = $result ? 'Xóa module thành công!' : 'Xóa module thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        log_debug('Delete error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi xóa: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: danhmuc_type.php");
    exit;
}

// Handle add/edit module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_module']) || isset($_POST['edit_module']))) {
    try {
        $title = trim($_POST['title']);
        $position = trim($_POST['position']);
        $option = trim($_POST['option']);
        $action = trim($_POST['action'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title) || empty($option) || !is_numeric($position)) {
            throw new Exception("Tiêu đề, OP và Sắp xếp là bắt buộc.");
        }

        if (isset($_POST['add_module'])) {
            $stmt = $pdo->prepare("INSERT INTO modules (title, position, option_name, action_name, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $position, $option, $action, $is_active]);
            $_SESSION['toast_message'] = 'Thêm module thành công!';
            $_SESSION['toast_type'] = 'success';
            log_debug("Added module: $title, OP: $option");
        } else {
            $module_id = $_POST['module_id'];
            $stmt = $pdo->prepare("UPDATE modules SET title = ?, position = ?, option_name = ?, action_name = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$title, $position, $option, $action, $is_active, $module_id]);
            $_SESSION['toast_message'] = 'Cập nhật module thành công!';
            $_SESSION['toast_type'] = 'success';
            log_debug("Updated module ID: $module_id, OP: $option");
        }
    } catch (Exception $e) {
        log_debug('Save error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: danhmuc_type.php");
    exit;
}

// Fetch module list
$stmt = $pdo->query("SELECT * FROM modules ORDER BY position ASC, id DESC");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active state
$method = isset($_GET['method']) ? $_GET['method'] : 'list';

// Fetch module for edit
$edit_module = null;
if ($method === 'frm' && isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_module = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Modules - Kaiadmin</title>
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
    <!-- Bootstrap Switch CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/css/bootstrap3/bootstrap-switch.min.css" rel="stylesheet">
    <style>
        .add-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .table-responsive {
            margin-bottom: 1rem;
        }
        .form-group label {
            font-weight: 500;
        }
        .form-header {
            margin-bottom: 20px;
        }
        .form-header .btn-primary {
            padding: 8px 20px;
        }
        @media (max-width: 768px) {
            .add-btn {
                position: static;
                margin-bottom: 15px;
            }
            .form-header .btn-primary {
                padding: 6px 15px;
                font-size: 0.9rem;
            }
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
                        <h3 class="fw-bold mb-3">Quản lý Modules</h3>
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
                                <a href="#">Modules</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($method === 'frm'): ?>
                            <!-- Form Add/Edit Module -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $edit_module ? 'Sửa Module' : 'Thêm Module'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="form-header d-flex justify-content-end">
                                                <button type="submit" name="<?php echo $edit_module ? 'edit_module' : 'add_module'; ?>" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Lưu
                                                </button>
                                                <a href="danhmuc_type.php" class="btn btn-secondary ml-2">
                                                    <i class="fas fa-times"></i> Hủy
                                                </a>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="title">Tiêu đề</label>
                                                        <input type="text" id="title" name="title" class="form-control" value="<?php echo $edit_module ? htmlspecialchars($edit_module['title']) : ''; ?>" required placeholder="Nhập tiêu đề, ví dụ: Trang chủ">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="position">Sắp xếp</label>
                                                        <input type="number" id="position" name="position" class="form-control" value="<?php echo $edit_module ? htmlspecialchars($edit_module['position']) : '0'; ?>" required min="0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Trạng thái hiển thị</label>
                                                        <div>
                                                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $edit_module && $edit_module['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="is_active">Bật</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="option">OP (Option)</label>
                                                        <input type="text" id="option" name="option" class="form-control" value="<?php echo $edit_module ? htmlspecialchars($edit_module['option_name']) : ''; ?>" required placeholder="VD: home (gọi file home.php)">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="action">Action (ACT)</label>
                                                        <input type="text" id="action" name="action" class="form-control" value="<?php echo $edit_module ? htmlspecialchars($edit_module['action_name']) : ''; ?>" placeholder="Không bắt buộc, ví dụ: index">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($edit_module): ?>
                                                <input type="hidden" name="module_id" value="<?php echo $edit_module['id']; ?>">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Module List -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách Modules</h4>
                                    </div>
                                    <div class="card-body">
                                        <a href="?method=frm" class="btn btn-primary add-btn">
                                            <i class="fas fa-plus"></i> Thêm Module
                                        </a>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Sắp xếp</th>
                                                        <th>Tiêu đề</th>
                                                        <th>OP</th>
                                                        <th>ACT</th>
                                                        <th>Hiển thị</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($modules as $module): ?>
                                                        <tr>
                                                            <td><?php echo $module['id']; ?></td>
                                                            <td><?php echo htmlspecialchars($module['position']); ?></td>
                                                            <td><?php echo htmlspecialchars($module['title']); ?></td>
                                                            <td><?php echo htmlspecialchars($module['option_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($module['action_name'] ?: '-'); ?></td>
                                                            <td>
                                                                <form method="POST" action="danhmuc_type.php">
                                                                    <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $module['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?method=frm&edit_id=<?php echo $module['id']; ?>" class="btn btn-sm btn-warning">
                                                                    <i class="fas fa-edit"></i> Sửa
                                                                </a>
                                                                <a href="?delete_id=<?php echo $module['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                                    <i class="fas fa-trash-alt"></i> Xóa
                                                                </a>
                                                            </td>

                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
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
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <!-- Bootstrap Switch JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/js/bootstrap-switch.min.js"></script>
    <script>
        $(document).ready(function() {
            // iZitoast notification
            <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
                iziToast.<?php echo $_SESSION['toast_type']; ?>({
                    title: '<?php echo $_SESSION['toast_type'] === 'success' ? 'Thành công' : 'Lỗi'; ?>',
                    message: '<?php echo $_SESSION['toast_message']; ?>',
                    position: 'topRight',
                    timeout: 6000
                });
                <?php
                unset($_SESSION['toast_message']);
                unset($_SESSION['toast_type']);
                ?>
            <?php endif; ?>

            // Initialize Bootstrap Switch
            $(".toggle-switch").bootstrapSwitch({
                onText: 'Bật',
                offText: 'Tắt',
                onColor: 'success',
                offColor: 'danger',
                size: 'small'
            });
        });
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>