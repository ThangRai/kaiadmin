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
    header("Location: ykienkhachhang.php");
    exit;
}

// Fetch available modules from categories with module_id = 18
function getModules($pdo) {
    $stmt = $pdo->prepare("SELECT id, title_vi AS name FROM categories WHERE module_id = 18 AND is_active = 1 ORDER BY title_vi ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle add/edit feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $module_id = trim($_POST['module_id'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $image_path = null;

    // Validate module_id
    $modules = getModules($pdo);
    $valid_module = array_reduce($modules, fn($carry, $module) => $carry || (string)$module['id'] === $module_id, false);
    if (!$valid_module) {
        $_SESSION['toast_message'] = 'Module không hợp lệ!';
        $_SESSION['toast_type'] = 'error';
        header("Location: ykienkhachhang.php?method=frm");
        exit;
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/feedback/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $_SESSION['toast_message'] = 'Lỗi khi tải lên hình ảnh!';
            $_SESSION['toast_type'] = 'error';
            header("Location: ykienkhachhang.php?method=frm");
            exit;
        }
    }

    try {
        if ($id) {
            // Update existing feedback
            $sql = "UPDATE customer_feedback SET name = ?, module_id = ?, description = ?, content = ?, is_active = ?, updated_at = NOW()";
            $params = [$name, $module_id, $description, $content, $is_active];
            if ($image_path) {
                $sql .= ", image = ?";
                $params[] = $image_path;
            }
            $sql .= " WHERE id = ?";
            $params[] = $id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['toast_message'] = 'Cập nhật ý kiến khách hàng thành công!';
        } else {
            // Add new feedback
            $sql = "INSERT INTO customer_feedback (name, image, module_id, description, content, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $image_path, $module_id, $description, $content, $is_active]);
            $_SESSION['toast_message'] = 'Thêm ý kiến khách hàng thành công!';
        }
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }

    header("Location: ykienkhachhang.php");
    exit;
}

// Handle delete feedback
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("SELECT image FROM customer_feedback WHERE id = ?");
        $stmt->execute([$id]);
        $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($feedback && $feedback['image'] && file_exists($feedback['image'])) {
            unlink($feedback['image']);
        }
        $stmt = $pdo->prepare("DELETE FROM customer_feedback WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['toast_message'] = 'Xóa ý kiến khách hàng thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi xóa: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: ykienkhachhang.php");
    exit;
}

// Handle copy feedback
if (isset($_GET['copy_id'])) {
    $id = (int)$_GET['copy_id'];
    try {
        $stmt = $pdo->prepare("SELECT name, image, module_id, description, content, is_active FROM customer_feedback WHERE id = ?");
        $stmt->execute([$id]);
        $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($feedback) {
            $new_image = null;
            if ($feedback['image']) {
                $ext = pathinfo($feedback['image'], PATHINFO_EXTENSION);
                $new_image = 'uploads/feedback/' . time() . '_copy.' . $ext;
                copy($feedback['image'], $new_image);
            }
            $stmt = $pdo->prepare("INSERT INTO customer_feedback (name, image, module_id, description, content, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$feedback['name'], $new_image, $feedback['module_id'], $feedback['description'], $feedback['content'], $feedback['is_active']]);
            $_SESSION['toast_message'] = 'Sao chép ý kiến khách hàng thành công!';
            $_SESSION['toast_type'] = 'success';
        }
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi sao chép: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: ykienkhachhang.php");
    exit;
}

// Handle toggle is_active
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    try {
        $stmt = $pdo->prepare("SELECT is_active FROM customer_feedback WHERE id = ?");
        $stmt->execute([$id]);
        $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($feedback) {
            $new_status = $feedback['is_active'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE customer_feedback SET is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
            $_SESSION['toast_type'] = 'success';
        }
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: ykienkhachhang.php");
    exit;
}

// Fetch feedback for edit
$edit_feedback = null;
if (isset($_GET['edit_id'])) {
    $id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM customer_feedback WHERE id = ?");
    $stmt->execute([$id]);
    $edit_feedback = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all feedback for display
$feedbacks = [];
if (!isset($_GET['method']) || $_GET['method'] !== 'frm') {
    $stmt = $pdo->query("SELECT * FROM customer_feedback ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch modules for dropdown
$modules = getModules($pdo);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Ý kiến Khách hàng - Kaiadmin</title>
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
    <script src="ckeditor/ckeditor.js"></script>
    <style>
        .form-group label { font-weight: 500; }
        .card-header { background-color: #f8f9fa; }
        .table-responsive { margin-bottom: 1rem; }
        .add-btn, .form-actions { position: absolute; top: 20px; right: 20px; }
        .feedback-image { max-width: 100px; height: auto; }
        .status-toggle { cursor: pointer; }
        @media (max-width: 768px) {
            .add-btn, .form-actions { position: static; margin-bottom: 15px; }
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
                        <h3 class="fw-bold mb-3">Quản lý Ý kiến Khách hàng</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="../index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Ý kiến Khách hàng</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Danh sách Ý kiến Khách hàng</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($_GET['method']) && $_GET['method'] === 'frm'): ?>
                                        <div class="form-actions">
                                            <button type="submit" form="feedbackForm" class="btn btn-primary">
                                                <i class="fas fa-save"></i> <?php echo $edit_feedback ? 'Cập nhật' : 'Lưu'; ?>
                                            </button>
                                            <a href="ykienkhachhang.php" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Hủy
                                            </a>
                                        </div>

                                        <form id="feedbackForm" method="POST" action="ykienkhachhang.php" enctype="multipart/form-data">
                                            <?php if ($edit_feedback): ?>
                                                <input type="hidden" name="id" value="<?php echo $edit_feedback['id']; ?>">
                                            <?php endif; ?>
                                            <div class="form-group">
                                                <label for="name">Tên Khách hàng <span class="text-danger">*</span></label>
                                                <input type="text" id="name" name="name" class="form-control" value="<?php echo $edit_feedback['name'] ?? ''; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="module_id">Module <span class="text-danger">*</span></label>
                                                <select id="module_id" name="module_id" class="form-control" required>
                                                    <option value="">Chọn module</option>
                                                    <?php foreach ($modules as $module): ?>
                                                        <option value="<?php echo $module['id']; ?>" <?php echo (isset($edit_feedback['module_id']) && (string)$edit_feedback['module_id'] === (string)$module['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($module['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="image">Hình ảnh</label>
                                                <input type="file" id="image" name="image" class="form-control-file" accept="image/*">
                                                <?php if ($edit_feedback && $edit_feedback['image']): ?>
                                                    <img src="<?php echo $edit_feedback['image']; ?>" class="feedback-image mt-2" alt="Hình ảnh">
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-group">
                                                <label for="description">Mô tả</label>
                                                <textarea id="description" name="description" class="form-control"><?php echo $edit_feedback['description'] ?? ''; ?></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="content">Nội dung</label>
                                                <textarea id="content" name="content" class="form-control"><?php echo $edit_feedback['content'] ?? ''; ?></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="is_active">Trạng thái</label>
                                                <select id="is_active" name="is_active" class="form-control">
                                                    <option value="1" <?php echo (isset($edit_feedback['is_active']) && $edit_feedback['is_active'] == 1) ? 'selected' : ''; ?>>Hiện</option>
                                                    <option value="0" <?php echo (isset($edit_feedback['is_active']) && $edit_feedback['is_active'] == 0) ? 'selected' : ''; ?>>Ẩn</option>
                                                </select>
                                            </div>
                                        </form>
                                        <script>
                                            CKEDITOR.replace('description');
                                            CKEDITOR.replace('content');
                                        </script>
                                    <?php else: ?>
                                        <a href="ykienkhachhang.php?method=frm" class="btn btn-primary add-btn">
                                            <i class="fas fa-plus"></i> Thêm Ý kiến
                                        </a>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Tên</th>
                                                        <th>Hình ảnh</th>
                                                        <th>Nội dung</th>
                                                        <th>Trạng thái</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($feedbacks as $feedback): ?>
                                                        <tr>
                                                            <td><?php echo $feedback['id']; ?></td>
                                                            <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                                                            <td>
                                                                <?php if ($feedback['image']): ?>
                                                                    <img src="<?php echo $feedback['image']; ?>" class="feedback-image" alt="Hình ảnh">
                                                                <?php else: ?>
                                                                    Không có
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo ($feedback['content'] ?? 'Không có'); ?></td>
                                                            <td>
                                                                <a href="?toggle_id=<?php echo $feedback['id']; ?>" class="status-toggle">
                                                                    <span class="badge badge-<?php echo $feedback['is_active'] ? 'success' : 'danger'; ?>">
                                                                        <?php echo $feedback['is_active'] ? 'Hiện' : 'Ẩn'; ?>
                                                                    </span>
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <a href="ykienkhachhang.php?method=frm&edit_id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-primary" title="Sửa">
                                                                    <i class="fas fa-edit"></i> Sửa
                                                                </a>
                                                                <a href="?copy_id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-info" title="Chép">
                                                                    <i class="fas fa-copy"></i> Sao chép
                                                                </a>
                                                                <a href="?delete_id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa ý kiến này?')">
                                                                    <i class="fas fa-trash-alt"></i> Xoá
                                                                </a>
                                                            </td>

                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
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
                    title: '<?php echo $_SESSION['toast_type'] === 'success' ? 'Thành công!' : 'Lỗi!'; ?>',
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