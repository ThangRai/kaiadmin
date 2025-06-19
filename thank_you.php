<?php
session_start();
require 'header.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảm Ơn - Website Chính</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .thank-you-container { text-align: center; padding: 50px; }
        .thank-you-container h2 { color: #28a745; }
    </style>
</head>
<body>
    <div class="container thank-you-container">
        <h2>Cảm ơn bạn đã đặt hàng!</h2>
        <p>Đơn hàng của bạn đã được ghi nhận. Chúng tôi sẽ liên hệ với bạn sớm.</p>
        <a href="index.php" class="btn btn-primary">Quay về trang chủ</a>
    </div>
</body>
</html>