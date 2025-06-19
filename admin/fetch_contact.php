<?php
require 'database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("SELECT * FROM contact_info WHERE id = ?");
    $stmt->execute([$id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contact) {
        echo json_encode($contact);
    } else {
        echo json_encode(['error' => 'Không tìm thấy liên hệ']);
    }
}
?>