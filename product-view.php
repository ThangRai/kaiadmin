<?php
ob_start();
session_start(); // Giữ nguyên để khởi tạo session

require __DIR__ . '/admin/database/config.php';
require_once __DIR__ . '/admin/lib/column-helper.php';

// Check database connection
if (!$pdo) {
    die('Không thể kết nối đến cơ sở dữ liệu!');
}

// Fetch column settings
$column_settings = function_exists('getColumnSettings') ? getColumnSettings('products', $pdo) : [
    'items_per_row_tiny' => 2,
    'items_per_row_sm' => 2,
    'items_per_row_md' => 2,
    'items_per_row_lg' => 3,
    'items_per_row_xl' => 6,
];

// Fetch products from database
$stmt = $pdo->prepare("SELECT p.*, GROUP_CONCAT(c.title_vi SEPARATOR ', ') AS category_titles 
                       FROM products p 
                       LEFT JOIN product_categories pc ON p.id = pc.product_id 
                       LEFT JOIN categories c ON pc.category_id = c.id 
                       WHERE p.is_active = 1 
                       GROUP BY p.id 
                       ORDER BY p.position ASC, p.id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Trang chủ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
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
        /* Responsive columns from database or default */
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
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Danh sách sản phẩm</h2>
        <div class="row">
            <?php if (empty($products)): ?>
                <p>Không có sản phẩm nào để hiển thị.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <?php
                    $gallery = $product['gallery_images'] ? explode(',', $product['gallery_images']) : [];
                    $original_price = floatval($product['original_price']);
                    $current_price = floatval($product['current_price']);
                    $discount = $original_price > 0 ? round((($original_price - $current_price) / $original_price) * 100) : 0;
                    ?>
                    <div class="col-12 product-col">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars(!empty($gallery) ? $gallery[0] : 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['title_vi']); ?>">
                            </div>
                            <div class="product-details">
                                <h5 class="product-title"><?php echo htmlspecialchars($product['title_vi']); ?></h5>
                                <div class="prices">
                                    <?php if ($original_price > 0): ?>
                                        <span class="original-price"><?php echo number_format($original_price, 0); ?> đ</span>
                                    <?php endif; ?>
                                    <span class="current-price"><?php echo number_format($current_price, 0); ?> đ</span>
                                    <?php if ($discount > 0): ?>
                                        <span class="discount">(Giảm <?php echo $discount; ?>%)</span>
                                    <?php endif; ?>
                                    <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i></button>
                                </div>
                            </div>
                            <div class="hover-icons">
                                <div class="hover-icon" title="Xem chi tiết"><i class="fas fa-eye"></i></div>
                                <div class="hover-icon" title="Yêu thích"><i class="fas fa-heart"></i></div>
                                <div class="hover-icon" title="So sánh"><i class="fas fa-balance-scale"></i></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php ob_end_flush(); ?>
</body>
</html>