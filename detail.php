<?php
ob_start();
session_start(); // Giữ nguyên để khởi tạo session

// Đường dẫn tuyệt đối đến config.php
require $_SERVER['DOCUMENT_ROOT'] . '/kai/admin/database/config.php';

// Include header để lấy biến và hiển thị header
require 'header.php'; // Chỉ include một lần để lấy biến và hiển thị header

// Check database connection
if (!$pdo) {
    die('Không thể kết nối đến cơ sở dữ liệu!');
}

// Get slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Fetch data based on slug
$stmt = $pdo->prepare("SELECT i.*, c.title_vi AS parent_title FROM info i LEFT JOIN categories c ON i.parent_id = c.id WHERE i.slug_vi = ? AND i.is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    // If not found in info, check categories
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug_vi = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$item) {
    die('Không tìm thấy nội dung.');
}

// Set breadcrumb
$breadcrumb_items = [];
if (isset($item['parent_id']) && !empty($item['parent_id'])) {
    $stmt = $pdo->prepare("SELECT title_vi, slug_vi FROM categories WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$item['parent_id']]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($parent) {
        $breadcrumb_items[] = ['title' => $parent['title_vi'], 'url' => '/' . $parent['slug_vi']];
    }
}
$page_title = isset($item['title_vi']) ? $item['title_vi'] : 'Chi tiết';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title) . ' - Website Chính'; ?></title>
    <?php if (isset($favicon)): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: <?php echo isset($colors['body']['bg']) ? htmlspecialchars($colors['body']['bg']) : '#ffffff'; ?>;
            color: <?php echo isset($colors['body']['text']) ? htmlspecialchars($colors['body']['text']) : '#000000'; ?>;
            font-family: <?php echo isset($fonts['body']['family']) ? htmlspecialchars($fonts['body']['family']) : 'Arial'; ?>, sans-serif;
            font-style: <?php echo isset($fonts['body']['style']) ? htmlspecialchars($fonts['body']['style']) : 'normal'; ?>;
            font-size: <?php echo isset($fonts['body']['size']) ? htmlspecialchars($fonts['body']['size']) : '16'; ?>px;
        }
        .content-detail {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid red; /* Để kiểm tra phần tử hiển thị */
        }
        .content-detail h1 {
            color: <?php echo isset($colors['title']['text']) ? htmlspecialchars($colors['title']['text']) : '#000000'; ?>;
            font-family: <?php echo isset($fonts['title']['family']) ? htmlspecialchars($fonts['title']['family']) : 'Arial'; ?>, sans-serif;
            font-style: <?php echo isset($fonts['title']['style']) ? htmlspecialchars($fonts['title']['style']) : 'normal'; ?>;
            font-size: <?php echo isset($fonts['title']['size']) ? htmlspecialchars($fonts['title']['size']) : '24'; ?>px;
            border-bottom: 2px solid <?php echo isset($colors['title']['bg']) ? htmlspecialchars($colors['title']['bg']) : '#007bff'; ?>;
            padding-bottom: 10px;
        }
        .content-text {
            color: <?php echo isset($colors['body']['text']) ? htmlspecialchars($colors['body']['text']) : '#000000'; ?>;
            font-family: <?php echo isset($fonts['body']['family']) ? htmlspecialchars($fonts['body']['family']) : 'Arial'; ?>, sans-serif;
            font-style: <?php echo isset($fonts['body']['style']) ? htmlspecialchars($fonts['body']['style']) : 'normal'; ?>;
            font-size: <?php echo isset($fonts['body']['size']) ? htmlspecialchars($fonts['body']['size']) : '16'; ?>px;
            line-height: 1.6;
            margin-top: 20px;
            border: 1px solid green; /* Để kiểm tra phần tử hiển thị */
        }
    </style>
</head>
<body>
    <!-- Header đã được include ở trên, không cần include lại -->

    <div class="content-detail">
        <?php 
        $display_position = isset($item['display_position']) ? $item['display_position'] : '';
        $h1_content = isset($item['h1_content']) ? $item['h1_content'] : '';
        $title_vi = isset($item['title_vi']) ? $item['title_vi'] : '';
        if (!$display_position || strpos($display_position, 'an_tieu_de') === false): ?>
            <h1><?php echo htmlspecialchars($h1_content ?: $title_vi ?: ''); ?></h1>
        <?php endif; ?>

        <?php 
        if (isset($item['content_vi']) && !empty($item['content_vi'])): ?>
            <div class="content-text"><?php echo nl2br(trim($item['content_vi'])); ?></div>
            <?php echo '<pre>Debug: Content length = ' . strlen($item['content_vi']) . '</pre>'; ?>
        <?php else: ?>
            <p>Debug: content_vi is empty or not set.</p>
            <p>Không có nội dung để hiển thị.</p>
        <?php endif; ?>
    </div>

    <?php ob_end_flush(); ?>
</body>
</html>