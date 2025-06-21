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
    header("Location: gallery.php");
    exit;
}

// Handle toggle status
if (isset($_POST['toggle_status'])) {
    $gallery_id = $_POST['gallery_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE gallery SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $gallery_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Toggle status error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: gallery.php");
    exit;
}

// Handle update position
if (isset($_POST['update_position'])) {
    $gallery_id = $_POST['gallery_id'];
    $new_position = $_POST['new_position'];
    try {
        $stmt = $pdo->prepare("UPDATE gallery SET position = ? WHERE id = ?");
        $stmt->execute([$new_position, $gallery_id]);
        $_SESSION['toast_message'] = 'Cập nhật thứ tự thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Update position error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật thứ tự!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: gallery.php");
    exit;
}

// Handle delete gallery
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $_SESSION['toast_message'] = $result ? 'Xóa ảnh thành công!' : 'Xóa ảnh thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        log_debug('Delete error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi xóa!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: gallery.php");
    exit;
}

// Handle add/edit gallery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_gallery']) || isset($_POST['edit_gallery']))) {
    $pdo->beginTransaction();
    try {
        $title_vi = trim($_POST['title_vi']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $position = trim($_POST['position']);
        $module_id = 9; // Module ID cho Gallery

        // Handle gallery images upload
        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $gallery_images_array = [];
        if (!empty($_FILES['gallery_images']['name'][0])) {
            foreach ($_FILES['gallery_images']['name'] as $key => $name) {
                if ($_FILES['gallery_images']['error'][$key] == 0) {
                    $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $gallery_name = time() . '_gallery_' . $key . '_' . basename($name);
                        $gallery_path = $upload_dir . $gallery_name;
                        if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], $gallery_path)) {
                            $gallery_images_array[] = '/kai/admin/uploads/' . $gallery_name;
                        }
                    }
                }
            }
            $gallery_images = implode(',', $gallery_images_array);
        } else {
            // Keep existing images when editing and no new images are uploaded
            if (isset($_POST['edit_gallery'])) {
                $gallery_id = $_POST['gallery_id'];
                $stmt = $pdo->prepare("SELECT gallery_images FROM gallery WHERE id = ?");
                $stmt->execute([$gallery_id]);
                $gallery_images = $stmt->fetchColumn();
            } else {
                $gallery_images = null;
            }
        }

        if (isset($_POST['add_gallery'])) {
            $stmt = $pdo->prepare("INSERT INTO gallery (title_vi, module_id, is_active, gallery_images, position, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title_vi, $module_id, $is_active, $gallery_images, $position]);
            log_debug("Added gallery: $title_vi");
        } else {
            $gallery_id = $_POST['gallery_id'];
            $stmt = $pdo->prepare("UPDATE gallery SET title_vi = ?, module_id = ?, is_active = ?, gallery_images = ?, position = ? WHERE id = ?");
            $stmt->execute([$title_vi, $module_id, $is_active, $gallery_images, $position, $gallery_id]);
            log_debug("Updated gallery ID: $gallery_id");
        }

        $pdo->commit();
        $_SESSION['toast_message'] = isset($_POST['add_gallery']) ? 'Thêm ảnh thành công!' : 'Cập nhật ảnh thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Save error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: gallery.php");
    exit;
}

// Fetch galleries with filters
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

$query = "SELECT * FROM gallery WHERE " . implode(' AND ', $where) . " ORDER BY position ASC, id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active state
$method = isset($_GET['method']) ? $_GET['method'] : 'list';
$edit_gallery = null;
if ($method === 'frm' && isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_gallery = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Thư viện Ảnh - Kaiadmin</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
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
        .gallery-upload-box {
            width: 300px;
            height: 300px;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: #f9f9f9;
            margin-bottom: 20px;
            position: relative;
            font-size: 1.2em;
            color: #666;
        }
        .gallery-upload-box:hover { border-color: #007bff; }
        .gallery-preview { display: flex; flex-wrap: wrap; gap: 10px; }
        .gallery-preview img { width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; }
        .gallery-preview .image-container { position: relative; }
        .gallery-preview .remove-image { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; line-height: 20px; text-align: center; cursor: pointer; font-size: 14px; }
        .position-input { width: 60px; }
        .thumbnail { cursor: pointer; width: 100px; height: 100px; object-fit: cover; }
        .modal-image { max-width: 100%; max-height: 80vh; object-fit: contain; }
        .modal-gallery { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .modal-gallery img { width: 150px; height: 150px; object-fit: cover; cursor: pointer; }
        @media (max-width: 768px) {
            .add-btn { position: static; margin-bottom: 15px; }
            .form-header .btn-primary { padding: 6px; font-size: 0.9rem; }
            .gallery-upload-box { width: 200px; height: 200px; }
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
                        <h3 class="fw-bold mb-3">Quản lý Thư viện Ảnh</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Thư viện Ảnh</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($method === 'frm'): ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $edit_gallery ? 'Sửa Thư viện Ảnh' : 'Thêm Thư viện Ảnh'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data" id="gallery-form">
                                            <div class="form-header d-flex justify-content-end">
                                                <button type="submit" name="<?php echo $edit_gallery ? 'edit_gallery' : 'add_gallery'; ?>" class="btn btn-primary">Lưu</button>
                                                <a href="gallery.php" class="btn btn-secondary ml-2">Hủy</a>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="title_vi">Tên thư viện</label>
                                                        <input type="text" id="title_vi" name="title_vi" class="form-control" value="<?php echo $edit_gallery ? htmlspecialchars($edit_gallery['title_vi']) : ''; ?>" required placeholder="Nhập tên thư viện ảnh">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hiển thị</label>
                                                        <div>
                                                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $edit_gallery && $edit_gallery['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="is_active">Bật</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="position">Sắp xếp</label>
                                                        <input type="number" id="position" name="position" class="form-control" value="<?php echo $edit_gallery ? htmlspecialchars($edit_gallery['position']) : '0'; ?>" required min="0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Thư viện ảnh</label>
                                                        <input type="file" id="gallery_images" name="gallery_images[]" class="form-control" accept="image/*" multiple style="display: none;">
                                                        <div class="gallery-upload-box" onclick="document.getElementById('gallery_images').click()">
                                                            Kéo thả hoặc nhấp để chọn ảnh
                                                        </div>
                                                        <div class="gallery-preview" id="gallery-preview">
                                                            <?php if ($edit_gallery && $edit_gallery['gallery_images']): ?>
                                                                <?php foreach (explode(',', $edit_gallery['gallery_images']) as $index => $img): ?>
                                                                    <div class="image-container" data-image-index="<?php echo $index; ?>">
                                                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Gallery Image">
                                                                        <span class="remove-image">x</span>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($edit_gallery): ?>
                                                <input type="hidden" name="gallery_id" value="<?php echo $edit_gallery['id']; ?>">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách Thư viện Ảnh</h4>
                                    </div>
                                    <div class="card-body">
                                        <a href="?method=frm" class="btn btn-primary add-btn">Thêm Thư viện Ảnh</a>
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
                                                        <a href="gallery.php" class="btn btn-secondary">Reset</a>
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
                                                        <th>Tên thư viện</th>
                                                        <th>Hình ảnh</th>
                                                        <th>Hiển thị</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($galleries as $gallery): ?>
                                                        <tr>
                                                            <td><?php echo $gallery['id']; ?></td>
                                                            <td>
                                                                <form method="POST" action="gallery.php" class="d-flex align-items-center gap-2">
                                                                    <input type="hidden" name="gallery_id" value="<?php echo $gallery['id']; ?>">
                                                                    <input type="hidden" name="update_position" value="1">
                                                                    <input type="number" class="form-control form-control-sm" name="new_position" value="<?php echo htmlspecialchars($gallery['position']); ?>" min="0" style="width: 80px;">
                                                                    <button type="submit" class="btn btn-sm btn-primary">Cập nhật</button>
                                                                </form>
                                                            </td>

                                                            <td><?php echo htmlspecialchars($gallery['title_vi']); ?></td>
                                                            <td>
                                                                <?php if ($gallery['gallery_images']): ?>
                                                                    <?php $first_image = explode(',', $gallery['gallery_images'])[0]; ?>
                                                                    <img src="<?php echo htmlspecialchars($first_image); ?>" class="thumbnail" alt="Thumbnail" data-toggle="modal" data-target="#galleryModal<?php echo $gallery['id']; ?>">
                                                                    <!-- Modal -->
                                                                    <div class="modal fade" id="galleryModal<?php echo $gallery['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="galleryModalLabel<?php echo $gallery['id']; ?>" aria-hidden="true">
                                                                        <div class="modal-dialog modal-lg" role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title" id="galleryModalLabel<?php echo $gallery['id']; ?>"><?php echo htmlspecialchars($gallery['title_vi']); ?></h5>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <img src="<?php echo htmlspecialchars($first_image); ?>" class="modal-image d-block mx-auto" alt="Large Image">
                                                                                    <div class="modal-gallery mt-3">
                                                                                        <?php foreach (explode(',', $gallery['gallery_images']) as $img): ?>
                                                                                            <img src="<?php echo htmlspecialchars($img); ?>" class="modal-gallery-img" alt="Gallery Image" onclick="changeModalImage(this)">
                                                                                        <?php endforeach; ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <form method="POST" action="gallery.php">
                                                                    <input type="hidden" name="gallery_id" value="<?php echo $gallery['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $gallery['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?method=frm&edit_id=<?php echo $gallery['id']; ?>" class="btn btn-sm btn-warning">
                                                                    <i class="fas fa-edit"></i> Sửa
                                                                </a>
                                                                <a href="?delete_id=<?php echo $gallery['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/js/bootstrap-switch.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
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

            // Initialize Bootstrap Switch
            $(".toggle-switch").bootstrapSwitch({
                onText: 'Bật',
                offText: 'Tắt',
                onColor: 'success',
                offColor: 'danger',
                size: 'small'
            });

            // Initialize Bootstrap Modals
            $('.modal').modal({
                show: false
            });

            // Gallery upload handling
            const galleryInput = document.getElementById('gallery_images');
            const galleryUploadBox = document.querySelector('.gallery-upload-box');
            const galleryPreview = document.getElementById('gallery-preview');
            const galleryForm = document.getElementById('gallery-form');
            let selectedFiles = [];

            // Handle existing images when editing
            <?php if ($edit_gallery && $edit_gallery['gallery_images']): ?>
                selectedFiles = [
                    <?php foreach (explode(',', $edit_gallery['gallery_images']) as $img): ?>
                        { src: '<?php echo htmlspecialchars($img); ?>' },
                    <?php endforeach; ?>
                ];
            <?php endif; ?>

            if (galleryInput && galleryUploadBox && galleryPreview) {
                // Handle drag and drop
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    galleryUploadBox.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    galleryUploadBox.addEventListener(eventName, () => {
                        galleryUploadBox.style.borderColor = '#007bff';
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    galleryUploadBox.addEventListener(eventName, () => {
                        galleryUploadBox.style.borderColor = '#ccc';
                    }, false);
                });

                galleryUploadBox.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    handleFiles(files);
                }

                galleryInput.addEventListener('change', function() {
                    handleFiles(this.files);
                });

                function handleFiles(files) {
                    for (const file of files) {
                        if (file.type.startsWith('image/')) {
                            selectedFiles.push(file);
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                addImageToPreview(e.target.result);
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                    updateFileInput();
                }

                function addImageToPreview(src) {
                    const container = document.createElement('div');
                    container.className = 'image-container';
                    const img = document.createElement('img');
                    img.src = src;
                    img.alt = 'Gallery Image';
                    const removeBtn = document.createElement('span');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerText = 'x';
                    removeBtn.addEventListener('click', removeImage);
                    container.appendChild(img);
                    container.appendChild(removeBtn);
                    galleryPreview.appendChild(container);
                }

                function removeImage(e) {
                    const container = e.target.parentElement;
                    const imgSrc = container.querySelector('img').src;
                    selectedFiles = selectedFiles.filter(file => {
                        if (file.src) {
                            return file.src !== imgSrc;
                        } else {
                            const reader = new FileReader();
                            let fileSrc;
                            reader.onload = function(e) { fileSrc = e.target.result; };
                            reader.readAsDataURL(file);
                            return fileSrc !== imgSrc;
                        }
                    });
                    container.remove();
                    updateFileInput();
                }

                function updateFileInput() {
                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach(file => {
                        if (!file.src) {
                            dataTransfer.items.add(file);
                        }
                    });
                    galleryInput.files = dataTransfer.files;
                }

                // Attach remove event to existing images
                document.querySelectorAll('.remove-image').forEach(btn => {
                    btn.addEventListener('click', removeImage);
                });
            }

            // Modal image change
            window.changeModalImage = function(element) {
                const largeImage = element.closest('.modal-body').querySelector('.modal-image');
                largeImage.src = element.src;
            };
        });
    </script>
    <?php ob_end_flush(); ?>