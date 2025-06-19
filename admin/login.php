<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'database/config.php';
require_once 'include/functions.php';

$error = '';
$success = '';

if (isset($_GET['action']) && $_GET['action'] === 'forgot' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $newPass = substr(str_shuffle('123456789abcdefghijklmnpqrstuvwxyz'), 0, 8);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$newPass, $email]);

            // Gửi email đơn giản
            $headers = "From: no-reply@kaiadmin.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            if (mail($email, "Mật khẩu mới", "Mật khẩu mới của bạn: $newPass", $headers)) {
                $success = "Mật khẩu mới đã được gửi về email!";
            } else {
                $error = "Không thể gửi email, vui lòng thử lại!";
                log_debug("Failed to send email to $email");
            }
        } else {
            $error = "Không tìm thấy email!";
        }
    } catch (Exception $e) {
        log_debug('Forgot password error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $error = "Lỗi hệ thống, vui lòng thử lại sau!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $userIp = $_SERVER['REMOTE_ADDR']; // Lấy địa chỉ IP của người dùng
    $status = 'failed'; // Mặc định trạng thái là thất bại

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['password'] === $password) {
                if ($user['is_active'] == 1) {
                    $status = 'success'; // Đăng nhập thành công
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['avatar'] = $user['avatar'] ?? 'default.png';
                    $_SESSION['email'] = $user['email'];
                } else {
                    $error = "Tài khoản của bạn đã bị khóa!";
                }
            } else {
                $error = "Sai mật khẩu!";
            }
        } else {
            $error = "Không tìm thấy tài khoản!";
        }

        // Ghi lịch sử đăng nhập
        try {
            $logStmt = $pdo->prepare("INSERT INTO userlog (userId, username, userIp, login_time, status) VALUES (:userId, :username, :userIp, NOW(), :status)");
            $logStmt->execute([
                ':userId' => $user['id'] ?? 0, // Nếu không tìm thấy user, ghi userId là 0
                ':username' => $username,
                ':userIp' => $userIp,
                ':status' => $status
            ]);
        } catch (Exception $e) {
            log_debug('Error logging login attempt: ' . $e->getMessage() . ' at line ' . $e->getLine());
        }

        if ($status === 'success') {
            header("Location: index.php");
            exit;
        }
    } catch (Exception $e) {
        log_debug('Login error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $error = "Lỗi hệ thống, vui lòng thử lại sau!";

        // Ghi lỗi hệ thống vào userlog
        try {
            $logStmt = $pdo->prepare("INSERT INTO userlog (userId, username, userIp, login_time, status) VALUES (:userId, :username, :userIp, NOW(), :status)");
            $logStmt->execute([
                ':userId' => 0,
                ':username' => $username,
                ':userIp' => $userIp,
                ':status' => 'failed'
            ]);
        } catch (Exception $e) {
            log_debug('Error logging login attempt: ' . $e->getMessage() . ' at line ' . $e->getLine());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Đăng nhập - Kaiadmin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #2980b9, #8e44ad);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            width: 350px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .form-group {
            position: relative;
            margin-bottom: 20px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 40px;
            border: 1px solid #ccc;
            border-radius: 30px;
            box-sizing: border-box;
        }
        .form-group i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #aaa;
        }
        .form-group .toggle-pass {
          position: absolute;
          left: 90%;
          top: 50%;
          transform: translateY(-50%);
          cursor: pointer;
          color: #666;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 30px;
            background: #8e44ad;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #732d91;
        }
        .forgot {
            text-align: center;
            margin-top: 15px;
        }
        .forgot a {
            text-decoration: none;
            color: #2980b9;
        }
        .forgot a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2><?= isset($_GET['action']) && $_GET['action'] === 'forgot' ? 'Quên mật khẩu' : 'Đăng nhập' ?></h2>

    <form method="post">
        <?php if (isset($_GET['action']) && $_GET['action'] === 'forgot'): ?>
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Nhập email" required>
            </div>
        <?php else: ?>
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Mật khẩu" required>
                <i class="fas fa-eye toggle-pass" onclick="togglePassword()"></i>
            </div>
        <?php endif; ?>

        <button type="submit">
            <?= isset($_GET['action']) && $_GET['action'] === 'forgot' ? 'Gửi mật khẩu mới' : 'Đăng nhập' ?>
        </button>
    </form>

    <div class="forgot">
        <?php if (isset($_GET['action']) && $_GET['action'] === 'forgot'): ?>
            <a href="login.php">← Quay lại đăng nhập</a>
        <?php else: ?>
            <a href="?action=forgot">Quên mật khẩu?</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($error): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Lỗi',
    text: '<?= addslashes($error) ?>'
});
</script>
<?php endif; ?>

<?php if ($success): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Thành công',
    text: '<?= addslashes($success) ?>',
    timer: 2000
});
</script>
<?php endif; ?>

<script>
function togglePassword() {
    const input = document.getElementById("password");
    const icon = document.querySelector(".toggle-pass");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.add("fa-eye");
        icon.classList.remove("fa-eye-slash");
    }
}
</script>

</body>
</html>