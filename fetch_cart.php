<?php
session_start();
header('Content-Type: application/json');

$cart = isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : [];

echo json_encode($cart);
?>