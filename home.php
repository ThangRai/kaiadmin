<?php
$content = [
    'title_vi' => 'Trang chủ',
    'content_vi' => 'Chào mừng đến với Kaiadmin!',
    'slug_vi' => 'trang-chu',
    'avatar' => '',
    'description_vi' => 'Website cung cấp sản phẩm và dịch vụ chất lượng cao.'
];

// Lấy slideshow
$stmt = $pdo->prepare("SELECT * FROM slideshow WHERE is_active = 1 ORDER BY position ASC");
$stmt->execute();
$slideshows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục nổi bật
$stmt = $pdo->prepare("SELECT * FROM categories WHERE display_position LIKE '%trang_chu%' AND is_active = 1 ORDER BY position ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy sản phẩm nổi bật
$stmt = $pdo->prepare("SELECT * FROM products WHERE display_position LIKE '%trang_chu%' AND is_active = 1 ORDER BY position ASC LIMIT 6");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($slideshows): ?>
    <div id="slideshow" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($slideshows as $index => $slide): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($slide['desktop_image']); ?>" class="d-none d-md-block" alt="<?php echo htmlspecialchars($slide['title_vi']); ?>">
                    <img src="<?php echo htmlspecialchars($slide['mobile_image']); ?>" class="d-block d-md-none" alt="<?php echo htmlspecialchars($slide['title_vi']); ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#slideshow" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#slideshow" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
<?php endif; ?>

<h1><?php echo htmlspecialchars($content['title_vi']); ?></h1>
<p><?php echo htmlspecialchars($content['description_vi']); ?></p>
<div><?php echo $content['content_vi']; ?></div>

<?php if ($categories): ?>
    <div class="category-list">
        <h2>Danh mục nổi bật</h2>
        <div class="row">
            <?php foreach ($categories as $cat): ?>
                <div class="col category-item">
                    <?php if ($cat['avatar']): ?>
                        <img src="<?php echo htmlspecialchars($cat['avatar']); ?>" alt="<?php echo htmlspecialchars($cat['title_vi']); ?>">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($cat['title_vi']); ?></h3>
                    <p><?php echo htmlspecialchars($cat['description_vi']); ?></p>
                    <a href="/<?php echo htmlspecialchars($cat['slug_vi']); ?>" class="btn btn-primary">Xem chi tiết</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($products): ?>
    <div class="products-list row">
        <h2>Sản phẩm nổi bật</h2>
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