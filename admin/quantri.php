<?php
ob_start();
session_start();
require 'database/config.php';
require_once 'include/functions.php';
require '../vendor/autoload.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check database connection
if (!$pdo) {
    log_debug("Database connection failed");
    $_SESSION['toast_message'] = 'Không thể kết nối đến cơ sở dữ liệu!';
    $_SESSION['toast_type'] = 'error';
    header("Location: quantri.php");
    exit;
}

// Handle toggle status
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0; // Nếu checkbox được check thì is_active = 1, ngược lại = 0
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $user_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Toggle status error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: quantri.php");
    exit;
}

// Handle account deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("DELETE FROM user_menu_permissions WHERE user_id = ?");
        $stmt->execute([$delete_id]);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $pdo->commit();
        $_SESSION['toast_message'] = $result ? 'Xóa tài khoản thành công!' : 'Xóa tài khoản thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Delete error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi xóa: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: quantri.php");
    exit;
}

// Handle add/edit account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_user']) || isset($_POST['edit_user']))) {
    $pdo->beginTransaction();
    try {
        log_debug('POST data: ' . print_r($_POST, true));
        log_debug('POST menu_permissions: ' . print_r($_POST['menu_permissions'] ?? [], true));

        $username = $_POST['username'];
        $password = !empty($_POST['password']) ? $_POST['password'] : null;
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $dob = $_POST['dob'];
        $role_id = $_POST['role_id'];
        $avatar = null;

        if (!empty($_FILES['avatar']['name'])) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $avatar_name = time() . '_' . basename($_FILES['avatar']['name']);
            $avatar_path = $upload_dir . $avatar_name;
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                throw new Exception("Failed to upload avatar");
            }
            $avatar = '/kai/admin/uploads/' . $avatar_name;
        }

        if (isset($_POST['add_user'])) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, address, dob, role_id, avatar, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->execute([$username, $password, $fullname, $email, $phone, $address, $dob, $role_id, $avatar]);
            $user_id = $pdo->lastInsertId();
            log_debug("Added user ID: $user_id");
        } else {
            $user_id = $_POST['user_id'];
            if ($avatar && $password) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, fullname = ?, email = ?, phone = ?, address = ?, dob = ?, role_id = ?, avatar = ? WHERE id = ?");
                $stmt->execute([$username, $password, $fullname, $email, $phone, $address, $dob, $role_id, $avatar, $user_id]);
            } elseif ($avatar) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, fullname = ?, email = ?, phone = ?, address = ?, dob = ?, role_id = ?, avatar = ? WHERE id = ?");
                $stmt->execute([$username, $fullname, $email, $phone, $address, $dob, $role_id, $avatar, $user_id]);
            } elseif ($password) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, fullname = ?, email = ?, phone = ?, address = ?, dob = ?, role_id = ? WHERE id = ?");
                $stmt->execute([$username, $password, $fullname, $email, $phone, $address, $dob, $role_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, fullname = ?, email = ?, phone = ?, address = ?, dob = ?, role_id = ? WHERE id = ?");
                $stmt->execute([$username, $fullname, $email, $phone, $address, $dob, $role_id, $user_id]);
            }
            log_debug("Updated user ID: $user_id");
        }

        $stmt = $pdo->prepare("DELETE FROM user_menu_permissions WHERE user_id = ?");
        $stmt->execute([$user_id]);
        log_debug("Deleted old permissions for user ID: $user_id");

        $menu_items = $pdo->query("SELECT id, name FROM menu_items ORDER BY position, id")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($menu_items)) {
            throw new Exception("No menu items found in menu_items table");
        }

        $stmt = $pdo->prepare("INSERT INTO user_menu_permissions (user_id, menu_item_id, can_view, can_add, can_edit, can_delete) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($menu_items as $menu) {
            $menu_id = $menu['id'];
            $can_view = isset($_POST['menu_permissions'][$menu_id]['view']) ? 1 : 0;
            $can_add = isset($_POST['menu_permissions'][$menu_id]['add']) ? 1 : 0;
            $can_edit = isset($_POST['menu_permissions'][$menu_id]['edit']) ? 1 : 0;
            $can_delete = isset($_POST['menu_permissions'][$menu_id]['delete']) ? 1 : 0;
            if ($can_view || $can_add || $can_edit || $can_delete) {
                $stmt->execute([$user_id, $menu_id, $can_view, $can_add, $can_edit, $can_delete]);
                log_debug("Saved permission for user ID: $user_id, menu ID: $menu_id, view: $can_view, add: $can_add, edit: $can_edit, delete: $can_delete");
            }
        }

        $pdo->commit();
        $_SESSION['toast_message'] = isset($_POST['add_user']) ? 'Thêm tài khoản thành công!' : 'Cập nhật tài khoản thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        log_debug('Save error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: quantri.php");
    exit;
}
// Handle send email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    try {
        $selected_users = isset($_POST['selected_users']) ? $_POST['selected_users'] : [];
        $subject = $_POST['subject'];
        $content = $_POST['content'];

        if (empty($selected_users)) {
            throw new Exception("Vui lòng chọn ít nhất một người nhận!");
        }

        if (empty($subject) || empty($content)) {
            throw new Exception("Tiêu đề và nội dung email không được để trống!");
        }

        // Initialize PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'badaotulong123@gmail.com'; // Thay bằng email của bạn
        $mail->Password = 'nihu fluz qcla wgmh'; // Thay bằng mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('badaotulong123@gmail.com', 'Kaiadmin');
        $mail->isHTML(true);

        // Fetch selected users' emails
        $stmt = $pdo->prepare("SELECT email, fullname FROM users WHERE id IN (" . implode(',', array_fill(0, count($selected_users), '?')) . ")");
        $stmt->execute($selected_users);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient['email'], $recipient['fullname']);
        }

        $mail->Subject = $subject;
        $mail->Body = nl2br($content);

        $mail->send();
        $_SESSION['toast_message'] = 'Gửi email thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        log_debug('Email error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi gửi email: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: quantri.php?active=mail");
    exit;
}

// Fetch user list
$stmt = $pdo->query("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch roles
$stmt = $pdo->query("SELECT * FROM roles ORDER BY id");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch menu items
$stmt = $pdo->query("SELECT * FROM menu_items ORDER BY position, id");
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch permissions for edit
$menu_permissions = [];
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM user_menu_permissions WHERE user_id = ?");
    $stmt->execute([$edit_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $menu_permissions[$row['menu_item_id']] = [
            'view' => $row['can_view'],
            'add' => $row['can_add'],
            'edit' => $row['can_edit'],
            'delete' => $row['can_delete']
        ];
    }
}

// Get active state
$active = isset($_GET['active']) ? $_GET['active'] : 'list';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản trị tài khoản - Kaiadmin</title>
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon">
    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: {"families":["Public Sans:300,400,500,600,700"]},
            custom: {"families":["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['assets/css/fonts.min.css']},
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <!-- iZitoast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <!-- Bootstrap Switch CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/css/bootstrap3/bootstrap-switch.min.css" rel="stylesheet">
    <script src="ckeditor/ckeditor.js"></script>

    <style>
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .tab-content {
            padding: 20px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }
        .contact-image, .avatar-image {
            max-width: 50px;
            height: auto;
        }
        .form-group label {
            font-weight: 500;
        }
        .form-header {
            margin-bottom: 20px;
        }
        .form-header .btn-primary {
            padding: 8px 20px;
        }
        .add-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        @media (max-width: 768px) {
            .tab-content {
                padding: 10px;
            }
            .table-responsive {
                margin-bottom: 1rem;
            }
            .form-header {
                margin-bottom: 15px;
            }
            .form-header .btn-primary {
                padding: 6px 15px;
                font-size: 0.9rem;
            }
            .add-btn {
                position: static;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'include/sidebar.php'; ?>
        <div class="main-panel">
            <?php include 'include/header.php'; ?>
            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Quản trị tài khoản</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="index.php">
                                    <i class="icon-home"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Quản trị</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($active === 'add' || $active === 'edit'): ?>
                            <!-- Form Add/Edit Account and Permissions -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $active === 'add' ? 'Thêm tài khoản' : 'Sửa tài khoản'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $edit_user = null;
                                        if ($active === 'edit' && isset($_GET['edit_id'])) {
                                            $edit_id = $_GET['edit_id'];
                                            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                                            $stmt->execute([$edit_id]);
                                            $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
                                        }
                                        ?>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="row">
                                                <!-- Permissions Table -->
                                                <div class="col-md-4">
                                                    <h4>Phân quyền Menu</h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Tên Menu</th>
                                                                    <th>Xem</th>
                                                                    <th>Thêm</th>
                                                                    <th>Sửa</th>
                                                                    <th>Xóa</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($menu_items as $menu): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($menu['name']); ?></td>
                                                                        <td><input type="checkbox" name="menu_permissions[<?php echo $menu['id']; ?>][view]" <?php echo isset($menu_permissions[$menu['id']]['view']) && $menu_permissions[$menu['id']]['view'] ? 'checked' : ''; ?>></td>
                                                                        <td><input type="checkbox" name="menu_permissions[<?php echo $menu['id']; ?>][add]" <?php echo isset($menu_permissions[$menu['id']]['add']) && $menu_permissions[$menu['id']]['add'] ? 'checked' : ''; ?>></td>
                                                                        <td><input type="checkbox" name="menu_permissions[<?php echo $menu['id']; ?>][edit]" <?php echo isset($menu_permissions[$menu['id']]['edit']) && $menu_permissions[$menu['id']]['edit'] ? 'checked' : ''; ?>></td>
                                                                        <td><input type="checkbox" name="menu_permissions[<?php echo $menu['id']; ?>][delete]" <?php echo isset($menu_permissions[$menu['id']]['delete']) && $menu_permissions[$menu['id']]['delete'] ? 'checked' : ''; ?>></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <!-- User Input Fields -->
                                                <div class="col-md-8">
                                                    <div class="form-header d-flex justify-content-end">
                                                        <button type="submit" name="<?php echo $active === 'edit' ? 'edit_user' : 'add_user'; ?>" class="btn btn-primary">
                                                            <i class="fas fa-save"></i> Lưu
                                                        </button>
                                                        <a href="quantri.php" class="btn btn-secondary ml-2">
                                                            <i class="fas fa-times"></i> Hủy
                                                        </a>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Tên đăng nhập</label>
                                                        <input type="text" name="username" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Mật khẩu</label>
                                                        <input type="password" name="password" class="form-control" <?php echo $active === 'add' ? 'required' : ''; ?> placeholder="Để trống nếu không đổi">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Họ tên</label>
                                                        <input type="text" name="fullname" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['fullname']) : ''; ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" name="email" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Số điện thoại</label>
                                                        <input type="text" name="phone" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['phone']) : ''; ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Địa chỉ</label>
                                                        <input type="text" name="address" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['address']) : ''; ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Ngày sinh</label>
                                                        <input type="date" name="dob" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['dob']) : ''; ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Quyền hạn</label>
                                                        <select name="role_id" class="form-control" required>
                                                            <option value="">Chọn quyền</option>
                                                            <?php foreach ($roles as $role): ?>
                                                                <option value="<?php echo $role['id']; ?>" <?php echo $edit_user && $edit_user['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($role['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Avatar</label>
                                                        <input type="file" name="avatar" class="form-control" accept="image/*">
                                                        <?php if ($edit_user && $edit_user['avatar']): ?>
                                                            <img src="<?php echo htmlspecialchars($edit_user['avatar']); ?>" class="avatar-image mt-2" alt="Avatar">
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($active === 'edit'): ?>
                                                        <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if ($active === 'mail'): ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Gửi Email</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Chọn người nhận</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>" class="user-checkbox"></td>
                                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tiêu đề</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nội dung</label>
                        <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                    </div>

                    <div class="form-header d-flex justify-content-end">
                        <button type="submit" name="send_email" class="btn btn-primary">Gửi</button>
                        <a href="quantri.php" class="btn btn-secondary ml-2">Hủy</a>
                    </div>
                </form>
                <script>
                    // CKEDITOR.replace('description');
                    CKEDITOR.replace('content');
                </script>
            </div>
        </div>
    </div>
<?php endif; ?>
                            <!-- Account List -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách tài khoản</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-end mb-3">
                                            <a href="?active=add" class="btn btn-primary mr-2">
                                                <i class="fas fa-user-plus"></i> Thêm tài khoản
                                            </a>
                                            <a href="?active=mail" class="btn btn-info">
                                                <i class="fas fa-envelope"></i> Gửi mail
                                            </a>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Tên user</th>
                                                        <th>Tên đăng nhập</th>
                                                        <th>Số điện thoại</th>
                                                        <th>Email</th>
                                                        <th>Ngày sinh</th>
                                                        <th>Quyền</th>
                                                        <!-- <th>Tác giả</th> -->
                                                        <th>Trạng thái</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($users as $user): ?>
                                                        <tr>
                                                            <td><?php echo $user['id']; ?></td>
                                                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['dob']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                                            <!-- <td><?php echo htmlspecialchars($user['fullname']); ?></td> -->
                                                            <td>
                                                                <form method="POST" action="quantri.php">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $user['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?active=edit&edit_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                                    <i class="fas fa-edit"></i> Sửa
                                                                </a>
                                                                <a href="?delete_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                                    <i class="fas fa-trash-alt"></i> Xoá
                                                                </a>
                                                            </td>

                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php include 'include/footer.php'; ?>
        <?php include 'include/custom-template.php'; ?>
        </div>
    </div>

    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/world.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
    <script src="assets/js/setting-demo.js"></script>
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <!-- Bootstrap Switch JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/js/bootstrap-switch.min.js"></script>
    <script>
    $(document).ready(function() {
        // Select/Deselect all checkboxes
        $('#select-all').on('change', function() {
            $('.user-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Update select-all checkbox based on individual checkboxes
        $('.user-checkbox').on('change', function() {
            if ($('.user-checkbox:checked').length === $('.user-checkbox').length) {
                $('#select-all').prop('checked', true);
            } else {
                $('#select-all').prop('checked', false);
            }
        });
    });
</script>
    <script>
        $(document).ready(function() {
            // iZitoast notification
            <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
                iziToast.<?php echo $_SESSION['toast_type']; ?>({
                    title: '<?php echo $_SESSION['title'] ?? ($_SESSION['toast_type'] === 'success' ? 'Thành công' : 'Lỗi'); ?>',
                    message: '<?php echo $_SESSION['toast_message']; ?>',
                    position: 'topRight',
                    timeout: 6000
                });
                <?php
                unset($_SESSION['toast_message']);
                unset($_SESSION['toast_type']);
                unset($_SESSION['title']);
                ?>
            <?php endif; ?>

            // Initialize Bootstrap Switch
            $(".toggle-switch").bootstrapSwitch({
                onText: 'Bật',
                offText: 'Tắt',
                onColor: 'success',
                offColor: 'danger',
                size: 'small'
            });
        });
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>