<?php
$is_detail = count($segments) > 1;
if ($is_detail) {
    // Chi tiết nội dung
    $slug = $segments[1];
    $stmt = $pdo->prepare("SELECT c.*, cat.title_vi AS category_title FROM content c LEFT JOIN categories cat ON c.parent_id = cat.id WHERE c.slug_vi = ? AND c.is_active = 1");
    $stmt->execute([$slug]);
    $content_item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($content_item) {
        $content = [
            'title_vi' => $content_item['title_vi'],
            'content_vi' => $content_item['content_vi'],
            'description_vi' => $content_item['description_vi'],
            'avatar' => $content_item['gallery_images'],
            'slug_vi' => $content_item['slug_vi'],
            'seo_title_vi' => $content_item['seo_title_vi'],
            'seo_description_vi' => $content_item['seo_description_vi']
        ];
    } else {
        header("HTTP/1.0 404 Not Found");
        include '404.php';
        exit;
    }
} else {
    // Danh sách nội dung
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
        $stmt = $pdo->prepare("SELECT * FROM content WHERE parent_id = ? AND is_active = 1 ORDER BY position ASC");
        $stmt->execute([$category['id']]);
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<?php if (!$is_detail && $contents): ?>
    <div class="content-list row">
        <h2>Nội dung</h2>
        <?php foreach ($contents as $content_item): ?>
            <div class="content-item">
                <?php if ($content_item['gallery_images']): ?>
                    <img src="<?php echo htmlspecialchars($content_item['gallery_images']); ?>" alt="<?php echo htmlspecialchars($content_item['title_vi']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($content_item['title_vi']); ?></h3>
                <p><?php echo htmlspecialchars($content_item['description_vi']); ?></p>
                <a href="/noi-dung/<?php echo htmlspecialchars($content_item['slug_vi']); ?>" class="btn btn-primary">Xem chi tiết</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>