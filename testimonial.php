<?php
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
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE parent_id = ? AND is_active = 1 ORDER BY position ASC");
    $stmt->execute([$category['id']]);
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}
?>

<h1><?php echo htmlspecialchars($content['title_vi']); ?></h1>
<?php if ($content['avatar']): ?>
    <img src="<?php echo htmlspecialchars($content['avatar']); ?>" alt="<?php echo htmlspecialchars($content['title_vi']); ?>">
<?php endif; ?>
<p><?php echo htmlspecialchars($content['description_vi']); ?></p>
<div><?php echo $content['content_vi']; ?></div>

<?php if ($testimonials): ?>
    <div class="testimonials-list row">
        <h2>Ý kiến khách hàng</h2>
        <?php foreach ($testimonials as $testimonial): ?>
            <div class="testimonials-item">
                <?php if ($testimonial['gallery_images']): ?>
                    <img src="<?php echo htmlspecialchars($testimonial['gallery_images']); ?>" alt="<?php echo htmlspecialchars($testimonial['title_vi']); ?>" class="rounded-circle" style="width: 80px; height: 80px;">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($testimonial['title_vi']); ?></h3>
                <p><?php echo htmlspecialchars($testimonial['content_vi']); ?></p>
                <p><strong><?php echo htmlspecialchars($testimonial['customer_name']); ?></strong></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>