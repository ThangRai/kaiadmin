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
    header("Location: doitac.php");
    exit;
}

// Generate slug with Vietnamese accents
function generate_slug($string) {
    $string = trim($string);
    $string = mb_strtolower($string, 'UTF-8');
    $string = preg_replace('/[^a-z0-9áàảãạăắằẳẴạâấẦẩẫẬéèẻẽẹêếềểễệíìỉĩịóòỏõọôốồổỗộơớờởỡợúùủũụưứừửữựýỳỷỹỵđ-]/u', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Handle toggle status
if (isset($_POST['toggle_status'])) {
    $partner_id = $_POST['partner_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE partner SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $partner_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Toggle status error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: doitac.php");
    exit;
}

// Handle update position
if (isset($_POST['update_position'])) {
    $partner_id = $_POST['partner_id'];
    $new_position = $_POST['new_position'];
    try {
        $stmt = $pdo->prepare("UPDATE partner SET position = ? WHERE id = ?");
        $stmt->execute([$new_position, $partner_id]);
        $_SESSION['toast_message'] = 'Cập nhật thứ tự thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Update position error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật thứ tự!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: doitac.php");
    exit;
}

// Handle delete partner
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM partner WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $_SESSION['toast_message'] = $result ? 'Xóa đối tác thành công!' : 'Xóa đối tác thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        log_debug('Delete error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi xóa!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: doitac.php");
    exit;
}

// Handle add/edit partner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_partner']) || isset($_POST['edit_partner']))) {
    $pdo->beginTransaction();
    try {
        $title_vi = trim($_POST['title_vi']);
        $parent_id = $_POST['parent_id'] ?: 0;
        $module_id = 14;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $h1_content = trim($_POST['h1_content']);
        $content_vi = $_POST['content_vi'];
        $slug_vi = trim($_POST['slug_vi']) ?: generate_slug($title_vi);
        $link_vi = trim($_POST['link_vi']);
        $link_target = $_POST['link_target'];
        $width = trim($_POST['width']);
        $height = trim($_POST['height']);

        // Handle single image upload
        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $image_name = time() . '_partner.' . $file_extension;
                $image_path = $upload_dir . $image_name;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image = '/kai/admin/uploads/' . $image_name;
                }
            }
        } else {
            // Keep existing image when editing and no new image is uploaded
            if (isset($_POST['edit_partner'])) {
                $partner_id = $_POST['partner_id'];
                $stmt = $pdo->prepare("SELECT image FROM partner WHERE id = ?");
                $stmt->execute([$partner_id]);
                $image = $stmt->fetchColumn();
            }
        }

        if (isset($_POST['add_partner'])) {
            $stmt = $pdo->prepare("INSERT INTO partner (title_vi, parent_id, module_id, is_active, h1_content, content_vi, slug_vi, link_vi, link_target, image, width, height, position, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title_vi, $parent_id, $module_id, $is_active, $h1_content, $content_vi, $slug_vi, $link_vi, $link_target, $image, $width, $height, $_POST['position']]);
            log_debug("Added partner: $title_vi");
        } else {
            $partner_id = $_POST['partner_id'];
            $stmt = $pdo->prepare("UPDATE partner SET title_vi = ?, parent_id = ?, module_id = ?, is_active = ?, h1_content = ?, content_vi = ?, slug_vi = ?, link_vi = ?, link_target = ?, image = ?, width = ?, height = ?, position = ? WHERE id = ?");
            $stmt->execute([$title_vi, $parent_id, $module_id, $is_active, $h1_content, $content_vi, $slug_vi, $link_vi, $link_target, $image, $width, $height, $_POST['position'], $partner_id]);
            log_debug("Updated partner ID: $partner_id");
        }

        $pdo->commit();
        $_SESSION['toast_message'] = isset($_POST['add_partner']) ? 'Thêm đối tác thành công!' : 'Cập nhật đối tác thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Save error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: doitac.php");
    exit;
}

// Fetch parent categories (only from module_id = 14 and active)
$stmt = $pdo->query("SELECT id, title_vi FROM categories WHERE module_id = 14 AND is_active = 1 ORDER BY position ASC");
$parent_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch partners with filters
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$is_active_filter = isset($_GET['is_active']) ? $_GET['is_active'] : '';

$where = ['1=1'];
$params = [];
if ($keyword) {
    $where[] = "title_vi LIKE ?";
    $params[] = "%$keyword%";
}
if ($is_active_filter !== '') {
    $where[] = "is_active = ?";
    $params[] = $is_active_filter;
}

$query = "SELECT * FROM partner WHERE " . implode(' AND ', $where) . " ORDER BY position ASC, id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active state
$method = isset($_GET['method']) ? $_GET['method'] : 'list';
$edit_partner = null;
if ($method === 'frm' && isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM partner WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_partner = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Đối tác - Kaiadmin</title>
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
    <script src="https://cdn.ckeditor.com/4.22.0/standard/ckeditor.js"></script>
    <style>
        .add-btn { position: absolute; top: 20px; right: 20px; }
        .table-responsive { margin-bottom: 1rem; }
        .form-group label { font-weight: 500; }
        .form-header { margin-bottom: 20px; }
        .image-preview {height: auto; margin-top: 10px; }
        .position-input { width: 60px; }
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
                        <h3 class="fw-bold mb-3">Quản lý Đối tác</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Đối tác</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($method === 'frm'): ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $edit_partner ? 'Sửa Đối tác' : 'Thêm Đối tác'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="form-header d-flex justify-content-end">
                                                <button type="submit" name="<?php echo $edit_partner ? 'edit_partner' : 'add_partner'; ?>" class="btn btn-primary">Lưu</button>
                                                <a href="doitac.php" class="btn btn-secondary ml-2">Hủy</a>
                                            </div>
                                            <div class="row">
                                                <!-- Left Column (3/12) -->
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Module</label>
                                                        <input type="hidden" name="module_id" value="8">
                                                        <input type="text" class="form-control" value="Đối tác (ID: 8)" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="parent_id">Danh mục cha</label>
                                                        <select id="parent_id" name="parent_id" class="form-control">
                                                            <option value="0">Không có</option>
                                                            <?php foreach ($parent_categories as $parent): ?>
                                                                <option value="<?php echo $parent['id']; ?>" <?php echo $edit_partner && $edit_partner['parent_id'] == $parent['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($parent['title_vi']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hiển thị</label>
                                                        <div>
                                                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $edit_partner && $edit_partner['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="is_active">Bật</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hình ảnh</label>
                                                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                                        <?php if ($edit_partner && $edit_partner['image']): ?>
                                                            <div class="image-preview">
                                                                <img src="<?php echo htmlspecialchars($edit_partner['image']); ?>" alt="Partner Image">
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="width">Độ rộng (px)</label>
                                                        <input type="number" id="width" name="width" class="form-control" value="<?php echo $edit_partner ? htmlspecialchars($edit_partner['width']) : ''; ?>" min="0" placeholder="Nhập độ rộng">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="height">Chiều cao (px)</label>
                                                        <input type="number" id="height" name="height" class="form-control" value="<?php echo $edit_partner ? htmlspecialchars($edit_partner['height']) : ''; ?>" min="0" placeholder="Nhập chiều cao">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="position">Sắp xếp</label>
                                                        <input type="number" id="position" name="position" class="form-control" value="<?php echo $edit_partner ? htmlspecialchars($edit_partner['position']) : '0'; ?>" required min="0">
                                                    </div>
                                                </div>
                                                <!-- Right Column (7/12) -->
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        <label for="title_vi">Tên tiếng Việt</label>
                                                        <input type="text" id="title_vi" name="title_vi" class="form-control" value="<?php echo $edit_partner ? htmlspecialchars($edit_partner['title_vi']) : ''; ?>" required placeholder="Nhập tên đối tác">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="h1_content">Nội dung thẻ H1</label>
                                                        <input type="text" id="h1_content" name="h1_content" class="form-control" value="<?php echo $edit_partner ? htmlspecialchars($edit_partner['h1_content']) : ''; ?>" placeholder="Nhập nội dung H1">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="content_vi">Nội dung tiếng Việt</label>
                                                        <textarea id="content_vi" name="content_vi" class="form-control"><?php echo $edit_partner ? htmlspecialchars($edit_partner['content_vi']) : ''; ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="slug_vi">Đường dẫn tiếng Việt</label>
                                                        <input type="text" id="slug_vi" name="slug_vi" class="form-control" value="<?php echo $edit_partner ? htmlspecialchars($edit_partner['slug_vi']) : ''; ?>" placeholder="Tự sinh từ tên nếu để trống">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_vi">Liên kết tiếng Việt</label>
                                                        <input type="text" id="link_vi" name="link_vi" class="form-control" value="<?php echo $edit_partner ? htmlspecialchars($edit_partner['link_vi']) : ''; ?>" placeholder="VD: /doi-tac">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_target">Phương thức</label>
                                                        <select id="link_target" name="link_target" class="form-control">
                                                            <option value="_self" <?php echo $edit_partner && $edit_partner['link_target'] == '_self' ? 'selected' : ''; ?>>Mở trang hiện tại</option>
                                                            <option value="_blank" <?php echo $edit_partner && $edit_partner['link_target'] == '_blank' ? 'selected' : ''; ?>>Mở trang mới</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($edit_partner): ?>
                                                <input type="hidden" name="partner_id" value="<?php echo $edit_partner['id']; ?>">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách Đối tác</h4>
                                    </div>
                                    <div class="card-body">
                                        <a href="?method=frm" class="btn btn-primary add-btn">Thêm Đối tác</a>
                                        <form method="GET" class="mb-4">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="keyword">Từ khóa</label>
                                                        <input type="text" id="keyword" name="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Nhập từ khóa">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="is_active">Trạng thái</label>
                                                        <select id="is_active" name="is_active" class="form-control">
                                                            <option value="" <?php echo $is_active_filter === '' ? 'selected' : ''; ?>>Tất cả</option>
                                                            <option value="1" <?php echo $is_active_filter == 1 ? 'selected' : ''; ?>>Hiện</option>
                                                            <option value="0" <?php echo $is_active_filter == 0 ? 'selected' : ''; ?>>Ẩn</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group mt-4">
                                                        <button type="submit" class="btn btn-primary">Tìm</button>
                                                        <a href="doitac.php" class="btn btn-secondary">Reset</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Sắp xếp</th>
                                                        <th>Tiêu đề</th>
                                                        <th>Hình</th>
                                                        <th>Hiển thị</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($partners as $partner): ?>
                                                        <tr>
                                                            <td><?php echo $partner['id']; ?></td>
                                                            <td>
                                                                <form method="POST" action="doitac.php" class="d-flex align-items-center gap-2">
                                                                    <input type="hidden" name="partner_id" value="<?php echo $partner['id']; ?>">
                                                                    <input type="hidden" name="update_position" value="1">
                                                                    <input type="number" class="form-control form-control-sm" name="new_position" value="<?php echo htmlspecialchars($partner['position']); ?>" min="0" style="width: 80px;">
                                                                    <button type="submit" class="btn btn-sm btn-primary">Cập nhật</button>
                                                                </form>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($partner['title_vi']); ?></td>
                                                            <td>
                                                                <?php if ($partner['image']): ?>
                                                                    <div class="image-preview">
                                                                        <img src="<?php echo htmlspecialchars($partner['image']); ?>" alt="Partner Image">
                                                                    </div>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <form method="POST" action="doitac.php">
                                                                    <input type="hidden" name="partner_id" value="<?php echo $partner['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $partner['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?method=frm&edit_id=<?php echo $partner['id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                                                <a href="?delete_id=<?php echo $partner['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/js/bootstrap-switch.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
                iziToast.<?php echo $_SESSION['toast_type']; ?>({
                    title: '<?php echo $_SESSION['toast_type'] === 'success' ? 'Thành công' : 'Lỗi'; ?>',
                    message: '<?php echo htmlspecialchars($_SESSION['toast_message']); ?>',
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

            CKEDITOR.replace('content_vi');

            const imageInput = document.getElementById('image');
            const imagePreview = document.querySelector('.image-preview');
            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function() {
                    imagePreview.innerHTML = '';
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.maxWidth = '100px';
                            img.style.height = 'auto';
                            imagePreview.appendChild(img);
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        });
    </script>
    <?php ob_end_flush(); ?>