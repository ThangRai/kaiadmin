<?php
session_start();
require 'header.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .cart-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .cart-table th, .cart-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .cart-table th { background-color: #f8f9fa; }
        .cart-table img { width: 50px; height: 50px; object-fit: cover; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Giỏ hàng</h2>
        <?php if (!empty($_SESSION['cart'])): ?>
            <table class="cart-table">
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tổng</th>
                    <th>Hành động</th>
                </tr>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>"></td>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo number_format($item['price'], 0); ?> đ</td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 0); ?> đ</td>
                        <td><a href="remove_cart_item.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Xóa</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <a href="checkout.php" class="btn btn-primary">Thanh toán</a>
        <?php else: ?>
            <p>Giỏ hàng trống.</p>
        <?php endif; ?>
    </div>
</body>
</html>