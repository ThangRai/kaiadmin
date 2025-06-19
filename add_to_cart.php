<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$item_id = $input['id'];
$item = [
    'id' => $input['id'],
    'title' => $input['title'],
    'image' => $input['image'],
    'price' => $input['price'],
    'quantity' => 1
];

if (isset($_SESSION['cart'][$item_id])) {
    $_SESSION['cart'][$item_id]['quantity']++;
} else {
    $_SESSION['cart'][$item_id] = $item;
}

echo json_encode(['success' => true]);
?>