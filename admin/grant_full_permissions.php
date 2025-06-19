<?php
session_start();
require 'database/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// File log để debug
$log_file = 'debug_permissions.log';
function log_debug($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

// Kiểm tra kết nối CSDL
if (!$pdo) {
    log_debug("Database connection failed");
    $_SESSION['toast_message'] = 'Không thể kết nối đến cơ sở dữ liệu!';
    $_SESSION['toast_type'] = 'error';
    header("Location: manage_menu.php");
    exit;
}

// ID của tài khoản admin
$admin_id = 1;

$pdo->beginTransaction();
try {
    // Lấy tất cả menu items
    $stmt = $pdo->query("SELECT id FROM menu_items WHERE is_visible = 1");
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    log_debug("Menu items fetched: " . print_r($menu_items, true));

    if (empty($menu_items)) {
        throw new Exception("No menu items found in menu_items table");
    }

    // Xóa quyền cũ của admin
    $stmt = $pdo->prepare("DELETE FROM user_menu_permissions WHERE user_id = ?");
    $stmt->execute([$admin_id]);
    log_debug("Deleted old permissions for user ID: $admin_id");

    // Gán full quyền (can_view, can_add, can_edit, can_delete = 1) cho tất cả menu items
    $stmt = $pdo->prepare("
        INSERT INTO user_menu_permissions (user_id, menu_item_id, can_view, can_add, can_edit, can_delete)
        VALUES (?, ?, 1, 1, 1, 1)
    ");
    foreach ($menu_items as $menu) {
        $stmt->execute([$admin_id, $menu['id']]);
        log_debug("Granted full permissions for user ID: $admin_id, menu ID: {$menu['id']}");
    }

    $pdo->commit();
    $_SESSION['toast_message'] = 'Gán full quyền cho tài khoản admin thành công!';
    $_SESSION['toast_type'] = 'success';
} catch (Exception $e) {
    $pdo->rollBack();
    log_debug("Error granting permissions: " . $e->getMessage() . ' at line ' . $e->getLine());
    $_SESSION['toast_message'] = 'Lỗi khi gán quyền: ' . $e->getMessage();
    $_SESSION['toast_type'] = 'error';
}

header("Location: manage_menu.php");
exit;
?>