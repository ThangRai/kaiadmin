<?php
$is_detail = count($segments) > 1;
if ($is_detail) {
    // Chi tiết bài viết giới thiệu
    $slug = $segments[1];
    $stmt = $pdo->prepare("SELECT i.*, c.title_vi AS category_title FROM info i LEFT JOIN categories c ON i.parent_id = c.id WHERE i.slug_vi = ? AND i.is_active = 1");
    $stmt->execute([$slug]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($info) {
        $content = [
            'title_vi' => $info['title_vi'],
            'content_vi' => $info['content_vi'],
            'description_vi' => $info['description_vi'],
            'avatar' => $info['gallery_images'],
            'slug_vi' => $info['slug_vi'],
            'seo_title_vi' => $info['seo_title_vi'],
            'seo_description_vi' => $info['seo_description_vi']
        ];
    } else {
        header("HTTP/1.0 404 Not Found");
        include '404.php';
        exit;
    }
} else {
    // Danh sách bài viết giới thiệu
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
        $stmt = $pdo->prepare("SELECT * FROM info WHERE parent_id = ? AND is_active = 1 ORDER BY position ASC");
        $stmt->execute([$category['id']]);
        $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<?php if (!$is_detail && $infos): ?>
    <div class="info-list row">
        <h2>Bài viết giới thiệu</h2>
        <?php foreach ($infos as $info): ?>
            <div class="info-item">
                <?php if ($info['gallery_images']): ?>
                    <img src="<?php echo htmlspecialchars($info['gallery_images']); ?>" alt="<?php echo htmlspecialchars($info['title_vi']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($info['title_vi']); ?></h3>
                <p><?php echo htmlspecialchars($info['description_vi']); ?></p>
                <a href="/gioi-thieu/<?php echo htmlspecialchars($info['slug_vi']); ?>" class="btn btn-primary">Xem chi tiết</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>