<?php
ob_start();
session_start();
require 'header.php'; // Bao gồm tệp header.php chứa config.php

// Khởi tạo giỏ hàng nếu chưa tồn tại
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$pdo) {
    die('Không thể kết nối đến cơ sở dữ liệu!');
}

// Lấy cài đặt website
$website_status = getSetting($pdo, 'website_status', '1');
$scroll_top = getSetting($pdo, 'scroll_top', '1');
$lock_copy = getSetting($pdo, 'lock_copy', '0');
$colors = json_decode(getSetting($pdo, 'colors', '{}'), true);
$fonts = json_decode(getSetting($pdo, 'fonts', '{}'), true);

// Lấy cài đặt số cột responsive
$column_settings = function_exists('getColumnSettings') ? getColumnSettings('products', $pdo) : [
    'items_per_row_tiny' => 2,
    'items_per_row_sm' => 2,
    'items_per_row_md' => 2,
    'items_per_row_lg' => 3,
    'items_per_row_xl' => 6,
];

// Lấy danh sách module
$stmt = $pdo->query("SELECT id, title, option_name FROM modules ORDER BY position ASC");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục cho menu chính
$stmt = $pdo->query("SELECT c.id, c.title_vi, c.slug_vi, c.parent_id, m.option_name 
                     FROM categories c 
                     LEFT JOIN modules m ON c.module_id = m.id 
                     WHERE FIND_IN_SET('menu_main', c.display_position) AND c.is_active = 1 
                     ORDER BY c.position ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm lấy nội dung module
function fetch_module_content($module_id, $pdo) {
    $content = [];
    switch ($module_id) {
        case 4: // Sản phẩm
            $stmt = $pdo->query("SELECT id, title_vi, description_vi, gallery_images, original_price, current_price 
                                 FROM products 
                                 WHERE is_active = 1 AND FIND_IN_SET('trang_chu', display_position) 
                                 ORDER BY position ASC");
            $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 5: // Dịch vụ
            $stmt = $pdo->query("SELECT id, title_vi, description_vi 
                                 FROM products 
                                 WHERE is_active = 1 
                                 ORDER BY position ASC");
            $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 6: // Dự án
            $stmt = $pdo->query("SELECT id, title_vi, description_vi 
                                 FROM projects 
                                 WHERE is_active = 1 
                                 ORDER BY position ASC");
            $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 15: // Thư viện ảnh
            $stmt = $pdo->query("SELECT id, title_vi, image_path 
                                 FROM gallery_images 
                                 WHERE is_active = 1 
                                 ORDER BY position ASC");
            $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 3: // Giới thiệu
            $stmt = $pdo->query("SELECT id, title_vi, description_vi, content_vi 
                                 FROM info 
                                 WHERE is_active = 1 
                                 ORDER BY position ASC");
            $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        default:
            $content = [];
    }
    return $content;
}

// Lấy logo
$stmt = $pdo->query("SELECT * FROM logos WHERE is_active = 1 ORDER BY position ASC LIMIT 1");
$logo = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy slideshow
$stmt = $pdo->query("SELECT * FROM slideshow WHERE is_active = 1 ORDER BY position ASC");
$slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy nội dung info cho trang chủ
$stmt = $pdo->query("SELECT i.*, c.title_vi AS parent_title 
                     FROM info i 
                     LEFT JOIN categories c ON i.parent_id = c.id 
                     WHERE FIND_IN_SET('trang_chu', i.display_position) AND i.is_active = 1 
                     ORDER BY i.position ASC");
$info_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục cho trang chủ
$stmt = $pdo->query("SELECT c.*, m.title AS module_title, m.option_name 
                     FROM categories c 
                     LEFT JOIN modules m ON c.module_id = m.id 
                     WHERE FIND_IN_SET('trang_chu', c.display_position) AND c.is_active = 1 
                     ORDER BY c.position ASC");
$homepage_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Trang Chủ';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title) . ' - Website Chính'; ?></title>
    <?php if (isset($logo['image_path'])): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($logo['image_path']); ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .slideshow { position: relative; width: 100%; height: 400px; overflow: hidden; }
        .slideshow img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; display: none; }
        .slideshow img.active { display: block; }
        .slideshow-controls { position: absolute; top: 50%; width: 100%; display: flex; justify-content: space-between; transform: translateY(-50%); }
        .slideshow-controls button { background: rgba(0, 0, 0, 0.5); color: #fff; border: none; padding: 10px; cursor: pointer; font-size: 1.2rem; }
        .slideshow-controls button:hover { background: rgba(0, 0, 0, 0.8); }
        .content { padding: 20px; }
        .module-section { margin-bottom: 40px; }
        .module-section h2 {
            border-bottom: 2px solid <?php echo htmlspecialchars($colors['title']['bg'] ?? '#007bff'); ?>;
            padding-bottom: 10px;
            color: <?php echo htmlspecialchars($colors['title']['text'] ?? '#000000'); ?>;
            font-family: <?php echo htmlspecialchars($fonts['title']['family'] ?? 'Arial'); ?>, sans-serif;
            font-style: <?php echo htmlspecialchars($fonts['title']['style'] ?? 'normal'); ?>;
            font-size: <?php echo htmlspecialchars($fonts['title']['size'] ?? '24'); ?>px;
        }
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .product-image img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 5px;
        }
        .product-details {
            padding: 10px 0;
            flex-grow: 1;
        }
        .product-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
            height: 2.5em;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .prices {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .original-price {
            text-decoration: line-through;
            color: #888;
            font-size: 0.9rem;
        }
        .current-price {
            color: #dc3545;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .discount {
            color: #28a745;
            font-size: 0.9rem;
            font-weight: 500;
            background-color: #e8f5e9;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .add-to-cart-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: auto;
        }
        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }
        .add-to-cart-btn i {
            margin-right: 5px;
        }
        .hover-icons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
            gap: 5px;
        }
        .product-card:hover .hover-icons {
            display: flex;
        }
        .hover-icon {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .hover-icon:hover {
            background-color: #007bff;
            color: #fff;
        }
        .hover-icon i {
            font-size: 1rem;
        }
        /* Responsive columns từ cơ sở dữ liệu */
        @media (max-width: 576px) {
            .product-col { flex: 0 0 calc(100% / <?php echo $column_settings['items_per_row_tiny']; ?>); max-width: calc(100% / <?php echo $column_settings['items_per_row_tiny']; ?>); }
        }
        @media (min-width: 576px) and (max-width: 767px) {
            .product-col { flex: 0 0 calc(100% / <?php echo $column_settings['items_per_row_sm']; ?>); max-width: calc(100% / <?php echo $column_settings['items_per_row_sm']; ?>); }
        }
        @media (min-width: 768px) and (max-width: 991px) {
            .product-col { flex: 0 0 calc(100% / <?php echo $column_settings['items_per_row_md']; ?>); max-width: calc(100% / <?php echo $column_settings['items_per_row_md']; ?>); }
        }
        @media (min-width: 992px) and (max-width: 1199px) {
            .product-col { flex: 0 0 calc(100% / <?php echo $column_settings['items_per_row_lg']; ?>); max-width: calc(100% / <?php echo $column_settings['items_per_row_lg']; ?>); }
        }
        @media (min-width: 1200px) {
            .product-col { flex: 0 0 calc(100% / <?php echo $column_settings['items_per_row_xl']; ?>); max-width: calc(100% / <?php echo $column_settings['items_per_row_xl']; ?>); }
        }
        .gallery-preview { display: flex; flex-wrap: wrap; gap: 10px; }
        .gallery-preview img { width: 100px; height: 100px; object-fit: cover; border: 1px solid <?php echo htmlspecialchars($colors['body']['text'] ?? '#ddd'); ?>; border-radius: 4px; }
        .raw-content {
            white-space: pre-wrap;
            font-family: <?php echo htmlspecialchars($fonts['body']['family'] ?? 'Arial'); ?>, sans-serif;
            font-style: <?php echo htmlspecialchars($fonts['body']['style'] ?? 'normal'); ?>;
            font-size: <?php echo htmlspecialchars($fonts['body']['size'] ?? '16'); ?>px;
            color: <?php echo htmlspecialchars($colors['body']['text'] ?? '#333'); ?>;
            background: #f5f5f5;
            padding: 10px;
            border: 1px solid <?php echo htmlspecialchars($colors['body']['text'] ?? '#ddd'); ?>;
            border-radius: 4px;
        }
        .view-more-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: <?php echo htmlspecialchars($colors['title']['bg'] ?? '#007bff'); ?>;
            color: <?php echo htmlspecialchars($colors['title']['text'] ?? '#ffffff'); ?>;
            text-decoration: none;
            border-radius: 4px;
            font-family: <?php echo htmlspecialchars($fonts['title']['family'] ?? 'Arial'); ?>, sans-serif;
            font-size: <?php echo htmlspecialchars($fonts['title']['size'] ?? '16'); ?>px;
            transition: background-color 0.3s;
        }
        .view-more-btn:hover {
            background-color: <?php echo htmlspecialchars($colors['title']['bg'] ?? '#0056b3'); ?>;
        }
        /* CSS cho popup giỏ hàng */
        .cart-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }
        .cart-popup.active {
            display: block;
        }
        .cart-popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .cart-popup-overlay.active {
            display: block;
        }
        .cart-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .cart-popup-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        .cart-popup-close {
            cursor: pointer;
            font-size: 1.5rem;
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .cart-item-details {
            flex-grow: 1;
        }
        .cart-item-title {
            font-size: 1rem;
            margin-bottom: 5px;
        }
        .cart-item-price {
            color: #dc3545;
            font-weight: 600;
        }
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cart-item-quantity input {
            width: 50px;
            text-align: center;
        }
        .cart-item-remove {
            cursor: pointer;
            color: #dc3545;
        }
        .cart-popup-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .cart-popup-actions a, .cart-popup-actions button {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 1rem;
        }
        .cart-popup-actions .view-cart {
            background-color: #007bff;
            color: #fff;
        }
        .cart-popup-actions .continue-shopping {
            background-color: #6c757d;
            color: #fff;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Header đã được include ở trên -->

    <!-- Slideshow -->
    <div class="slideshow">
        <?php foreach ($slides as $index => $slide): ?>
            <img src="<?php echo htmlspecialchars($slide['desktop_image'] ?: $slide['mobile_image']); ?>" alt="<?php echo htmlspecialchars($slide['title_vi']); ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>">
        <?php endforeach; ?>
        <div class="slideshow-controls">
            <button id="prevSlide"><</button>
            <button id="nextSlide">></button>
        </div>
    </div>

    <!-- Popup giỏ hàng -->
    <div class="cart-popup-overlay"></div>
    <div class="cart-popup">
        <div class="cart-popup-header">
            <h3>Giỏ hàng</h3>
            <span class="cart-popup-close">×</span>
        </div>
        <div class="cart-content"></div>
        <div class="cart-popup-actions">
            <a href="cart.php" class="view-cart">Xem giỏ hàng</a>
            <button class="continue-shopping">Tiếp tục mua sắm</button>
        </div>
    </div>

    <!-- Nội dung trang chủ -->
    <div class="content">
        <?php foreach ($info_items as $item): ?>
            <div class="module-section">
                <?php
                $hide_title = isset($item['display_position']) && strpos($item['display_position'], 'an_tieu_de') !== false;
                if (!$hide_title) {
                    echo '<h2>' . htmlspecialchars($item['h1_content'] ?: $item['title_vi'] ?: '') . '</h2>';
                }
                ?>
                <?php
                $show_description = isset($item['display_position']) && strpos($item['display_position'], 'lay_mo_ta') !== false;
                $show_content = isset($item['display_position']) && strpos($item['display_position'], 'lay_noi_dung') !== false;

                if ($show_description && isset($item['description_vi']) && !empty($item['description_vi'])) {
                    echo '<div class="raw-content">' . $item['description_vi'] . '</div>';
                }
                if ($show_content && isset($item['content_vi']) && !empty($item['content_vi'])) {
                    echo '<div class="raw-content">' . $item['content_vi'] . '</div>';
                }
                ?>
                <?php if (isset($item['gallery_images']) && $item['gallery_images']): ?>
                    <div class="gallery-preview">
                        <?php foreach (explode(',', $item['gallery_images']) as $img): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="Gallery Image">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- Nút Xem thêm -->
                <?php if (isset($item['slug_vi']) && !empty($item['slug_vi'])): ?>
                    <a href="detail.php?slug=<?php echo htmlspecialchars(urlencode($item['slug_vi'])); ?>" class="view-more-btn">Xem thêm</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php foreach ($homepage_categories as $category): ?>
            <div class="module-section">
                <?php
                $hide_title = isset($category['display_position']) && strpos($category['display_position'], 'an_tieu_de') !== false;
                if (!$hide_title) {
                    echo '<h2>' . htmlspecialchars($category['h1_content'] ?: $category['title_vi'] ?: '') . '</h2>';
                }
                ?>
                <?php
                $show_description = isset($category['display_position']) && strpos($category['display_position'], 'lay_mo_ta') !== false;
                $show_content = isset($category['display_position']) && strpos($category['display_position'], 'lay_noi_dung') !== false;

                if ($show_description && isset($category['description_vi']) && !empty($category['description_vi'])) {
                    echo '<div class="raw-content">' . $category['description_vi'] . '</div>';
                }
                if ($show_content && isset($category['content_vi']) && !empty($category['content_vi'])) {
                    echo '<div class="raw-content">' . $category['content_vi'] . '</div>';
                }
                ?>
                <?php
                $module_content = fetch_module_content($category['module_id'], $pdo);
                if (!empty($module_content)) {
                    echo '<div class="row">';
                    foreach ($module_content as $content_item) {
                        $hide_title = isset($content_item['display_position']) && strpos($content_item['display_position'], 'an_tieu_de') !== false;
                        if ($category['module_id'] == 4) { // Sản phẩm
                            $gallery = explode(',', $content_item['gallery_images']);
                            $original_price = floatval($content_item['original_price']);
                            $current_price = floatval($content_item['current_price']);
                            $discount = $original_price > 0 ? round((($original_price - $current_price) / $original_price) * 100) : 0;
                            echo '<div class="col-12 product-col">';
                            echo '<div class="product-card">';
                            echo '<div class="product-image">';
                            echo '<img src="' . htmlspecialchars($gallery[0] ?: 'placeholder.jpg') . '" alt="' . htmlspecialchars($content_item['title_vi']) . '">';
                            echo '</div>';
                            echo '<div class="product-details">';
                            if (!$hide_title) {
                                echo '<h5 class="product-title">' . htmlspecialchars($content_item['title_vi']) . '</h5>';
                            }
                            echo '<div class="prices">';
                            if ($original_price > 0) {
                                echo '<span class="original-price">' . number_format($original_price, 0) . ' đ</span>';
                            }
                            echo '<span class="current-price">' . number_format($current_price, 0) . ' đ</span>';
                            if ($discount > 0) {
                                echo '<span class="discount">(Giảm ' . $discount . '%)</span>';
                            }
                            echo '</div>';
                            echo '<button class="add-to-cart-btn" data-item=\'' . json_encode(['id' => $content_item['id'], 'title' => $content_item['title_vi'], 'image' => $gallery[0], 'price' => $current_price]) . '\'><i class="fas fa-shopping-cart"></i> Mua</button>';
                            echo '</div>';
                            echo '<div class="hover-icons">';
                            echo '<div class="hover-icon" title="Xem chi tiết"><i class="fas fa-eye"></i></div>';
                            echo '<div class="hover-icon" title="Yêu thích"><i class="fas fa-heart"></i></div>';
                            echo '<div class="hover-icon" title="So sánh"><i class="fas fa-balance-scale"></i></div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            if (!$hide_title) {
                                echo '<h3>' . htmlspecialchars($content_item['title_vi'] ?: '') . '</h3>';
                            }
                            if (isset($content_item['description_vi']) && !empty($content_item['description_vi'])) {
                                echo '<div class="raw-content">' . $content_item['description_vi'] . '</div>';
                            }
                        }
                    }
                    echo '</div>';
                }
                ?>
                <!-- Nút Xem thêm -->
                <?php if (isset($category['slug_vi']) && !empty($category['slug_vi'])): ?>
                    <a href="detail.php?slug=<?php echo htmlspecialchars(urlencode($category['slug_vi'])); ?>" class="view-more-btn">Xem thêm</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chức năng slideshow
        let slideIndex = 0;
        const slides = document.querySelectorAll('.slideshow img');
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) slide.classList.add('active');
            });
        }

        document.getElementById('prevSlide').addEventListener('click', () => {
            slideIndex = (slideIndex - 1 + totalSlides) % totalSlides;
            showSlide(slideIndex);
        });

        document.getElementById('nextSlide').addEventListener('click', () => {
            slideIndex = (slideIndex + 1) % totalSlides;
            showSlide(slideIndex);
        });

        setInterval(() => {
            slideIndex = (slideIndex + 1) % totalSlides;
            showSlide(slideIndex);
        }, 5000);

        // Chức năng popup giỏ hàng
        const cartPopup = document.querySelector('.cart-popup');
        const cartPopupOverlay = document.querySelector('.cart-popup-overlay');
        const cartContent = document.querySelector('.cart-content');
        const closePopup = document.querySelector('.cart-popup-close');
        const continueShopping = document.querySelector('.continue-shopping');
        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

        function updateCartPopup() {
            cartContent.innerHTML = '';
            fetch('fetch_cart.php')
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        cartContent.innerHTML = '<p>Giỏ hàng trống</p>';
                    } else {
                        data.forEach(item => {
                            const cartItem = document.createElement('div');
                            cartItem.className = 'cart-item';
                            cartItem.innerHTML = `
                                <img src="${item.image}" alt="${item.title}">
                                <div class="cart-item-details">
                                    <div class="cart-item-title">${item.title}</div>
                                    <div class="cart-item-price">${item.price.toLocaleString()} đ</div>
                                    <div class="cart-item-quantity">
                                        <span class="decrease-qty" data-id="${item.id}">-</span>
                                        <input type="number" value="${item.quantity}" min="1" data-id="${item.id}">
                                        <span class="increase-qty" data-id="${item.id}">+</span>
                                    </div>
                                </div>
                                <div class="cart-item-remove" data-id="${item.id}"><i class="fas fa-trash"></i></div>
                            `;
                            cartContent.appendChild(cartItem);
                        });

                        // Xử lý tăng/giảm số lượng
                        document.querySelectorAll('.decrease-qty').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                const input = this.nextElementSibling;
                                if (parseInt(input.value) > 1) {
                                    input.value = parseInt(input.value) - 1;
                                    updateCartItem(id, input.value);
                                }
                            });
                        });

                        document.querySelectorAll('.increase-qty').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                const input = this.previousElementSibling;
                                input.value = parseInt(input.value) + 1;
                                updateCartItem(id, input.value);
                            });
                        });

                        // Xử lý xóa sản phẩm
                        document.querySelectorAll('.cart-item-remove').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                removeCartItem(id);
                            });
                        });
                    }
                });
        }

        function addToCart(item) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(item)
            }).then(() => {
                cartPopup.classList.add('active');
                cartPopupOverlay.classList.add('active');
                updateCartPopup();
            });
        }

        function updateCartItem(id, quantity) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id, quantity })
            }).then(() => {
                updateCartPopup();
            });
        }

        function removeCartItem(id) {
            fetch('remove_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            }).then(() => {
                updateCartPopup();
            });
        }

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const item = JSON.parse(this.dataset.item);
                addToCart(item);
            });
        });

        closePopup.addEventListener('click', () => {
            cartPopup.classList.remove('active');
            cartPopupOverlay.classList.remove('active');
        });

        continueShopping.addEventListener('click', () => {
            cartPopup.classList.remove('active');
            cartPopupOverlay.classList.remove('active');
        });

        cartPopupOverlay.addEventListener('click', () => {
            cartPopup.classList.remove('active');
            cartPopupOverlay.classList.remove('active');
        });
    </script>

    <?php ob_end_flush(); ?>
</body>
</html>