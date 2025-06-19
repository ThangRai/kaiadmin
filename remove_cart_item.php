<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($_SESSION['cart'][$input['id']])) {
    unset($_SESSION['cart'][$input['id']]);
}

echo json_encode(['success' => true]);
?>