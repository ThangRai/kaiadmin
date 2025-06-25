<?php
ob_start();
session_start();
require 'admin/database/config.php';
require_once 'admin/include/functions.php';

// Lấy tham số page từ URL
$page = isset($_GET['page']) ? trim($_GET['page']) : 'trang-chu';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$segments = array_filter(explode('/', trim($page, '/')));

// Xử lý định tuyến
$module_id = null;
$template = 'home.php'; // Mặc định là trang chủ
$column_settings = [];

// Lấy cấu hình cột từ bảng column_settings
try {
    $stmt = $pdo->prepare("SELECT * FROM column_settings WHERE content_type IN ('products', 'services', 'info', 'faq', 'content', 'project', 'testimonials')");
    $stmt->execute();
    $column_settings_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($column_settings_raw as $setting) {
        $column_settings[$setting['content_type']] = [
            'tiny' => $setting['items_per_row_tiny'],
            'sm' => $setting['items_per_row_sm'],
            'md' => $setting['items_per_row_md'],
            'lg' => $setting['items_per_row_lg'],
            'xl' => $setting['items_per_row_xl']
        ];
    }
} catch (PDOException $e) {
    $column_settings = [
        'products' => ['tiny' => 1, 'sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 3],
        'services' => ['tiny' => 3, 'sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 6],
        'info' => ['tiny' => 2, 'sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 6],
        'faq' => ['tiny' => 1, 'sm' => 1, 'md' => 2, 'lg' => 2, 'xl' => 2],
        'content' => ['tiny' => 1, 'sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 3],
        'project' => ['tiny' => 1, 'sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 3],
        'testimonials' => ['tiny' => 1, 'sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 3]
    ];
}

// Xác định template dựa trên URL
try {
    if ($search_query) {
        $template = 'search.php';
        $module_id = null;
    } elseif (count($segments) === 0 || $segments[0] === 'trang-chu') {
        $template = 'home.php';
        $module_id = 2;
    } else {
        $stmt = $pdo->prepare("SELECT c.*, m.option_name, m.id AS module_id FROM categories c LEFT JOIN modules m ON c.module_id = m.id WHERE c.slug_vi = ? AND c.is_active = 1");
        $stmt->execute([$segments[0]]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($category) {
            $module_id = $category['module_id'];
            switch ($category['option_name']) {
                case 'intro':
                    $template = count($segments) > 1 ? 'intro.php' : 'intro.php';
                    break;
                case 'product':
                    $template = count($segments) > 1 ? 'product.php' : 'product.php';
                    break;
                case 'service':
                    $template = count($segments) > 1 ? 'service.php' : 'service.php';
                    break;
                case 'faq':
                    $template = 'faq.php';
                    break;
                case 'content':
                    $template = count($segments) > 1 ? 'content.php' : 'content.php';
                    break;
                case 'project':
                    $template = count($segments) > 1 ? 'project.php' : 'project.php';
                    break;
                case 'testimonial':
                    $template = 'testimonial.php';
                    break;
                default:
                    throw new Exception('404');
            }
        } else {
            throw new Exception('404');
        }
    }

    // Kiểm tra template tồn tại
    if (!file_exists($template)) {
        throw new Exception('404');
    }
} catch (Exception $e) {
    $template = '404.php';
    $module_id = null;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['seo_title_vi'] ?? ($content['title_vi'] ?? 'Kaiadmin')); ?> - Kaiadmin</title>
    <meta name="description" content="<?php echo htmlspecialchars($content['seo_description_vi'] ?? ($content['description_vi'] ?? '')); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <style>
        body {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
    <?php
    // CSS riêng cho từng trang
    $page_slug = $segments[0] ?? 'trang-chu';
    switch ($page_slug) {
        case 'trang-chu':
            echo '<link rel="stylesheet" href="assets/css/pages/home.css">';
            break;
        case 'gioi-thieu':
            echo '<link rel="stylesheet" href="assets/css/pages/intro.css">';
            break;
        case 'san-pham':
            echo '<link rel="stylesheet" href="assets/css/pages/product.css">';
            break;
        case 'dich-vu':
            echo '<link rel="stylesheet" href="assets/css/pages/service.css">';
            break;
        case 'faq':
            echo '<link rel="stylesheet" href="assets/css/pages/faq.css">';
            break;
        case 'noi-dung':
            echo '<link rel="stylesheet" href="assets/css/pages/content.css">';
            break;
        case 'du-an':
            echo '<link rel="stylesheet" href="assets/css/pages/project.css">';
            break;
        case 'y-kien-khach-hang':
            echo '<link rel="stylesheet" href="assets/css/pages/testimonial.css">';
            break;
        case 'search':
            echo '<link rel="stylesheet" href="assets/css/pages/search.css">';
            break;
        default:
            echo '<link rel="stylesheet" href="assets/css/pages/default.css">';
            break;
    }
    ?>
    <style>
        .page-content { margin: 20px 0; }
        .page-content img { max-width: 100%; height: auto; }
        .slideshow img { width: 100%; height: auto; }
        <?php
        // Áp dụng cấu hình cột
        foreach ($column_settings as $type => $cols) {
            echo "
                .{$type}-list .{$type}-item {
                    flex: 0 0 calc(100% / {$cols['tiny']});
                    max-width: calc(100% / {$cols['tiny']});
                }
                @media (min-width: 576px) {
                    .{$type}-list .{$type}-item {
                        flex: 0 0 calc(100% / {$cols['sm']});
                        max-width: calc(100% / {$cols['sm']});
                    }
                }
                @media (min-width: 768px) {
                    .{$type}-list .{$type}-item {
                        flex: 0 0 calc(100% / {$cols['md']});
                        max-width: calc(100% / {$cols['md']});
                    }
                }
                @media (min-width: 992px) {
                    .{$type}-list .{$type}-item {
                        flex: 0 0 calc(100% / {$cols['lg']});
                        max-width: calc(100% / {$cols['lg']});
                    }
                }
                @media (min-width: 1200px) {
                    .{$type}-list .{$type}-item {
                        flex: 0 0 calc(100% / {$cols['xl']});
                        max-width: calc(100% / {$cols['xl']});
                    }
                }
            ";
        }
        ?>
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <?php include 'include/header.php'; ?>

        <!-- Nội dung chính -->
        <div class="page-content">
            <?php include $template; ?>
        </div>

        <!-- Footer -->
        <?php include 'include/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/kaiadmin.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>