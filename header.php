<?php
require 'admin/database/config.php'; // Đường dẫn đến file cấu hình cơ sở dữ liệu
require 'functions.php'; // Include file chứa hàm build_menu

// Check database connection
if (!$pdo) {
    die('Không thể kết nối đến cơ sở dữ liệu!');
}

// Fetch settings
$favicon = getSetting($pdo, 'favicon');
$colors = json_decode(getSetting($pdo, 'colors', '{}'), true);
$fonts = json_decode(getSetting($pdo, 'fonts', '{}'), true);

// Fetch modules
$stmt = $pdo->query("SELECT id, title, option_name FROM modules ORDER BY position ASC");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for menu (only with menu_main)
$stmt = $pdo->query("SELECT c.id, c.title_vi, c.slug_vi, c.parent_id, m.option_name 
                     FROM categories c 
                     LEFT JOIN modules m ON c.module_id = m.id 
                     WHERE FIND_IN_SET('menu_main', c.display_position) AND c.is_active = 1 
                     ORDER BY c.position ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build menu items
$menu_items = [];
foreach ($categories as $category) {
    $menu_items[$category['parent_id']][] = $category;
}

// Fetch logos
$stmt = $pdo->query("SELECT * FROM logos WHERE is_active = 1 ORDER BY position ASC LIMIT 1"); // Lấy logo đầu tiên
$logo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Website Chính' : 'Website Chính'; ?></title>
    <?php if ($favicon): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: <?php echo htmlspecialchars($colors['body']['bg'] ?? '#ffffff'); ?>;
            color: <?php echo htmlspecialchars($colors['body']['text'] ?? '#000000'); ?>;
            font-family: <?php echo htmlspecialchars($fonts['body']['family'] ?? 'Arial'); ?>, sans-serif;
            font-style: <?php echo htmlspecialchars($fonts['body']['style'] ?? 'normal'); ?>;
            font-size: <?php echo htmlspecialchars($fonts['body']['size'] ?? '16'); ?>px;
        }
        .navbar {
            background-color: <?php echo htmlspecialchars($colors['menu']['bg'] ?? '#343a40'); ?>;
        }
        .navbar-nav .nav-link {
            color: <?php echo htmlspecialchars($colors['menu']['text'] ?? '#ffffff'); ?> !important;
            font-family: <?php echo htmlspecialchars($fonts['menu']['family'] ?? 'Arial'); ?>, sans-serif;
            font-style: <?php echo htmlspecialchars($fonts['menu']['style'] ?? 'normal'); ?>;
            font-size: <?php echo htmlspecialchars($fonts['menu']['size'] ?? '16'); ?>px;
        }
        .navbar-nav .nav-link:hover {
            color: <?php echo htmlspecialchars($colors['menu']['text'] ?? '#ddd'); ?> !important;
        }
        .navbar-brand img {
            max-height: 40px;
        }
        .dropdown-menu {
            background-color: <?php echo htmlspecialchars($colors['menu']['bg'] ?? '#343a40'); ?>;
            font-family: <?php echo htmlspecialchars($fonts['menu']['family'] ?? 'Arial'); ?>, sans-serif;
            font-style: <?php echo htmlspecialchars($fonts['menu']['style'] ?? 'normal'); ?>;
            font-size: <?php echo htmlspecialchars($fonts['menu']['size'] ?? '16'); ?>px;
        }
        .breadcrumb {
            background-color: <?php echo htmlspecialchars($colors['body']['bg'] ?? '#f8f9fa'); ?>;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .breadcrumb-item a {
            color: <?php echo htmlspecialchars($colors['body']['text'] ?? '#000000'); ?>;
            font-family: <?php echo htmlspecialchars($fonts['body']['family'] ?? 'Arial'); ?>, sans-serif;
            font-size: <?php echo htmlspecialchars($fonts['body']['size'] ?? '16'); ?>px;
        }
        .breadcrumb-item a:hover {
            color: <?php echo htmlspecialchars($colors['title']['bg'] ?? '#007bff'); ?>;
        }
    </style>
</head>
<body>
    <!-- Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <?php if ($logo && $logo['desktop_image']): ?>
                <a class="navbar-brand" href="/">
                    <img src="<?php echo htmlspecialchars($logo['desktop_image']); ?>" alt="Logo">
                </a>
            <?php else: ?>
                <a class="navbar-brand" href="/">Website</a>
            <?php endif; ?>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <?php echo build_menu($menu_items); ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb (chỉ hiển thị nếu không phải trang chủ) -->
    <?php if (isset($page_title) && $page_title !== 'Trang Chủ'): ?>
        <nav aria-label="breadcrumb">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <?php if (isset($breadcrumb_items)): ?>
                        <?php foreach ($breadcrumb_items as $item): ?>
                            <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($item['url']); ?>"><?php echo htmlspecialchars($item['title']); ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (isset($page_title)): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
                    <?php endif; ?>
                </ol>
            </div>
        </nav>
    <?php endif; ?>