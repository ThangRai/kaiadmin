<?php
session_start();
require 'database/config.php';


header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID đơn hàng không hợp lệ']);
    exit;
}

$order_id = intval($_GET['id']);

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['error' => 'Đơn hàng không tồn tại']);
    exit;
}

// Lấy chi tiết đơn hàng
$stmt = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
$stmt->execute([$order_id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'order' => $order,
    'details' => $details
]);
?>