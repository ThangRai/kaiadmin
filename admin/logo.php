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
    header("Location: logo.php");
    exit;
}

// Handle toggle status
if (isset($_POST['toggle_status'])) {
    $logo_id = $_POST['logo_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE logos SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $logo_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Toggle status error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: logo.php");
    exit;
}

// Handle update position
if (isset($_POST['update_position'])) {
    $logo_id = $_POST['logo_id'];
    $new_position = $_POST['new_position'];
    try {
        $stmt = $pdo->prepare("UPDATE logos SET position = ? WHERE id = ?");
        $stmt->execute([$new_position, $logo_id]);
        $_SESSION['toast_message'] = 'Cập nhật thứ tự thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Update position error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật thứ tự!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: logo.php");
    exit;
}

// Handle add/edit logo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_logo']) || isset($_POST['edit_logo']))) {
    $pdo->beginTransaction();
    try {
        $title_vi = trim($_POST['title_vi']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $link_vi = trim($_POST['link_vi']);
        $link_target = $_POST['link_target'];
        $width = trim($_POST['width']) ?: null;
        $height = trim($_POST['height']) ?: null;
        $position = trim($_POST['position']) ?: 0;

        // Handle file uploads
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $desktop_image = null;
        $mobile_image = null;

        if (!empty($_FILES['desktop_image']['name'])) {
            $file_extension = strtolower(pathinfo($_FILES['desktop_image']['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $desktop_name = time() . '_desktop_' . basename($_FILES['desktop_image']['name']);
                $desktop_path = $upload_dir . $desktop_name;
                if (move_uploaded_file($_FILES['desktop_image']['tmp_name'], $desktop_path)) {
                    $desktop_image = '/kai/admin/uploads/' . $desktop_name;
                }
            }
        }

        if (!empty($_FILES['mobile_image']['name'])) {
            $file_extension = strtolower(pathinfo($_FILES['mobile_image']['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $mobile_name = time() . '_mobile_' . basename($_FILES['mobile_image']['name']);
                $mobile_path = $upload_dir . $mobile_name;
                if (move_uploaded_file($_FILES['mobile_image']['tmp_name'], $mobile_path)) {
                    $mobile_image = '/kai/admin/uploads/' . $mobile_name;
                }
            }
        }

        if (isset($_POST['add_logo'])) {
            $stmt = $pdo->prepare("INSERT INTO logos (title_vi, desktop_image, mobile_image, link_vi, link_target, width, height, is_active, position, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title_vi, $desktop_image, $mobile_image, $link_vi, $link_target, $width, $height, $is_active, $position]);
            log_debug("Added logo: $title_vi");
        } else {
            $logo_id = $_POST['logo_id'];
            $stmt = $pdo->prepare("UPDATE logos SET title_vi = ?, desktop_image = ?, mobile_image = ?, link_vi = ?, link_target = ?, width = ?, height = ?, is_active = ?, position = ? WHERE id = ?");
            $stmt->execute([$title_vi, $desktop_image, $mobile_image, $link_vi, $link_target, $width, $height, $is_active, $position, $logo_id]);
            log_debug("Updated logo ID: $logo_id");
        }

        $pdo->commit();
        $_SESSION['toast_message'] = isset($_POST['add_logo']) ? 'Thêm logo thành công!' : 'Cập nhật logo thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Save error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: logo.php");
    exit;
}

// Handle delete logo
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM logos WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $_SESSION['toast_message'] = $result ? 'Xóa logo thành công!' : 'Xóa logo thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        log_debug('Delete error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi xóa!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: logo.php");
    exit;
}

// Fetch logos with filters
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$is_active_filter = isset($_GET['is_active']) ? $_GET['is_active'] : '';

$where = ['1=1'];
$params = [];
if ($keyword) {
    $where[] = "title_vi LIKE ?";
    $params[] = "%$keyword%";
}
if ($is_active_filter && in_array($is_active_filter, ['0', '1'])) {
    $where[] = "is_active = ?";
    $params[] = (int)$is_active_filter;
}

$query = "SELECT * FROM logos WHERE " . implode(' AND ', $where) . " ORDER BY position ASC, id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$method = isset($_GET['method']) ? $_GET['method'] : 'list';
$edit_logo = null;
if ($method === 'frm' && isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM logos WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_logo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Logo - Kaiadmin</title>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/css/bootstrap3/bootstrap-switch.min.css" rel="stylesheet">
    <style>
        .add-btn { position: absolute; top: 20px; right: 20px; }
        .table-responsive { margin-bottom: 1rem; }
        .form-group label { font-weight: 500; }
        .form-header { margin-bottom: 20px; }
        .image-preview { max-width: 50px; height: auto; margin-top: 10px; }
        .gallery-preview { display: flex; gap: 10px; }
        .gallery-preview img { width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; }
        .position-input { width: 80px; }
        @media (max-width: 768px) {
            .add-btn { position: static; margin-bottom: 15px; }
            .form-header .btn-primary { padding: 6px; font-size: 0.9rem; }
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
                        <h3 class="fw-bold mb-3">Quản lý Logo</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Logo</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($method === 'frm'): ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $edit_logo ? 'Sửa Logo' : 'Thêm Logo'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="form-header d-flex justify-content-end">
                                                <button type="submit" name="<?php echo $edit_logo ? 'edit_logo' : 'add_logo'; ?>" class="btn btn-primary">Lưu</button>
                                                <a href="logo.php" class="btn btn-secondary ml-2">Hủy</a>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="title_vi">Tên tiếng Việt</label>
                                                        <input type="text" id="title_vi" name="title_vi" class="form-control" value="<?php echo $edit_logo ? htmlspecialchars($edit_logo['title_vi']) : ''; ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="desktop_image">Hình desktop</label>
                                                        <input type="file" id="desktop_image" name="desktop_image" class="form-control" accept="image/*">
                                                        <?php if ($edit_logo && $edit_logo['desktop_image']): ?>
                                                            <img src="<?php echo htmlspecialchars($edit_logo['desktop_image']); ?>" class="image-preview" alt="Desktop Image">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="mobile_image">Hình mobile</label>
                                                        <input type="file" id="mobile_image" name="mobile_image" class="form-control" accept="image/*">
                                                        <?php if ($edit_logo && $edit_logo['mobile_image']): ?>
                                                            <img src="<?php echo htmlspecialchars($edit_logo['mobile_image']); ?>" class="image-preview" alt="Mobile Image">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_vi">Liên kết tiếng Việt</label>
                                                        <input type="text" id="link_vi" name="link_vi" class="form-control" value="<?php echo $edit_logo ? htmlspecialchars($edit_logo['link_vi']) : ''; ?>" placeholder="VD: /trang-chu">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_target">Phương thức</label>
                                                        <select id="link_target" name="link_target" class="form-control">
                                                            <option value="_self" <?php echo $edit_logo && $edit_logo['link_target'] == '_self' ? 'selected' : ''; ?>>Mở trang hiện tại</option>
                                                            <option value="_blank" <?php echo $edit_logo && $edit_logo['link_target'] == '_blank' ? 'selected' : ''; ?>>Mở trang mới</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="width">Độ rộng (px)</label>
                                                        <input type="number" id="width" name="width" class="form-control" value="<?php echo $edit_logo ? htmlspecialchars($edit_logo['width']) : ''; ?>" min="0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="height">Chiều cao (px)</label>
                                                        <input type="number" id="height" name="height" class="form-control" value="<?php echo $edit_logo ? htmlspecialchars($edit_logo['height']) : ''; ?>" min="0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="position">Sắp xếp</label>
                                                        <input type="number" id="position" name="position" class="form-control" value="<?php echo $edit_logo ? htmlspecialchars($edit_logo['position']) : '0'; ?>" required min="0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hiển thị</label>
                                                        <div>
                                                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $edit_logo && $edit_logo['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="is_active">Bật</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($edit_logo): ?>
                                                <input type="hidden" name="logo_id" value="<?php echo $edit_logo['id']; ?>">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách Logo</h4>
                                    </div>
                                    <div class="card-body">
                                        <a href="?method=frm" class="btn btn-primary add-btn">Thêm Logo</a>
                                        <form method="GET" class="mb-4">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="keyword">Từ khóa</label>
                                                        <input type="text" id="keyword" name="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Nhập từ khóa">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="is_active">Ẩn/Hiện</label>
                                                        <select id="is_active" name="is_active" class="form-control">
                                                            <option value="">Tất cả</option>
                                                            <option value="1" <?php echo $is_active_filter == '1' ? 'selected' : ''; ?>>Hiển thị</option>
                                                            <option value="0" <?php echo $is_active_filter == '0' ? 'selected' : ''; ?>>Ẩn</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group mt-4 d-flex gap-2">
                                                        <button type="submit" class="btn btn-primary">Tìm</button>
                                                        <a href="logo.php" class="btn btn-secondary">Reset</a>
                                                    </div>
                                                </div>

                                            </div>
                                        </form>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th style="width: 20%;">Sắp xếp</th>
                                                        <th>Tiêu đề</th>
                                                        <th>Hình</th>
                                                        <th>Hiển thị</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($logos as $logo): ?>
                                                        <tr>
                                                            <td><?php echo $logo['id']; ?></td>
                                                            <td>
                                                                <form method="POST" action="logo.php" style="display: flex; align-items: center; gap: 8px;">
                                                                    <input type="hidden" name="logo_id" value="<?php echo $logo['id']; ?>">
                                                                    <input type="hidden" name="update_position" value="1">
                                                                    <input type="number" class="form-control position-input" name="new_position" value="<?php echo htmlspecialchars($logo['position']); ?>" min="0">
                                                                    <button type="submit" class="btn btn-sm btn-primary">Cập nhật</button>
                                                                </form>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($logo['title_vi']); ?></td>
                                                            <td>
                                                                <?php if ($logo['desktop_image']): ?>
                                                                    <div class="mb-2">
                                                                        <small><strong>Hình desktop:</strong></small><br>
                                                                        <img src="<?php echo htmlspecialchars($logo['desktop_image']); ?>" class="image-preview" alt="Desktop Image">
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if ($logo['mobile_image']): ?>
                                                                    <div>
                                                                        <small><strong>Hình mobile:</strong></small><br>
                                                                        <img src="<?php echo htmlspecialchars($logo['mobile_image']); ?>" class="image-preview" alt="Mobile Image">
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>

                                                            <td>
                                                                <form method="POST" action="logo.php">
                                                                    <input type="hidden" name="logo_id" value="<?php echo $logo['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $logo['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?method=frm&edit_id=<?php echo $logo['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="?delete_id=<?php echo $logo['id']; ?>" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                                    <i class="fas fa-trash-alt"></i>
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
        </div>
    </div>

    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
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
</xaiArtifact>