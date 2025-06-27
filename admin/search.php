<?php
session_start();
require 'database/config.php';

if (!isset($_SESSION['user_id'])) {
    exit('<div class="no-results">Vui lòng đăng nhập!</div>');
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
if (empty($query)) {
    exit('<div class="no-results">Vui lòng nhập từ khóa!</div>');
}

try {
    $results = [];
    $query = '%' . $query . '%';

    // Tìm kiếm trong bảng categories
    $stmt = $pdo->prepare("SELECT id, title_vi FROM categories WHERE title_vi LIKE ? OR description_vi LIKE ? OR seo_title_vi LIKE ?");
    $stmt->execute([$query, $query, $query]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'table' => 'categories',
            'id' => $row['id'],
            'name' => $row['title_vi'],
            'edit_url' => 'categories.php?method=frm&edit_id=' . $row['id']
        ];
    }

    // Tìm kiếm trong bảng products
    $stmt = $pdo->prepare("SELECT id, title_vi, product_code FROM products WHERE title_vi LIKE ? OR product_code LIKE ? OR description_vi LIKE ?");
    $stmt->execute([$query, $query, $query]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'table' => 'products',
            'id' => $row['id'],
            'name' => $row['title_vi'] . ($row['product_code'] ? ' (' . $row['product_code'] . ')' : ''),
            'edit_url' => 'product.php?method=frm&edit_id=' . $row['id']
        ];
    }

    // Tìm kiếm trong bảng info
    $stmt = $pdo->prepare("SELECT id, title_vi FROM info WHERE title_vi LIKE ? OR description_vi LIKE ? OR seo_title_vi LIKE ?");
    $stmt->execute([$query, $query, $query]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'table' => 'info',
            'id' => $row['id'],
            'name' => $row['title_vi'],
            'edit_url' => 'info.php?method=frm&edit_id=' . $row['id']
        ];
    }


        // Tìm kiếm trong bảng service
    $stmt = $pdo->prepare("SELECT id, title_vi FROM service WHERE title_vi LIKE ? OR description_vi LIKE ? OR seo_title_vi LIKE ?");
    $stmt->execute([$query, $query, $query]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'table' => 'service',
            'id' => $row['id'],
            'name' => $row['title_vi'],
            'edit_url' => 'service.php?method=frm&edit_id=' . $row['id']
        ];
    }

            // Tìm kiếm trong bảng project
    $stmt = $pdo->prepare("SELECT id, title_vi FROM project WHERE title_vi LIKE ? OR description_vi LIKE ? OR seo_title_vi LIKE ?");
    $stmt->execute([$query, $query, $query]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'table' => 'project',
            'id' => $row['id'],
            'name' => $row['title_vi'],
            'edit_url' => 'project.php?method=frm&edit_id=' . $row['id']
        ];
    }


    // Tìm kiếm trong bảng gallery
    $stmt = $pdo->prepare("SELECT id, title_vi FROM gallery WHERE title_vi LIKE ?");
    $stmt->execute([$query]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'table' => 'gallery',
            'id' => $row['id'],
            'name' => $row['title_vi'],
            'edit_url' => 'gallery.php?method=frm&edit_id=' . $row['id']
        ];
    }


        // Tìm kiếm trong bảng hoidap
    $stmt = $pdo->prepare("SELECT id, title_vi FROM hoidap WHERE title_vi LIKE ?");
    $stmt->execute([$query]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'table' => 'hoidap',
            'id' => $row['id'],
            'name' => $row['title_vi'],
            'edit_url' => 'hoidap.php?method=frm&edit_id=' . $row['id']
        ];
    }

    
    if (empty($results)) {
        echo '<div class="no-results">Không tìm thấy kết quả!</div>';
    } else {
        foreach ($results as $result) {
            echo '<div class="result-item"><a href="' . htmlspecialchars($result['edit_url']) . '">' . htmlspecialchars($result['name']) . '</a></div>';
        }
    }
} catch (Exception $e) {
    echo '<div class="no-results">Lỗi khi tìm kiếm: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>