<?php
$is_detail = count($segments) > 1;
if ($is_detail) {
    // Chi tiết sản phẩm
    $slug = $segments[1];
    $stmt = $pdo->prepare("SELECT p.*, c.title_vi AS category_title FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug_vi = ? AND p.is_active = 1");
    $stmt->execute([$slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $content = [
            'title_vi' => $product['title_vi'],
            'content_vi' => $product['content_vi'],
            'description_vi' => $product['description_vi'],
            'avatar' => $product['gallery_images'],
            'slug_vi' => $product['slug_vi'],
            'seo_title_vi' => $product['seo_title_vi'],
            'seo_description_vi' => $product['seo_description_vi'],
            'current_price' => $product['current_price']
        ];
    } else {
        header("HTTP/1.0 404 Not Found");
        include '404.php';
        exit;
    }
} else {
    // Danh sách sản phẩm
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
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND is_active = 1 ORDER BY position ASC");
        $stmt->execute([$category['id']]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<?php if ($is_detail): ?>
    <p>Giá: <?php echo number_format($content['current_price'], 0, ',', '.'); ?> VNĐ</p>
    <a href="/cart/add/<?php echo htmlspecialchars($content['slug_vi']); ?>" class="btn btn-success">Thêm vào giỏ hàng</a>
<?php endif; ?>

<?php if (!$is_detail && $products): ?>
    <div class="products-list row">
        <h2>Sản phẩm</h2>
        <?php foreach ($products as $product): ?>
            <div class="products-item">
                <?php if ($product['gallery_images']): ?>
                    <img src="<?php echo htmlspecialchars($product['gallery_images']); ?>" alt="<?php echo htmlspecialchars($product['title_vi']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($product['title_vi']); ?></h3>
                <p><?php echo htmlspecialchars($product['description_vi']); ?></p>
                <p>Giá: <?php echo number_format($product['current_price'], 0, ',', '.'); ?> VNĐ</p>
                <a href="/san-pham/<?php echo htmlspecialchars($product['slug_vi']); ?>" class="btn btn-primary">Xem chi tiết</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>