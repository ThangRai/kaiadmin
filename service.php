<?php
$is_detail = count($segments) > 1;
if ($is_detail) {
    // Chi tiết dịch vụ
    $slug = $segments[1];
    $stmt = $pdo->prepare("SELECT s.*, c.title_vi AS category_title FROM service s LEFT JOIN categories c ON s.parent_id = c.id WHERE s.slug_vi = ? AND s.is_active = 1");
    $stmt->execute([$slug]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($service) {
        $content = [
            'title_vi' => $service['title_vi'],
            'content_vi' => $service['content_vi'],
            'description_vi' => $service['description_vi'],
            'avatar' => $service['gallery_images'],
            'slug_vi' => $service['slug_vi'],
            'seo_title_vi' => $service['seo_title_vi'],
            'seo_description_vi' => $service['seo_description_vi']
        ];
    } else {
        header("HTTP/1.0 404 Not Found");
        include '404.php';
        exit;
    }
} else {
    // Danh sách dịch vụ
    $stmt = $pdo->prepare("SELECT c.* FROM categories c WHERE c.slug_vi = ? AND c.is_active = 1");
    $stmt->execute([$segments[0]]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($category) {
        $content = [
            'title_vi' => $category['title_vi'],
            'content_vi' => $category['content_vi'],
            'description_vi' => $category['description_vi'],
            'avatar' => $category['avatar'],
            'slug_vi' => $category['slug_vi'],
            'seo_title_vi' => $category['seo_title_vi'],
            'seo_description_vi' => $category['seo_description_vi']
        ];
        $stmt = $pdo->prepare("SELECT * FROM service WHERE parent_id = ? AND is_active = 1 ORDER BY position ASC");
        $stmt->execute([$category['id']]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        header("HTTP/1.0 404 Not Found");
        include '404.php';
        exit;
    }
}
?>

<h1><?php echo htmlspecialchars($content['title_vi']); ?></h1>
<?php if ($content['avatar']): ?>
    <img src="<?php echo htmlspecialchars($content['avatar']); ?>" alt="<?php echo htmlspecialchars($content['title_vi']); ?>">
<?php endif; ?>
<p><?php echo htmlspecialchars($content['description_vi']); ?></p>
<div><?php echo $content['content_vi']; ?></div>

<?php if (!$is_detail && $services): ?>
    <div class="services-list row">
        <h2>Dịch vụ</h2>
        <?php foreach ($services as $service): ?>
            <div class="services-item">
                <?php if ($service['gallery_images']): ?>
                    <img src="<?php echo htmlspecialchars($service['gallery_images']); ?>" alt="<?php echo htmlspecialchars($service['title_vi']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($service['title_vi']); ?></h3>
                <p><?php echo htmlspecialchars($service['description_vi']); ?></p>
                <a href="/dich-vu/<?php echo htmlspecialchars($service['slug_vi']); ?>" class="btn btn-primary">Xem chi tiết</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>