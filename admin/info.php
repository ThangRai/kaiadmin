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
    header("Location: info.php");
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
    $info_id = $_POST['info_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE info SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $info_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Toggle status error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: info.php");
    exit;
}

// Handle update position
if (isset($_POST['update_position'])) {
    $info_id = $_POST['info_id'];
    $new_position = $_POST['new_position'];
    try {
        $stmt = $pdo->prepare("UPDATE info SET position = ? WHERE id = ?");
        $stmt->execute([$new_position, $info_id]);
        $_SESSION['toast_message'] = 'Cập nhật thứ tự thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Update position error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật thứ tự!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: info.php");
    exit;
}

// Handle update display position
if (isset($_POST['update_display_position'])) {
    $info_id = $_POST['info_id'];
    $stmt = $pdo->prepare("SELECT display_position FROM info WHERE id = ?");
    $stmt->execute([$info_id]);
    $current_positions = $stmt->fetchColumn();
    $current_positions_array = $current_positions ? explode(',', $current_positions) : [];
    $new_positions = isset($_POST['display_position']) ? $_POST['display_position'] : [];
    $merged_positions = array_unique(array_merge($current_positions_array, $new_positions));
    $display_position = implode(',', $merged_positions);
    try {
        $stmt = $pdo->prepare("UPDATE info SET display_position = ? WHERE id = ?");
        $stmt->execute([$display_position, $info_id]);
        $_SESSION['toast_message'] = 'Cập nhật vị trí hiển thị thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Update display position error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật vị trí!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: info.php");
    exit;
}

// Handle remove display position
if (isset($_POST['remove_display_position'])) {
    $info_id = $_POST['info_id'];
    $position_to_remove = $_POST['position_to_remove'];
    try {
        $stmt = $pdo->prepare("SELECT display_position FROM info WHERE id = ?");
        $stmt->execute([$info_id]);
        $current_positions = $stmt->fetchColumn();
        $positions = $current_positions ? explode(',', $current_positions) : [];
        $positions = array_filter($positions, function($pos) use ($position_to_remove) {
            return $pos !== $position_to_remove;
        });
        $new_positions = implode(',', $positions);
        $stmt = $pdo->prepare("UPDATE info SET display_position = ? WHERE id = ?");
        $stmt->execute([$new_positions, $info_id]);
        $_SESSION['toast_message'] = 'Xóa vị trí hiển thị thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Remove display position error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi xóa vị trí!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: info.php");
    exit;
}

// Handle delete info
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM info WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $_SESSION['toast_message'] = $result ? 'Xóa thông tin thành công!' : 'Xóa thông tin thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        log_debug('Delete error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi xóa!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: info.php");
    exit;
}

// Handle add/edit info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_info']) || isset($_POST['edit_info']))) {
    $pdo->beginTransaction();
    try {
        $title_vi = trim($_POST['title_vi']);
        $parent_id = $_POST['parent_id'] ?: 0; // Cho phép chọn danh mục cha thuộc module Giới thiệu
        $module_id = 3; // Chỉ giới hạn module Giới thiệu
        $display_position = isset($_POST['display_position']) ? implode(',', $_POST['display_position']) : '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $h1_content = trim($_POST['h1_content']);
        $description_vi = $_POST['description_vi'];
        $content_vi = $_POST['content_vi'];
        $slug_vi = trim($_POST['slug_vi']) ?: generate_slug($title_vi);
        $link_vi = trim($_POST['link_vi']);
        $link_target = $_POST['link_target'];
        $seo_title_vi = trim($_POST['seo_title_vi']);
        $seo_description_vi = trim($_POST['seo_description_vi']);
        $seo_keywords_vi = trim($_POST['seo_keywords_vi']);
        $position = trim($_POST['position']);

        // Handle gallery images upload
        $upload_dir = 'uploads/';
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
            $gallery_images = null;
        }

        if (isset($_POST['add_info'])) {
            $stmt = $pdo->prepare("INSERT INTO info (title_vi, parent_id, module_id, display_position, is_active, h1_content, description_vi, content_vi, slug_vi, link_vi, link_target, seo_title_vi, seo_description_vi, seo_keywords_vi, gallery_images, position, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title_vi, $parent_id, $module_id, $display_position, $is_active, $h1_content, $description_vi, $content_vi, $slug_vi, $link_vi, $link_target, $seo_title_vi, $seo_description_vi, $seo_keywords_vi, $gallery_images, $position]);
            log_debug("Added info: $title_vi");
        } else {
            $info_id = $_POST['info_id'];
            $stmt = $pdo->prepare("UPDATE info SET title_vi = ?, parent_id = ?, module_id = ?, display_position = ?, is_active = ?, h1_content = ?, description_vi = ?, content_vi = ?, slug_vi = ?, link_vi = ?, link_target = ?, seo_title_vi = ?, seo_description_vi = ?, seo_keywords_vi = ?, gallery_images = ?, position = ? WHERE id = ?");
            $stmt->execute([$title_vi, $parent_id, $module_id, $display_position, $is_active, $h1_content, $description_vi, $content_vi, $slug_vi, $link_vi, $link_target, $seo_title_vi, $seo_description_vi, $seo_keywords_vi, $gallery_images ?: null, $position, $info_id]);
            log_debug("Updated info ID: $info_id");
        }

        $pdo->commit();
        $_SESSION['toast_message'] = isset($_POST['add_info']) ? 'Thêm thông tin thành công!' : 'Cập nhật thông tin thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Save error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: info.php");
    exit;
}

// Fetch parent categories (only from module_id = 3 and active)
$stmt = $pdo->query("SELECT id, title_vi FROM categories WHERE module_id = 3 AND is_active = 1 ORDER BY position ASC");
$parent_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch info with filters
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$display_position_filter = isset($_GET['display_position']) ? $_GET['display_position'] : '';
$is_active_filter = isset($_GET['is_active']) ? $_GET['is_active'] : '';

$where = ['1=1'];
$params = [];
if ($keyword) {
    $where[] = "title_vi LIKE ?";
    $params[] = "%$keyword%";
}
if ($display_position_filter) {
    $where[] = "display_position LIKE ?";
    $params[] = "%$display_position_filter%";
}
if ($is_active_filter !== '') {
    $where[] = "is_active = ?";
    $params[] = $is_active_filter;
}

$query = "SELECT * FROM info WHERE " . implode(' AND ', $where) . " ORDER BY position ASC, id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$infos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display positions
$display_positions = [
    'menu_top' => 'Menu Top',
    'menu_main' => 'Menu Main',
    'trang_chu' => 'Trang chủ',
    'an_tieu_de' => 'Ẩn tiêu đề',
    'lay_mo_ta' => 'Lấy mô tả',
    'lay_noi_dung' => 'Lấy nội dung',
    'noi_bat' => 'Nổi bật',
    'duoi' => 'Dưới'
];

// Get active state
$method = isset($_GET['method']) ? $_GET['method'] : 'list';
$edit_info = null;
if ($method === 'frm' && isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM info WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_info = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Thông tin - Kaiadmin</title>
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
    <script src="ckeditor/ckeditor.js"></script>
    <style>
        .add-btn { position: absolute; top: 20px; right: 20px; }
        .table-responsive { margin-bottom: 1rem; }
        .form-group label { font-weight: 500; }
        .form-header { margin-bottom: 20px; }
        .image-preview { max-width: 100px; height: auto; margin-top: 10px; }
        .gallery-preview { display: flex; flex-wrap: wrap; gap: 10px; }
        .gallery-preview img { width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; }
        .gallery-upload { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; }
        .gallery-upload .upload-box { width: 100px; height: 100px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; cursor: pointer; background: #f9f9f9; }
        .char-count { font-size: 0.9em; color: #666; }
        .position-input { width: 60px; }
        .display-position-list { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 5px; }
        .display-position-item { background: #e9ecef; padding: 2px 8px; border-radius: 3px; display: flex; align-items: center; }
        .remove-position { cursor: pointer; color: red; margin-left: 5px; text-decoration: none; }
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
                        <h3 class="fw-bold mb-3">Quản lý Thông tin</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Thông tin</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($method === 'frm'): ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $edit_info ? 'Sửa Thông tin' : 'Thêm Thông tin'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="form-header d-flex justify-content-end">
                                                <button type="submit" name="<?php echo $edit_info ? 'edit_info' : 'add_info'; ?>" class="btn btn-primary">Lưu</button>
                                                <a href="info.php" class="btn btn-secondary ml-2">Hủy</a>
                                            </div>
                                            <div class="row">
                                                <!-- Left Column (3/12) -->
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Module</label>
                                                        <input type="hidden" name="module_id" value="3">
                                                        <input type="text" class="form-control" value="Giới thiệu (ID: 3)" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="parent_id">Danh mục cha</label>
                                                        <select id="parent_id" name="parent_id" class="form-control">
                                                            <option value="0">Không có</option>
                                                            <?php foreach ($parent_categories as $parent): ?>
                                                                <option value="<?php echo $parent['id']; ?>" <?php echo $edit_info && $edit_info['parent_id'] == $parent['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($parent['title_vi']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Vị trí hiển thị</label>
                                                        <?php foreach ($display_positions as $key => $value): ?>
                                                            <div>
                                                                <input type="checkbox" name="display_position[]" id="dp_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo $edit_info && in_array($key, explode(',', $edit_info['display_position'] ?? '')) ? 'checked' : ''; ?>>
                                                                <label for="dp_<?php echo $key; ?>"><?php echo $value; ?></label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hiển thị</label>
                                                        <div>
                                                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $edit_info && $edit_info['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="is_active">Bật</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Thư viện ảnh</label>
                                                        <input type="file" id="gallery_images" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                                                        <div class="gallery-upload" id="gallery-upload">
                                                            <div class="upload-box" onclick="document.getElementById('gallery_images').click()">+</div>
                                                        </div>
                                                        <?php if ($edit_info && $edit_info['gallery_images']): ?>
                                                            <div class="gallery-preview">
                                                                <?php foreach (explode(',', $edit_info['gallery_images']) as $img): ?>
                                                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Gallery Image">
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="position">Sắp xếp</label>
                                                        <input type="number" id="position" name="position" class="form-control" value="<?php echo $edit_info ? htmlspecialchars($edit_info['position']) : '0'; ?>" required min="0">
                                                    </div>
                                                </div>
                                                <!-- Right Column (7/12) -->
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        <label for="title_vi">Tên tiếng Việt</label>
                                                        <input type="text" id="title_vi" name="title_vi" class="form-control" value="<?php echo $edit_info ? htmlspecialchars($edit_info['title_vi']) : ''; ?>" required placeholder="Nhập tên thông tin">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="h1_content">Nội dung thẻ H1</label>
                                                        <input type="text" id="h1_content" name="h1_content" class="form-control" value="<?php echo $edit_info ? htmlspecialchars($edit_info['h1_content']) : ''; ?>" placeholder="Nhập nội dung H1">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="description_vi">Mô tả tiếng Việt</label>
                                                        <textarea id="description_vi" name="description_vi" class="form-control"><?php echo $edit_info ? htmlspecialchars($edit_info['description_vi']) : ''; ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="content_vi">Nội dung tiếng Việt</label>
                                                        <textarea id="content_vi" name="content_vi" class="form-control"><?php echo $edit_info ? htmlspecialchars($edit_info['content_vi']) : ''; ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="slug_vi">Đường dẫn tiếng Việt</label>
                                                        <input type="text" id="slug_vi" name="slug_vi" class="form-control" value="<?php echo $edit_info ? htmlspecialchars($edit_info['slug_vi']) : ''; ?>" placeholder="Tự sinh từ tên nếu để trống">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_vi">Liên kết tiếng Việt</label>
                                                        <input type="text" id="link_vi" name="link_vi" class="form-control" value="<?php echo $edit_info ? htmlspecialchars($edit_info['link_vi']) : ''; ?>" placeholder="VD: /trang-chu">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_target">Phương thức</label>
                                                        <select id="link_target" name="link_target" class="form-control">
                                                            <option value="_self" <?php echo $edit_info && $edit_info['link_target'] == '_self' ? 'selected' : ''; ?>>Mở trang hiện tại</option>
                                                            <option value="_blank" <?php echo $edit_info && $edit_info['link_target'] == '_blank' ? 'selected' : ''; ?>>Mở trang mới</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="seo_title_vi">Tiêu đề trang SEO</label>
                                                        <input type="text" id="seo_title_vi" name="seo_title_vi" class="form-control" value="<?php echo $edit_info ? htmlspecialchars($edit_info['seo_title_vi']) : ''; ?>" placeholder="Nhập tiêu đề SEO">
                                                        <span class="char-count" id="seo_title_count">0/70 ký tự</span>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="seo_description_vi">Mô tả meta SEO</label>
                                                        <textarea id="seo_description_vi" name="seo_description_vi" class="form-control"><?php echo $edit_info ? htmlspecialchars($edit_info['seo_description_vi']) : ''; ?></textarea>
                                                        <span class="char-count" id="seo_description_count">0/160 ký tự</span>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="seo_keywords_vi">Từ khóa meta</label>
                                                        <input type="text" id="seo_keywords_vi" name="seo_keywords_vi" class="form-control" value="<?php echo $edit_info ? htmlspecialchars($edit_info['seo_keywords_vi']) : ''; ?>" placeholder="Nhập từ khóa, cách nhau bằng dấu phẩy">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($edit_info): ?>
                                                <input type="hidden" name="info_id" value="<?php echo $edit_info['id']; ?>">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách Thông tin</h4>
                                    </div>
                                    <div class="card-body">
                                        <a href="?method=frm" class="btn btn-primary add-btn">Thêm Thông tin</a>
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
                                                        <label for="display_position">Vị trí hiển thị</label>
                                                        <select id="display_position" name="display_position" class="form-control">
                                                            <option value="">Tất cả</option>
                                                            <?php foreach ($display_positions as $key => $value): ?>
                                                                <option value="<?php echo $key; ?>" <?php echo $display_position_filter == $key ? 'selected' : ''; ?>>
                                                                    <?php echo $value; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
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
                                                        <a href="info.php" class="btn btn-secondary">Reset</a>
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
                                                        <th>Chức năng khác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($infos as $info): ?>
                                                        <tr>
                                                            <td><?php echo $info['id']; ?></td>
                                                            <td>
                                                                <form method="POST" action="info.php">
                                                                    <input type="hidden" name="info_id" value="<?php echo $info['id']; ?>">
                                                                    <input type="hidden" name="update_position" value="1">
                                                                    <input type="number" class="form-control position-input" name="new_position" value="<?php echo htmlspecialchars($info['position']); ?>" min="0">
                                                                    <button type="submit" class="btn btn-sm btn-primary mt-1">Cập nhật</button>
                                                                </form>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($info['title_vi']); ?></td>
                                                            <td>
                                                                <?php if ($info['gallery_images']): ?>
                                                                    <div class="gallery-preview">
                                                                        <?php foreach (explode(',', $info['gallery_images']) as $img): ?>
                                                                            <img src="<?php echo htmlspecialchars($img); ?>" alt="Gallery Image">
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <form method="POST" action="info.php">
                                                                    <input type="hidden" name="info_id" value="<?php echo $info['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $info['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?method=frm&edit_id=<?php echo $info['id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                                                <a href="?delete_id=<?php echo $info['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                                                            </td>
                                                            <td>
                                                                <div class="display-position-list">
                                                                    <?php if ($info['display_position']): ?>
                                                                        <?php foreach (explode(',', $info['display_position']) as $pos): ?>
                                                                            <?php if (isset($display_positions[$pos])): ?>
                                                                                <span class="display-position-item">
                                                                                    <?php echo htmlspecialchars($display_positions[$pos]); ?>
                                                                                    <form method="POST" action="info.php" style="display:inline;">
                                                                                        <input type="hidden" name="info_id" value="<?php echo $info['id']; ?>">
                                                                                        <input type="hidden" name="position_to_remove" value="<?php echo $pos; ?>">
                                                                                        <input type="hidden" name="remove_display_position" value="1">
                                                                                        <button type="submit" class="remove-position">x</button>
                                                                                    </form>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <form method="POST" action="info.php">
                                                                    <input type="hidden" name="info_id" value="<?php echo $info['id']; ?>">
                                                                    <input type="hidden" name="update_display_position" value="1">
                                                                    <select name="display_position[]" class="form-control">
                                                                        <?php foreach ($display_positions as $key => $value): ?>
                                                                            <option value="<?php echo $key; ?>">
                                                                                <?php echo htmlspecialchars($value); ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <button type="submit" class="btn btn-sm btn-primary mt-1">Thêm</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">    <!-- iZitoast JS -->
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

            CKEDITOR.replace('description_vi');
            CKEDITOR.replace('content_vi');

            function updateCharCount(inputId, countId, maxLength) {
                const input = document.getElementById(inputId);
                const count = document.getElementById(countId);
                if (input && count) {
                    count.textContent = `${input.value.length}/${maxLength} ký tự`;
                    input.addEventListener('input', () => {
                        count.textContent = `${input.value.length}/${maxLength} ký tự`;
                    });
                }
            }
            updateCharCount('seo_title_vi', 'seo_title_count', 70);
            updateCharCount('seo_description_vi', 'seo_description_count', 160);

            const galleryInput = document.getElementById('gallery_images');
            const galleryUpload = document.getElementById('gallery-upload');
            if (galleryInput && galleryUpload) {
                galleryInput.addEventListener('change', function() {
                    galleryUpload.innerHTML = '<div class="upload-box" onclick="document.getElementById(\'gallery_images\').click()">+</div>';
                    for (const file of this.files) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.width = '100px';
                            img.style.height = '100px';
                            img.style.objectFit = 'cover';
                            img.style.border = '1px solid #ddd';
                            galleryUpload.insertBefore(img, galleryUpload.firstChild);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
    <?php ob_end_flush(); ?>