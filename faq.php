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
    $stmt = $pdo->prepare("SELECT * FROM faq WHERE parent_id = ? AND is_active = 1 ORDER BY position ASC");
    $stmt->execute([$category['id']]);
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<?php if ($faqs): ?>
    <div class="faq-list row">
        <h2>Câu hỏi thường gặp</h2>
        <div class="accordion" id="faqAccordion">
            <?php foreach ($faqs as $index => $faq): ?>
                <div class="faq-item card">
                    <div class="card-header" id="heading<?php echo $index; ?>">
                        <h3 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                                <?php echo htmlspecialchars($faq['title_vi']); ?>
                            </button>
                        </h3>
                    </div>
                    <div id="collapse<?php echo $index; ?>" class="collapse" aria-labelledby="heading<?php echo $index; ?>" data-parent="#faqAccordion">
                        <div class="card-body">
                            <?php echo htmlspecialchars($faq['content_vi']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>