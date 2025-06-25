<?php
$content = [
    'title_vi' => 'Kết quả tìm kiếm: ' . htmlspecialchars($search_query),
    'content_vi' => '',
    'slug_vi' => 'search',
    'avatar' => '',
    'description_vi' => ''
];

$search_query_sql = '%' . $search_query . '%';
$stmt = $pdo->prepare("
    SELECT 'product' AS type, id, title_vi, slug_vi, gallery_images, description_vi FROM products 
    WHERE (title_vi LIKE ? OR description_vi LIKE ?) AND is_active = 1
    UNION
    SELECT 'info' AS type, id, title_vi, slug_vi, gallery_images, description_vi FROM info 
    WHERE (title_vi LIKE ? OR description_vi LIKE ?) AND is_active = 1
    UNION
    SELECT 'service' AS type, id, title_vi, slug_vi, gallery_images, description_vi FROM service 
    WHERE (title_vi LIKE ? OR description_vi LIKE ?) AND is_active = 1
    UNION
    SELECT 'content' AS type, id, title_vi, slug_vi, gallery_images, description_vi FROM content 
    WHERE (title_vi LIKE ? OR description_vi LIKE ?) AND is_active = 1
    UNION
    SELECT 'project' AS type, id, title_vi, slug_vi, gallery_images, description_vi FROM project 
    WHERE (title_vi LIKE ? OR description_vi LIKE ?) AND is_active = 1
    UNION
    SELECT 'testimonial' AS type, id, title_vi, slug_vi, gallery_images, content_vi AS description_vi FROM testimonials 
    WHERE (title_vi LIKE ? OR content_vi LIKE ?) AND is_active = 1
");
$stmt->execute(array_fill(0, 12, $search_query_sql));
$search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1><?php echo htmlspecialchars($content['title_vi']); ?></h1>
<div class="search-results">
    <?php if ($search_results): ?>
        <div class="row">
            <?php foreach ($search_results as $result): ?>
                <div class="col search-result-item">
                    <?php if ($result['gallery_images']): ?>
                        <img src="<?php echo htmlspecialchars($result['gallery_images']); ?>" alt="<?php echo htmlspecialchars($result['title_vi']); ?>">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($result['title_vi']); ?></h3>
                    <p><?php echo htmlspecialchars($result['description_vi']); ?></p>
                    <a href="/<?php echo $result['type'] === 'product' ? 'san-pham' : ($result['type'] === 'info' ? 'gioi-thieu' : ($result['type'] === 'service' ? 'dich-vu' : ($result['type'] === 'content' ? 'noi-dung' : ($result['type'] === 'project' ? 'du-an' : 'y-kien-khach-hang')))); ?>/<?php echo htmlspecialchars($result['slug_vi']); ?>" class="btn btn-primary">Xem chi tiết</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Không tìm thấy kết quả nào.</p>
    <?php endif; ?>
</div>