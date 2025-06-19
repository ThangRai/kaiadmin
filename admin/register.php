<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = $_POST['username'] ?? '';
    $password   = $_POST['password'] ?? '';
    $fullname   = $_POST['fullname'] ?? '';
    $email      = $_POST['email'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $address    = $_POST['address'] ?? '';
    $role_id    = $_POST['role_id'] ?? 2; // 1: admin, 2: user, 3: staff

    // Xử lý upload ảnh
    $avatarPath = '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarPath = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath);
    }

    // Lưu thông tin (KHÔNG mã hóa mật khẩu vì bạn yêu cầu)
    $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, address, role_id, avatar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $fullname, $email, $phone, $address, $role_id, $avatarPath]);

    echo "Đăng ký thành công!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
</head>
<body>
<h2>Đăng ký tài khoản</h2>
<form method="post" enctype="multipart/form-data">
    <label>Họ và tên: <input type="text" name="fullname" required></label><br>
    <label>Số điện thoại: <input type="text" name="phone" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Địa chỉ: <input type="text" name="address" required></label><br>
    <label>Tên đăng nhập: <input type="text" name="username" required></label><br>
    <label>Mật khẩu: <input type="password" name="password" required></label><br>
    <label>Ảnh đại diện: <input type="file" name="avatar" accept="image/*"></label><br>
    <label>Vai trò:
        <select name="role_id">
            <option value="1">Admin</option>
            <option value="3">Nhân viên</option>
            <option value="2" selected>Người dùng</option>
        </select>
    </label><br>
    <button type="submit">Đăng ký</button>
</form>
</body>
</html>
