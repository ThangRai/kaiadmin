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
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $ward = trim($_POST['ward']);
    $address_detail = trim($_POST['address_detail']);
    $password = trim($_POST['password'] ?? '');

    // Xử lý ảnh đại diện
    $avatar = $user['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['toast_message'] = 'Chỉ cho phép định dạng JPG, JPEG, PNG hoặc GIF.';
            $_SESSION['toast_type'] = 'error';
            header("Location: profile.php");
            exit;
        }

        if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $_SESSION['toast_message'] = 'Kích thước file không được vượt quá 2MB.';
            $_SESSION['toast_type'] = 'error';
            header("Location: profile.php");
            exit;
        }

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
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, phone = ?, province = ?, district = ?, ward = ?, address_detail = ?, avatar = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$fullname, $email, $username, $phone, $province, $district, $ward, $address_detail, $avatar, $password, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, phone = ?, province = ?, district = ?, ward = ?, address_detail = ?, avatar = ? WHERE id = ?");
            $result = $stmt->execute([$fullname, $email, $username, $phone, $province, $district, $ward, $address_detail, $avatar, $userId]);
        }

        if ($result) {
            $_SESSION['toast_message'] = 'Cập nhật thông tin thành công!';
            $_SESSION['toast_type'] = 'success';
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
        .loading-spinner {
            display: none;
            margin-left: 10px;
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
                            <label>Tỉnh/Thành phố</label>
                            <select name="province" id="province" class="form-control" required>
                                <option value="">Chọn Tỉnh/Thành phố</option>
                            </select>
                            <span class="loading-spinner" id="province-loading">Đang tải...</span>
                        </div>
                        <div class="mb-3">
                            <label>Quận/Huyện</label>
                            <select name="district" id="district" class="form-control" required>
                                <option value="">Chọn Quận/Huyện</option>
                            </select>
                            <span class="loading-spinner" id="district-loading">Đang tải...</span>
                        </div>
                        <div class="mb-3">
                            <label>Phường/Xã</label>
                            <select name="ward" id="ward" class="form-control" required>
                                <option value="">Chọn Phường/Xã</option>
                            </select>
                            <span class="loading-spinner" id="ward-loading">Đang tải...</span>
                        </div>
                        <div class="mb-3">
                            <label>Địa chỉ chi tiết</label>
                            <input type="text" name="address_detail" class="form-control" value="<?= htmlspecialchars($user['address_detail'] ?? '') ?>" placeholder="Số nhà, tên đường...">
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
        <?php include 'include/custom-template.php'; ?>
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

            // Hàm hiển thị spinner
            function showSpinner(id) {
                $(`#${id}`).show();
            }
            function hideSpinner(id) {
                $(`#${id}`).hide();
            }

            // Load provinces
            showSpinner('province-loading');
            $.getJSON('https://provinces.open-api.vn/api/p/', function(data) {
                hideSpinner('province-loading');
                let provinceSelect = $('#province');
                provinceSelect.append('<option value="">Chọn Tỉnh/Thành phố</option>');
                $.each(data, function(index, province) {
                    provinceSelect.append(`<option value="${province.name}" data-code="${province.code}">${province.name}</option>`);
                });
                // Set current province
                provinceSelect.val('<?= htmlspecialchars($user['province'] ?? '') ?>');
                if ('<?= htmlspecialchars($user['province'] ?? '') ?>' !== '') {
                    provinceSelect.trigger('change');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                hideSpinner('province-loading');
                iziToast.error({
                    title: 'Lỗi',
                    message: 'Không thể tải danh sách Tỉnh/Thành phố: ' + textStatus,
                    position: 'topRight',
                    timeout: 5000
                });
            });

            // Load districts when province changes
            $('#province').change(function() {
                let provinceCode = $(this).find(':selected').data('code');
                let districtSelect = $('#district');
                let wardSelect = $('#ward');
                districtSelect.empty().append('<option value="">Chọn Quận/Huyện</option>');
                wardSelect.empty().append('<option value="">Chọn Phường/Xã</option>');
                if (provinceCode) {
                    showSpinner('district-loading');
                    $.getJSON(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`, function(data) {
                        hideSpinner('district-loading');
                        $.each(data.districts, function(index, district) {
                            districtSelect.append(`<option value="${district.name}" data-code="${district.code}">${district.name}</option>`);
                        });
                        // Set current district
                        if ('<?= htmlspecialchars($user['district'] ?? '') ?>' !== '') {
                            districtSelect.val('<?= htmlspecialchars($user['district'] ?? '') ?>');
                            districtSelect.trigger('change');
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        hideSpinner('district-loading');
                        iziToast.error({
                            title: 'Lỗi',
                            message: 'Không thể tải danh sách Quận/Huyện: ' + textStatus,
                            position: 'topRight',
                            timeout: 5000
                        });
                    });
                }
            });

            // Load wards when district changes
            $('#district').change(function() {
                let districtCode = $(this).find(':selected').data('code');
                let wardSelect = $('#ward');
                wardSelect.empty().append('<option value="">Chọn Phường/Xã</option>');
                if (districtCode) {
                    showSpinner('ward-loading');
                    $.getJSON(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`, function(data) {
                        hideSpinner('ward-loading');
                        if (data.wards && data.wards.length > 0) {
                            $.each(data.wards, function(index, ward) {
                                wardSelect.append(`<option value="${ward.name}">${ward.name}</option>`);
                            });
                            // Set current ward
                            if ('<?= htmlspecialchars($user['ward'] ?? '') ?>' !== '') {
                                wardSelect.val('<?= htmlspecialchars($user['ward'] ?? '') ?>');
                            }
                        } else {
                            iziToast.warning({
                                title: 'Cảnh báo',
                                message: 'Không có dữ liệu Phường/Xã cho Quận/Huyện này.',
                                position: 'topRight',
                                timeout: 5000
                            });
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        hideSpinner('ward-loading');
                        iziToast.error({
                            title: 'Lỗi',
                            message: 'Không thể tải danh sách Phường/Xã: ' + textStatus,
                            position: 'topRight',
                            timeout: 5000
                        });
                    });
                }
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