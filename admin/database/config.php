<?php
// admin/database/config.php

$host = 'localhost';       // hoặc 127.0.0.1
$dbname = 'kaiadmin_db';   // tên CSDL bạn tạo
$username = 'root';        // tài khoản MySQL (XAMPP thường là root)
$password = '';            // mật khẩu (XAMPP mặc định rỗng)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Kiểm tra và định nghĩa hàm chỉ khi chưa tồn tại
if (!function_exists('getSetting')) {
    function getSetting($pdo, $key, $default = '') {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['value'] : $default;
    }
}