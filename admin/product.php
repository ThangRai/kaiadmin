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
    header("Location: product.php");
    exit;
}

// Generate slug with Vietnamese accents, removing "quả" prefix
function generate_slug($string) {
    $string = trim($string);
    $string = mb_strtolower($string, 'UTF-8');
    // Remove "quả" prefix if it exists
    $string = preg_replace('/^quả\s+/u', '', $string);
    $string = preg_replace('/[^a-z0-9áàảãạăắằẳẴạâấẦẩẫẬéèẻẽẹêếềểễệíìỉĩịóòỏõọôốồổỗộơớờởỡợúùủũụưứừửữựýỳỷỹỵđ-]/u', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Handle toggle status
if (isset($_POST['toggle_status'])) {
    $product_id = $_POST['product_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $product_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Toggle status error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: product.php");
    exit;
}

// Handle update position
if (isset($_POST['update_position'])) {
    $product_id = $_POST['product_id'];
    $new_position = $_POST['new_position'];
    try {
        $stmt = $pdo->prepare("UPDATE products SET position = ? WHERE id = ?");
        $stmt->execute([$new_position, $product_id]);
        $_SESSION['toast_message'] = 'Cập nhật thứ tự thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Update position error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi cập nhật thứ tự!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: product.php");
    exit;
}

// Handle add/edit product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_product']) || isset($_POST['edit_product']))) {
    $pdo->beginTransaction();
    try {
        $title_vi = trim($_POST['title_vi']);
        $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
        $product_code = trim($_POST['product_code']);
        $current_price = trim($_POST['current_price']) ?: 0;
        $original_price = trim($_POST['original_price']) ?: 0;
        $weight = trim($_POST['weight']) ?: 0;
        $display_position = isset($_POST['display_position']) ? implode(',', $_POST['display_position']) : '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $stock_status = $_POST['stock_status'] === 'in_stock' ? 'in_stock' : 'out_of_stock';
        $h1_content = trim($_POST['h1_content']);
        $description_vi = $_POST['description_vi'];
        $content_vi = $_POST['content_vi'];
        $slug_vi = trim($_POST['slug_vi']) ?: generate_slug($title_vi);
        $link_vi = trim($_POST['link_vi']);
        $link_target = $_POST['link_target'];
        $seo_title_vi = trim($_POST['seo_title_vi']);
        $seo_description_vi = trim($_POST['seo_description_vi']);
        $seo_keywords_vi = trim($_POST['seo_keywords_vi']);
        $position = trim($_POST['position']) ?: 0;

        // Handle file uploads
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $gallery_images = null;

        $gallery_images_array = [];
        if (!empty($_FILES['gallery_images']['name'][0])) {
            foreach ($_FILES['gallery_images']['name'] as $key => $name) {
                if ($_FILES['gallery_images']['error'][$key] == 0) {
                    $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($file_extension, $allowed_extensions)) {
                        throw new Exception("Ảnh gallery chỉ cho phép định dạng JPG, JPEG, PNG hoặc GIF.");
                    }
                    $gallery_name = time() . '_gallery_' . $key . '_' . basename($name);
                    $gallery_path = $upload_dir . $gallery_name;
                    if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], $gallery_path)) {
                        $gallery_images_array[] = '/kai/admin/uploads/' . $gallery_name;
                    }
                }
            }
            $gallery_images = implode(',', $gallery_images_array);
        }

        // Xử lý category_id
        $default_category_id = !empty($category_ids) ? $category_ids[0] : 0; // Giá trị mặc định, có thể thay đổi

        if (isset($_POST['add_product'])) {
            $stmt = $pdo->prepare("INSERT INTO products (title_vi, product_code, current_price, original_price, weight, display_position, is_active, stock_status, h1_content, description_vi, content_vi, slug_vi, link_vi, link_target, seo_title_vi, seo_description_vi, seo_keywords_vi, gallery_images, position, created_at, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            $stmt->execute([$title_vi, $product_code, $current_price, $original_price, $weight, $display_position, $is_active, $stock_status, $h1_content, $description_vi, $content_vi, $slug_vi, $link_vi, $link_target, $seo_title_vi, $seo_description_vi, $seo_keywords_vi, $gallery_images, $position, $default_category_id]);
            $product_id = $pdo->lastInsertId();

            // Insert categories
            if (!empty($category_ids)) {
                foreach ($category_ids as $category_id) {
                    $stmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
                    $stmt->execute([$product_id, $category_id]);
                }
            }
            log_debug("Added product: $title_vi");
        } else {
            $product_id = $_POST['product_id'];
            $stmt = $pdo->prepare("UPDATE products SET title_vi = ?, product_code = ?, current_price = ?, original_price = ?, weight = ?, display_position = ?, is_active = ?, stock_status = ?, h1_content = ?, description_vi = ?, content_vi = ?, slug_vi = ?, link_vi = ?, link_target = ?, seo_title_vi = ?, seo_description_vi = ?, seo_keywords_vi = ?, gallery_images = ?, position = ?, category_id = ? WHERE id = ?");
            $stmt->execute([$title_vi, $product_code, $current_price, $original_price, $weight, $display_position, $is_active, $stock_status, $h1_content, $description_vi, $content_vi, $slug_vi, $link_vi, $link_target, $seo_title_vi, $seo_description_vi, $seo_keywords_vi, $gallery_images, $position, $default_category_id, $product_id]);

            // Update categories
            $stmt = $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?");
            $stmt->execute([$product_id]);
            if (!empty($category_ids)) {
                foreach ($category_ids as $category_id) {
                    $stmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
                    $stmt->execute([$product_id, $category_id]);
                }
            }
            log_debug("Updated product ID: $product_id");
        }

        $pdo->commit();
        $_SESSION['toast_message'] = isset($_POST['add_product']) ? 'Thêm sản phẩm thành công!' : 'Cập nhật sản phẩm thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Save error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: product.php");
    exit;
}

// Handle delete product
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->execute([$delete_id]);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $pdo->commit();
        $_SESSION['toast_message'] = $result ? 'Xóa sản phẩm thành công!' : 'Xóa sản phẩm thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Delete error: ' . $e->getMessage());
        $_SESSION['toast_message'] = 'Lỗi xóa!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: product.php");
    exit;
}

// Pagination
$per_page_options = [10, 25, 100, 500];
$per_page = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $per_page_options) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Fetch products with filters
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category_id_filter = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$display_position_filter = isset($_GET['display_position']) ? $_GET['display_position'] : '';
$stock_status_filter = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';
$is_active_filter = isset($_GET['is_active']) ? $_GET['is_active'] : '';

$where = ['1=1'];
$params = [];
if ($keyword) {
    $where[] = "(p.title_vi LIKE ?)";
    $params[] = "%$keyword%";
}
if ($category_id_filter) {
    $where[] = "EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)";
    $params[] = (int)$category_id_filter;
}
if ($display_position_filter) {
    $where[] = "p.display_position LIKE ?";
    $params[] = "%$display_position_filter%";
}
if ($stock_status_filter && in_array($stock_status_filter, ['in_stock', 'out_of_stock'])) {
    $where[] = "p.stock_status = ?";
    $params[] = $stock_status_filter;
}
if ($is_active_filter && in_array($is_active_filter, ['0', '1'])) {
    $where[] = "p.is_active = ?";
    $params[] = (int)$is_active_filter;
}

// Count total products
$count_query = "SELECT COUNT(*) FROM products p WHERE " . implode(' AND ', $where);
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_products = $stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Fetch products with validated LIMIT and OFFSET
$query = "SELECT p.*, GROUP_CONCAT(c.title_vi SEPARATOR ', ') AS category_titles 
          FROM products p 
          LEFT JOIN product_categories pc ON p.id = pc.product_id 
          LEFT JOIN categories c ON pc.category_id = c.id 
          WHERE " . implode(' AND ', $where) . " 
          GROUP BY p.id 
          ORDER BY p.position ASC, p.id DESC 
          LIMIT " . intval($per_page) . " OFFSET " . intval($offset);
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug log
log_debug("Query: $query, Params: " . print_r($params, true));

// Fetch categories for filter and form (only for module "Sản phẩm")
$stmt = $pdo->query("SELECT c.id, c.title_vi FROM categories c INNER JOIN modules m ON c.module_id = m.id WHERE m.title = 'Sản phẩm' ORDER BY c.position ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Stock status options
$stock_status_options = [
    'in_stock' => 'Còn hàng',
    'out_of_stock' => 'Hết hàng'
];

// Get active state
$method = isset($_GET['method']) ? $_GET['method'] : 'list';

// Fetch product for edit
$edit_product = null;
$edit_category_ids = [];
if ($method === 'frm' && isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT category_id FROM product_categories WHERE product_id = ?");
    $stmt->execute([$edit_id]);
    $edit_category_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id');
}

// Calculate SEO score
function calculate_seo_score($title, $description, $keywords) {
    $score = 0;
    if (strlen($title) >= 10 && strlen($title) <= 70) $score += 30;
    if (strlen($description) >= 50 && strlen($description) <= 160) $score += 30;
    if (!empty($keywords)) $score += 20;
    if (strlen($title) > 0) $score += 10;
    if (strlen($description) > 0) $score += 10;
    return min($score, 100);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Sản phẩm - Kaiadmin</title>
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
    <!-- CKEditor -->
    <script src="ckeditor/ckeditor.js"></script>
    <style>
        .col-md-2 {
    padding: unset !important;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
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
        .seo-score {
            color: green;
            font-weight: bold;
        }
        .image-preview {
            max-width: 70px;
            height: auto;
            margin-top: 10px;
        }
        .gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .gallery-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        .gallery-upload {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }
        .gallery-upload .upload-box {
            width: 100px;
            height: 100px;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: #f9f9f9;
        }
        .char-count {
            font-size: 0.9em;
            color: #666;
        }
        .position-input {
            width: 80px;
        }
        .pagination {
            margin-top: 20px;
        }
        /* CKEditor custom icon colors */
        .cke_button__bold_icon { filter: hue-rotate(120deg); } /* Green */
        .cke_button__italic_icon { filter: hue-rotate(0deg); } /* Red */
        .cke_button__underline_icon { filter: hue-rotate(240deg); } /* Blue */
        .cke_button__link_icon { filter: hue-rotate(60deg); } /* Yellow */
        .cke_button__image_icon { filter: hue-rotate(300deg); } /* Purple */
        @media (max-width: 768px) {
            .add-btn {
                position: static;
                margin-bottom: 15px;
            }
            .form-header .btn-primary {
                padding: 6px;
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
                        <h3 class="fw-bold mb-3">Quản lý Sản phẩm</h3>
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
                                <a href="#">Sản phẩm</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($method === 'frm'): ?>
                            <!-- Form Add/Edit Product -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $edit_product ? 'Sửa Sản phẩm' : 'Thêm Sản phẩm'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="form-header d-flex justify-content-end">
                                                <button type="submit" name="<?php echo $edit_product ? 'edit_product' : 'add_product'; ?>" class="btn btn-primary">Lưu</button>
                                                <a href="product.php" class="btn btn-secondary ml-2">Hủy</a>
                                            </div>
                                            <div class="row">
                                                <!-- Left Column (3/12) -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="category_ids">Danh mục</label>
                                                        <select id="category_ids" name="category_ids[]" class="form-control">
                                                            <?php foreach ($categories as $category): ?>
                                                                <option value="<?php echo $category['id']; ?>" <?php echo in_array($category['id'], $edit_category_ids) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($category['title_vi']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="product_code">Mã sản phẩm</label>
                                                        <input type="text" id="product_code" name="product_code" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['product_code']) : ''; ?>" placeholder="VD: SP001">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="current_price">Giá hiện tại</label>
                                                        <input type="number" id="current_price" name="current_price" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['current_price']) : '0.00'; ?>" min="0" step="0.01" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="original_price">Giá gốc</label>
                                                        <input type="number" id="original_price" name="original_price" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['original_price']) : '0.00'; ?>" min="0" step="0.01">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="weight">Trọng lượng (kg)</label>
                                                        <input type="number" id="weight" name="weight" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['weight']) : '0.00'; ?>" min="0" step="0.01">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Vị trí hiển thị</label>
                                                        <?php foreach ($display_positions as $key => $value): ?>
                                                            <div>
                                                                <input type="checkbox" name="display_position[]" id="dp_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo $edit_product && in_array($key, explode(',', $edit_product['display_position'] ?? '')) ? 'checked' : ''; ?>>
                                                                <label for="dp_<?php echo $key; ?>"><?php echo $value; ?></label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="position">Sắp xếp</label>
                                                        <input type="number" id="position" name="position" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['position']) : '0'; ?>" required min="0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hiển thị</label>
                                                        <div>
                                                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $edit_product && $edit_product['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="is_active">Bật</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Trạng thái hàng</label>
                                                        <div>
                                                            <input type="radio" name="stock_status" id="in_stock" value="in_stock" <?php echo !$edit_product || $edit_product['stock_status'] == 'in_stock' ? 'checked' : ''; ?> required>
                                                            <label for="in_stock">Còn hàng</label>
                                                        </div>
                                                        <div>
                                                            <input type="radio" name="stock_status" id="out_of_stock" value="out_of_stock" <?php echo $edit_product && $edit_product['stock_status'] == 'out_of_stock' ? 'checked' : ''; ?>>
                                                            <label for="out_of_stock">Hết hàng</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hình ảnh sản phẩm</label>
                                                        <input type="file" id="gallery_images" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                                                        <div class="gallery-upload" id="gallery-upload">
                                                            <div class="upload-box" onclick="document.getElementById('gallery_images').click()">+</div>
                                                        </div>
                                                        <?php if ($edit_product && $edit_product['gallery_images']): ?>
                                                            <div class="gallery-preview">
                                                                <?php foreach (explode(',', $edit_product['gallery_images']) as $img): ?>
                                                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Gallery Image">
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <!-- Right Column (9/12) -->
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <label for="title_vi">Tên tiếng Việt</label>
                                                        <input type="text" id="title_vi" name="title_vi" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['title_vi']) : ''; ?>" required placeholder="Nhập tên sản phẩm">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="h1_content">Nội dung thẻ H1</label>
                                                        <input type="text" id="h1_content" name="h1_content" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['h1_content']) : ''; ?>" placeholder="Nhập nội dung H1">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="description_vi">Mô tả tiếng Việt</label>
                                                        <textarea id="description_vi" name="description_vi" class="form-control"><?php echo $edit_product ? htmlspecialchars($edit_product['description_vi']) : ''; ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="content_vi">Nội dung tiếng Việt</label>
                                                        <textarea id="content_vi" name="content_vi" class="form-control"><?php echo $edit_product ? htmlspecialchars($edit_product['content_vi']) : ''; ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="slug_vi">Đường dẫn tiếng Việt</label>
                                                        <input type="text" id="slug_vi" name="slug_vi" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['slug_vi']) : ''; ?>" placeholder="Tự sinh từ tên nếu để trống">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_vi">Liên kết tiếng Việt</label>
                                                        <input type="text" id="link_vi" name="link_vi" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['link_vi']) : ''; ?>" placeholder="VD: /san-pham-moi">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="link_target">Phương thức</label>
                                                        <select id="link_target" name="link_target" class="form-control">
                                                            <option value="_self" <?php echo $edit_product && $edit_product['link_target'] == '_self' ? 'selected' : ''; ?>>Mở trang hiện tại</option>
                                                            <option value="_blank" <?php echo $edit_product && $edit_product['link_target'] == '_blank' ? 'selected' : ''; ?>>Mở trang mới</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="seo_title_vi">Tiêu đề trang SEO</label>
                                                        <input type="text" id="seo_title_vi" name="seo_title_vi" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['seo_title_vi']) : ''; ?>" placeholder="Nhập tiêu đề SEO">
                                                        <span class="char-count" id="seo_title_count">0/70 ký tự</span>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="seo_description_vi">Mô tả meta SEO</label>
                                                        <textarea id="seo_description_vi" name="seo_description_vi" class="form-control"><?php echo $edit_product ? htmlspecialchars($edit_product['seo_description_vi']) : ''; ?></textarea>
                                                        <span class="char-count" id="seo_description_count">0/160 ký tự</span>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="seo_keywords_vi">Từ khóa meta</label>
                                                        <input type="text" id="seo_keywords_vi" name="seo_keywords_vi" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['seo_keywords_vi']) : ''; ?>" placeholder="Nhập từ khóa, cách nhau bằng dấu phẩy">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($edit_product): ?>
                                                <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Product List -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách Sản phẩm</h4>
                                    </div>
                                    <div class="card-body">
                                        <a href="?method=frm" class="btn btn-primary add-btn">Thêm Sản phẩm</a>
                                        <!-- Filter Form -->
                                        <form method="GET" class="mb-4">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="keyword">Từ khóa</label>
                                                        <input type="text" id="keyword" name="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Nhập từ khóa">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="category_id">Danh mục</label>
                                                        <select id="category_id" name="category_id" class="form-control">
                                                            <option value="">Tất cả</option>
                                                            <?php foreach ($categories as $category): ?>
                                                                <option value="<?php echo $category['id']; ?>" <?php echo $category_id_filter == $category['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($category['title_vi']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
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
                                                        <label for="stock_status">Trạng thái hàng</label>
                                                        <select id="stock_status" name="stock_status" class="form-control">
                                                            <option value="">Tất cả</option>
                                                            <?php foreach ($stock_status_options as $key => $value): ?>
                                                                <option value="<?php echo $key; ?>" <?php echo $stock_status_filter == $key ? 'selected' : ''; ?>>
                                                                    <?php echo $value; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
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
                                                    <div class="form-group mt-4">
                                                        <button type="submit" class="btn btn-primary">Tìm</button>
                                                        <a href="product.php" class="btn btn-secondary">Reset</a>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="per_page">Sản phẩm mỗi trang</label>
                                                        <select id="per_page" name="per_page" class="form-control" onchange="this.form.submit()">
                                                            <?php foreach ($per_page_options as $option): ?>
                                                                <option value="<?php echo $option; ?>" <?php echo $per_page == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- Product Table -->
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <!-- <th>ID</th> -->
                                                        <th style="width:10%;">Sắp xếp</th>
                                                        <th>Tiêu đề</th>
                                                        <th>Danh mục</th>
                                                        <th>Hình</th>
                                                        <th>Giá</th>
                                                        <th>Hiển thị</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($products as $product): ?>
                                                        <?php
                                                        $product_category_ids = [];
                                                        $stmt = $pdo->prepare("SELECT category_id FROM product_categories WHERE product_id = ?");
                                                        $stmt->execute([$product['id']]);
                                                        $product_category_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id');
                                                        ?>
                                                        <tr>
                                                            <!-- <td><?php echo $product['id']; ?></td> -->
                                                        <td>
                                                            <form method="POST" action="product.php" style="display: flex; align-items: center; gap: 8px;">
                                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                                <input type="hidden" name="update_position" value="1">
                                                                <input type="number" class="form-control position-input" name="new_position"
                                                                    value="<?php echo htmlspecialchars($product['position']); ?>" min="0" style="width: 70px;">
                                                                <button type="submit" class="btn btn-sm btn-primary">Cập nhật</button>
                                                            </form>
                                                        </td>

                                                            <td>
                                                                <?php echo htmlspecialchars($product['title_vi']); ?>
                                                                <br>
                                                                <span class="seo-score">
                                                                    SEO: <?php echo calculate_seo_score($product['seo_title_vi'], $product['seo_description_vi'], $product['seo_keywords_vi']); ?>/100
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small>
                                                                    <?php
                                                                    $categories = explode(',', $product['category_titles'] ?? 'N/A');
                                                                    foreach ($categories as $cat) {
                                                                        echo htmlspecialchars(trim($cat)) . '<br>';
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </td>

                                                            <td>
                                                                <?php
                                                                $gallery = $product['gallery_images'] ? explode(',', $product['gallery_images']) : [];
                                                                echo !empty($gallery) ? '<img src="' . htmlspecialchars($gallery[0]) . '" class="image-preview" alt="Image">' : '-';
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <span style="white-space: nowrap;">Giá: <?php echo number_format($product['current_price'], 2); ?> đ</span><br>
                                                                <span style="white-space: nowrap;">Giá gốc: <?php echo number_format($product['original_price'], 2); ?> đ</span>
                                                            </td>

                                                            <td>
                                                                <form method="POST" action="product.php">
                                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $product['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?method=frm&edit_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="?delete_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- Pagination -->
                                        <div class="pagination d-flex justify-content-between align-items-center">
                                            <div>
                                                Hiển thị <?php echo min($total_products, ($page - 1) * $per_page + 1); ?> đến <?php echo min($total_products, $page * $per_page); ?> của <?php echo $total_products; ?> sản phẩm
                                            </div>
                                            <nav>
                                                <ul class="pagination">
                                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $per_page; ?>&keyword=<?php echo urlencode($keyword); ?>&category_id=<?php echo $category_id_filter; ?>&display_position=<?php echo $display_position_filter; ?>&stock_status=<?php echo $stock_status_filter; ?>&is_active=<?php echo $is_active_filter; ?>">Trước</a>
                                                    </li>
                                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                            <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>&keyword=<?php echo urlencode($keyword); ?>&category_id=<?php echo $category_id_filter; ?>&display_position=<?php echo $display_position_filter; ?>&stock_status=<?php echo $stock_status_filter; ?>&is_active=<?php echo $is_active_filter; ?>"><?php echo $i; ?></a>
                                                        </li>
                                                    <?php endfor; ?>
                                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $per_page; ?>&keyword=<?php echo urlencode($keyword); ?>&category_id=<?php echo $category_id_filter; ?>&display_position=<?php echo $display_position_filter; ?>&stock_status=<?php echo $stock_status_filter; ?>&is_active=<?php echo $is_active_filter; ?>">Sau</a>
                                                    </li>
                                                </ul>
                                            </nav>
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
            // iZitoast notification
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

            // Initialize Bootstrap Switch
            $(".toggle-switch").bootstrapSwitch({
                onText: 'Bật',
                offText: 'Tắt',
                onColor: 'success',
                offColor: 'danger',
                size: 'small'
            });

            // Initialize CKEditor
            CKEDITOR.replace('description_vi');
            CKEDITOR.replace('content_vi');

            // Character count for SEO fields
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

            // Gallery upload preview
            const galleryInput = document.getElementById('gallery_images');
            const galleryDiv = document.getElementById('gallery-upload');
            if (galleryInput && galleryDiv) {
                galleryInput.addEventListener('change', function() {
                    galleryDiv.innerHTML = '<div class="upload-box" onclick="document.getElementById(\'gallery_images\').click()">+</div>';
                    for (const file of this.files) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.width = '100px';
                            img.style.height = '100px';
                            img.style.objectFit = 'cover';
                            img.style.border = '1px solid #ddd';
                            galleryDiv.insertBefore(img, galleryDiv.firstChild);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
    <?php ob_end_flush(); ?>