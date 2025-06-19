<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'database/config.php';
require_once 'include/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Không tìm thấy người dùng.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password'] ?? '');

    // Xử lý ảnh đại diện
    $avatar = $user['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = 'uploads/';
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Kiểm tra định dạng file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['toast_message'] = 'Chỉ cho phép định dạng JPG, JPEG, PNG hoặc GIF.';
            $_SESSION['toast_type'] = 'error';
            header("Location: profile.php");
            exit;
        }

        // Kiểm tra kích thước file (giới hạn 2MB)
        if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $_SESSION['toast_message'] = 'Kích thước file không được vượt quá 2MB.';
            $_SESSION['toast_type'] = 'error';
            header("Location: profile.php");
            exit;
        }

        // Tạo tên file duy nhất
        $avatarName = time() . '_' . basename($_FILES['avatar']['name']);
        $avatarPath = $uploadDir . $avatarName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath)) {
            $avatar = '/kai/admin/uploads/' . $avatarName;
        } else {
            $_SESSION['toast_message'] = 'Lỗi khi tải lên ảnh đại diện.';
            $_SESSION['toast_type'] = 'error';
            header("Location: profile.php");
            exit;
        }
    }

    try {
        // Nếu người dùng nhập mật khẩu mới
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, phone = ?, address = ?, avatar = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$fullname, $email, $username, $phone, $address, $avatar, $password, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, phone = ?, address = ?, avatar = ? WHERE id = ?");
            $result = $stmt->execute([$fullname, $email, $username, $phone, $address, $avatar, $userId]);
        }

        if ($result) {
            $_SESSION['toast_message'] = 'Cập nhật thông tin thành công!';
            $_SESSION['toast_type'] = 'success';
            // Cập nhật session
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['avatar'] = $avatar;
        } else {
            $_SESSION['toast_message'] = 'Cập nhật thông tin thất bại.';
            $_SESSION['toast_type'] = 'error';
        }
    } catch (PDOException $e) {
        log_debug('Update profile error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <title>Trang cá nhân - Kaiadmin</title>
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon" />
    <!-- iZitoast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css" />
    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["assets/css/fonts.min.css"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <style>
        /* Định vị nút Cập nhật cố định */
        .fixed-update-btn {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            padding: 10px 20px;
            font-size: 16px;
        }
        @media (max-width: 768px) {
            .fixed-update-btn {
                top: 80px;
                right: 10px;
                padding: 8px 15px;
                font-size: 14px;
            }
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .sidebar-mini .sidebar {
                display: block;
                width: 250px;
                position: fixed;
                z-index: 1000;
            }
            .sidebar-mini .main-panel {
                margin-left: 0;
            }
        }
        .nav-item.active > a,
        .nav-collapse li.active a {
            background-color: #1a2035 !important;
            color: #ffffff !important;
        }
        .nav-item.active > a i,
        .nav-collapse li.active a i {
            color: #ffffff !important;
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
                    <h2 class="mb-4">Thông tin cá nhân</h2>
                    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded" style="max-width: 100%;">
                        <button type="submit" class="btn btn-primary fixed-update-btn" style="margin-right: 10px;">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                        <div class="mb-3">
                            <label>Họ tên</label>
                            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Mật khẩu mới (bỏ trống nếu không đổi)</label>
                            <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu mới">
                        </div>
                        <div class="mb-3">
                            <label>Điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label>Địa chỉ</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label>Ảnh đại diện</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?= htmlspecialchars($user['avatar']) ?>" width="80" class="mt-2 rounded-circle" alt="Avatar">
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid d-flex justify-content-center">
                    <div class="copyright">
                        2024, made with <i class="fa fa-heart heart text-danger"></i> by
                        <a href="http://www.themekita.com">Thắng Rai</a>
                    </div>
                </div>
            </footer>
        </div>
        <div class="custom-template">
            <div class="title">Settings</div>
            <div class="custom-content">
                <div class="switcher">
                    <div class="switch-block">
                        <h4>Logo Header</h4>
                        <div class="btnSwitch">
                            <button type="button" class="selected changeLogoHeaderColor" data-color="dark"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="blue"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="purple"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="light-blue"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="green"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="orange"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="red"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="white"></button>
                            <br />
                            <button type="button" class="changeLogoHeaderColor" data-color="dark2"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="blue2"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="purple2"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="light-blue2"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="green2"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="orange2"></button>
                            <button type="button" class="changeLogoHeaderColor" data-color="red2"></button>
                        </div>
                    </div>
                    <div class="switch-block">
                        <h4>Navbar Header</h4>
                        <div class="btnSwitch">
                            <button type="button" class="changeTopBarColor" data-color="dark"></button>
                            <button type="button" class="changeTopBarColor" data-color="blue"></button>
                            <button type="button" class="changeTopBarColor" data-color="purple"></button>
                            <button type="button" class="changeTopBarColor" data-color="light-blue"></button>
                            <button type="button" class="changeTopBarColor" data-color="green"></button>
                            <button type="button" class="changeTopBarColor" data-color="orange"></button>
                            <button type="button" class="changeTopBarColor" data-color="red"></button>
                            <button type="button" class="selected changeTopBarColor" data-color="white"></button>
                            <br />
                            <button type="button" class="changeTopBarColor" data-color="dark2"></button>
                            <button type="button" class="changeTopBarColor" data-color="blue2"></button>
                            <button type="button" class="changeTopBarColor" data-color="purple2"></button>
                            <button type="button" class="changeTopBarColor" data-color="light-blue2"></button>
                            <button type="button" class="changeTopBarColor" data-color="green2"></button>
                            <button type="button" class="changeTopBarColor" data-color="orange2"></button>
                            <button type="button" class="changeTopBarColor" data-color="red2"></button>
                        </div>
                    </div>
                    <div class="switch-block">
                        <h4>Sidebar</h4>
                        <div class="btnSwitch">
                            <button type="button" class="changeSideBarColor" data-color="white"></button>
                            <button type="button" class="selected changeSideBarColor" data-color="dark"></button>
                            <button type="button" class="changeSideBarColor" data-color="dark2"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="custom-toggle">
                <i class="icon-settings"></i>
            </div>
        </div>
    </div>
    <!-- Core JS Files -->
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
    <script>
        $(document).ready(function() {
            // Toggle sidebar
            $('.toggle-sidebar').on('click', function(e) {
                e.stopPropagation();
                $('body').toggleClass('sidebar-mini');
                $('.sidebar').toggleClass('sidebar-mini');
            });
            $('.sidenav-toggler').on('click', function(e) {
                e.stopPropagation();
                $('body').removeClass('sidebar-mini');
                $('.sidebar').removeClass('sidebar-mini');
            });
            $('.nav-item a[data-bs-toggle="collapse"]').on('click', function(e) {
                e.stopPropagation();
            });
            $('.nav-item').on('click', function(e) {
                e.stopPropagation();
            });
            // iZitoast notification
            <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
                iziToast.<?php echo $_SESSION['toast_type']; ?>({
                    title: '<?php echo $_SESSION['toast_type'] === 'success' ? 'Thành công' : 'Lỗi'; ?>',
                    message: '<?php echo $_SESSION['toast_message']; ?>',
                    position: 'topRight',
                    timeout: 5000
                });
                <?php
                unset($_SESSION['toast_message']);
                unset($_SESSION['toast_type']);
                ?>
            <?php endif; ?>
        });
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>