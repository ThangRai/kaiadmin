<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($_SESSION['cart'][$input['id']])) {
    $_SESSION['cart'][$input['id']]['quantity'] = $input['quantity'];
}

echo json_encode(['success' => true]);
?>