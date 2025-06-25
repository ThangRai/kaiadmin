<?php
$is_detail = count($segments) > 1;
if ($is_detail) {
    // Chi tiết dự án
    $slug = $segments[1];
    $stmt = $pdo->prepare("SELECT p.*, c.title_vi AS category_title FROM project p LEFT JOIN categories c ON p.parent_id = c.id WHERE p.slug_vi = ? AND p.is_active = 1");
    $stmt->execute([$slug]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($project) {
        $content = [
            'title_vi' => $project['title_vi'],
            'content_vi' => $project['content_vi'],
            'description_vi' => $project['description_vi'],
            'avatar' => $project['gallery_images'],
            'slug_vi' => $project['slug_vi'],
            'seo_title_vi' => $project['seo_title_vi'],
            'seo_description_vi' => $project['seo_description_vi']
        ];
    } else {
        header("HTTP/1.0 404 Not Found");
        include '404.php';
        exit;
    }
} else {
    // Danh sách dự án
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
        $stmt = $pdo->prepare("SELECT * FROM project WHERE parent_id = ? AND is_active = 1 ORDER BY position ASC");
        $stmt->execute([$category['id']]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<?php if (!$is_detail && $projects): ?>
    <div class="project-list row">
        <h2>Dự án</h2>
        <?php foreach ($projects as $project): ?>
            <div class="project-item">
                <?php if ($project['gallery_images']): ?>
                    <img src="<?php echo htmlspecialchars($project['gallery_images']); ?>" alt="<?php echo htmlspecialchars($project['title_vi']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($project['title_vi']); ?></h3>
                <p><?php echo htmlspecialchars($project['description_vi']); ?></p>
                <a href="/du-an/<?php echo htmlspecialchars($project['slug_vi']); ?>" class="btn btn-primary">Xem chi tiết</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>