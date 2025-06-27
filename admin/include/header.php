<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu người dùng chưa đăng nhập thì chuyển hướng về trang login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Gán biến để dùng trong giao diện
$fullname = $_SESSION['fullname'] ?? 'Người dùng';
$avatar = $_SESSION['avatar'] ?? 'default.png';
$email = $_SESSION['email'] ?? 'Chưa cập nhật';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Kaiadmin - Bootstrap 5 Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon" />
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
            active: function() {
                sessionStorage.fonts = true;
            },
        });
    </script>
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <!-- iZitoast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <style>
        nav.navbar.navbar-header-left.navbar-expand-lg.navbar-form.nav-search.p-0.d-none.d-lg-flex {
            width: 50%;
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .search-results .result-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .search-results .result-item:hover {
            background: #f8f9fa;
        }
        .search-results .result-item a {
            color: #333;
            text-decoration: none;
            display: block;
        }
        .search-results .no-results {
            padding: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="main-header">
        <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
                <a href="index.php" class="logo">
                    <img src="assets/img/kaiadmin/logo_light.svg" alt="navbar brand" class="navbar-brand" height="20" />
                </a>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="gg-menu-right"></i>
                    </button>
                    <button class="btn btn-toggle sidenav-toggler">
                        <i class="gg-menu-left"></i>
                    </button>
                </div>
                <button class="topbar-toggler more">
                    <i class="gg-more-vertical-alt"></i>
                </button>
            </div>
            <!-- End Logo Header -->
        </div>
        <!-- Navbar Header -->
        <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
            <div class="container-fluid">
                <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
                    <div class="input-group position-relative">
                        <div class="input-group-prepend">
                            <button type="submit" class="btn btn-search pe-1">
                                <i class="fa fa-search search-icon"></i>
                            </button>
                        </div>
                        <input type="text" id="search-input" placeholder="Tìm kiếm ..." class="form-control" />
                        <div id="search-results" class="search-results"></div>
                    </div>
                </nav>
                <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                    <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" aria-haspopup="true">
                            <i class="fa fa-search"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-search animated fadeIn">
                            <form class="navbar-left navbar-form nav-search">
                                <div class="input-group">
                                    <input type="text" id="mobile-search-input" placeholder="Tìm kiếm ..." class="form-control" />
                                    <div id="mobile-search-results" class="search-results"></div>
                                </div>
                            </form>
                        </ul>
                    </li>
                    <li class="nav-item topbar-icon dropdown hidden-caret">
                        <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-envelope"></i>
                        </a>
                        <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
                            <li>
                                <div class="dropdown-title d-flex justify-content-between align-items-center">
                                    Tin nhắn
                                    <a href="#" class="small">Đánh dấu tất cả đã đọc</a>
                                </div>
                            </li>
                            <li>
                                <div class="message-notif-scroll scrollbar-outer">
                                    <div class="notif-center">
                                        <a href="#">
                                            <div class="notif-img">
                                                <img src="assets/img/jm_denis.jpg" alt="Img Profile" />
                                            </div>
                                            <div class="notif-content">
                                                <span class="subject">Jimmy Denis</span>
                                                <span class="block"> Bạn khỏe không? </span>
                                                <span class="time">5 phút trước</span>
                                            </div>
                                        </a>
                                        <a href="#">
                                            <div class="notif-img">
                                                <img src="assets/img/chadengle.jpg" alt="Img Profile" />
                                            </div>
                                            <div class="notif-content">
                                                <span class="subject">Chad</span>
                                                <span class="block"> Ok, Cảm ơn! </span>
                                                <span class="time">12 phút trước</span>
                                            </div>
                                        </a>
                                        <a href="#">
                                            <div class="notif-img">
                                                <img src="assets/img/mlane.jpg" alt="Img Profile" />
                                            </div>
                                            <div class="notif-content">
                                                <span class="subject">Jhon Doe</span>
                                                <span class="block"> Sẵn sàng cho cuộc họp hôm nay... </span>
                                                <span class="time">12 phút trước</span>
                                            </div>
                                        </a>
                                        <a href="#">
                                            <div class="notif-img">
                                                <img src="assets/img/talha.jpg" alt="Img Profile" />
                                            </div>
                                            <div class="notif-content">
                                                <span class="subject">Talha</span>
                                                <span class="block"> Chào, Apa Kabar? </span>
                                                <span class="time">17 phút trước</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a class="see-all" href="javascript:void(0);">Xem tất cả tin nhắn<i class="fa fa-angle-right"></i></a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item topbar-icon dropdown hidden-caret">
                        <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bell"></i>
                            <span class="notification">4</span>
                        </a>
                        <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                            <li>
                                <div class="dropdown-title">Bạn có 4 thông báo mới</div>
                            </li>
                            <li>
                                <div class="notif-scroll scrollbar-outer">
                                    <div class="notif-center">
                                        <a href="#">
                                            <div class="notif-icon notif-primary">
                                                <i class="fa fa-user-plus"></i>
                                            </div>
                                            <div class="notif-content">
                                                <span class="block"> Người dùng mới đã đăng ký </span>
                                                <span class="time">5 phút trước</span>
                                            </div>
                                        </a>
                                        <a href="#">
                                            <div class="notif-icon notif-success">
                                                <i class="fa fa-comment"></i>
                                            </div>
                                            <div class="notif-content">
                                                <span class="block"> Rahmad đã bình luận về Admin </span>
                                                <span class="time">12 phút trước</span>
                                            </div>
                                        </a>
                                        <a href="#">
                                            <div class="notif-img">
                                                <img src="assets/img/profile2.jpg" alt="Img Profile" />
                                            </div>
                                            <div class="notif-content">
                                                <span class="block"> Reza gửi tin nhắn cho bạn </span>
                                                <span class="time">12 phút trước</span>
                                            </div>
                                        </a>
                                        <a href="#">
                                            <div class="notif-icon notif-danger">
                                                <i class="fa fa-heart"></i>
                                            </div>
                                            <div class="notif-content">
                                                <span class="block"> Farrah đã thích Admin </span>
                                                <span class="time">17 phút trước</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a class="see-all" href="javascript:void(0);">Xem tất cả thông báo<i class="fa fa-angle-right"></i></a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item topbar-icon hidden-caret">
                        <a class="nav-link" href="#" id="btnFullscreen">
                            <i class="fas fa-expand"></i> <!-- Icon full màn hình -->
                        </a>
                    </li>

                    <li class="nav-item topbar-user dropdown hidden-caret">
                        <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                            <div class="avatar-sm">
                                <img src="<?php echo $avatar; ?>" alt="avatar" class="avatar-img rounded-circle" />
                            </div>
                            <span class="profile-username">
                                <span class="op-7">Hi !</span>
                                <span class="fw-bold"><?php echo $fullname; ?></span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-user animated fadeIn">
                            <div class="dropdown-user-scroll scrollbar-outer">
                                <li>
                                    <div class="user-box">
                                        <div class="avatar">
                                            <img src="<?php echo $avatar; ?>" alt="avatar" class="avatar-img rounded" style="width: 40px; height: auto;" />
                                        </div>
                                        <div class="u-text">
                                            <h4><?php echo $fullname; ?></h4>
                                            <p class="text-muted"><?php echo $email; ?></p>
                                            <!-- <a href="profile.php" class="btn btn-xs btn-secondary btn-sm">Xem hồ sơ</a> -->
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user"></i> Hồ sơ của tôi
                                    </a>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                    </a>
                                </li>

                            </div>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- End Navbar -->
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <!-- <script src="assets/js/core/bootstrap.min.js"></script> -->
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Live search cho desktop
            $('#search-input').on('input', function() {
                let query = $(this).val().trim();
                if (query.length > 0) {
                    $.ajax({
                        url: 'search.php',
                        method: 'GET',
                        data: { q: query },
                        success: function(response) {
                            $('#search-results').html(response).show();
                        },
                        error: function() {
                            $('#search-results').html('<div class="no-results">Lỗi khi tìm kiếm!</div>').show();
                        }
                    });
                } else {
                    $('#search-results').hide();
                }
            });

            // Live search cho mobile
            $('#mobile-search-input').on('input', function() {
                let query = $(this).val().trim();
                if (query.length > 0) {
                    $.ajax({
                        url: 'search.php',
                        method: 'GET',
                        data: { q: query },
                        success: function(response) {
                            $('#mobile-search-results').html(response).show();
                        },
                        error: function() {
                            $('#mobile-search-results').html('<div class="no-results">Lỗi khi tìm kiếm!</div>').show();
                        }
                    });
                } else {
                    $('#mobile-search-results').hide();
                }
            });

            // Ẩn kết quả khi click ra ngoài
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.input-group').length) {
                    $('#search-results, #mobile-search-results').hide();
                }
            });
        });
    </script>
    <!-- Nút full màn hình  -->
    <script>
    document.getElementById('btnFullscreen').addEventListener('click', function(e) {
        e.preventDefault();
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    });
</script>

</body>
</html>
<?php ob_end_flush(); ?>