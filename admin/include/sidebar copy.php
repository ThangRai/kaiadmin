<?php
// Không có khoảng trắng trước dòng này
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$fullname = $_SESSION['fullname'] ?? 'Người dùng';
$avatar = $_SESSION['avatar'] ?? 'default.png';
$email = $_SESSION['email'] ?? 'Chưa cập nhật';

// Lấy tên file hiện tại để xác định trang
$current_page = basename($_SERVER['PHP_SELF']);

// Định nghĩa các trang thuộc danh mục "Cơ Bản" để highlight danh mục cha
$base_pages = [
    'avatars.php',
    'components/buttons.html',
    'components/gridsystem.html',
    'components/panels.html',
    'components/notifications.html',
    'components/sweetalert.html',
    'font-awesome-icons.php',
    'components/simple-line-icons.html',
    'components/typography.html'
];
?>
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="index.php" class="logo">
                <img
                    src="assets/img/kaiadmin/logo_light.svg"
                    alt="thương hiệu navbar"
                    class="navbar-brand"
                    height="20"
                />
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
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
<a href="index.php">
    <i class="fas fa-home"></i>
    <p>Bảng Điều Khiển</p>
</a>

                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Thành Phần</h4>
                </li>
                <li class="nav-item <?php echo in_array($current_page, $base_pages) ? 'active' : ''; ?>">
                    <a
                        data-bs-toggle="collapse"
                        href="#base"
                        class="<?php echo in_array($current_page, $base_pages) ? '' : 'collapsed'; ?>"
                        aria-expanded="<?php echo in_array($current_page, $base_pages) ? 'true' : 'false'; ?>"
                    >
                        <i class="fas fa-layer-group"></i>
                        <p>Cơ Bản</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo in_array($current_page, $base_pages) ? 'show' : ''; ?>" id="base">
                        <ul class="nav nav-collapse">
                            <li class="<?php echo $current_page == 'avatars.php' ? 'active' : ''; ?>">
                                <a href="avatars.php">
                                    <span class="sub-item">Hình Đại Diện</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'components/buttons.html' ? 'active' : ''; ?>">
                                <a href="components/buttons.html">
                                    <span class="sub-item">Nút</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'components/gridsystem.html' ? 'active' : ''; ?>">
                                <a href="components/gridsystem.html">
                                    <span class="sub-item">Hệ Thống Lưới</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'components/panels.html' ? 'active' : ''; ?>">
                                <a href="components/panels.html">
                                    <span class="sub-item">Bảng</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'components/notifications.html' ? 'active' : ''; ?>">
                                <a href="components/notifications.html">
                                    <span class="sub-item">Thông Báo</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'components/sweetalert.html' ? 'active' : ''; ?>">
                                <a href="components/sweetalert.html">
                                    <span class="sub-item">Cảnh Báo Ngọt</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'font-awesome-icons.php' ? 'active' : ''; ?>">
                                <a href="font-awesome-icons.php">
                                    <span class="sub-item">Biểu Tượng Font Awesome</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'components/simple-line-icons.html' ? 'active' : ''; ?>">
                                <a href="components/simple-line-icons.html">
                                    <span class="sub-item">Biểu Tượng Đơn Giản</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'components/typography.html' ? 'active' : ''; ?>">
                                <a href="components/typography.html">
                                    <span class="sub-item">Kiểu Chữ</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?php echo in_array($current_page, ['sidebar-style-2.html', 'icon-menu.html']) ? 'active' : ''; ?>">
                    <a
                        data-bs-toggle="collapse"
                        href="#sidebarLayouts"
                        class="<?php echo in_array($current_page, ['sidebar-style-2.html', 'icon-menu.html']) ? '' : 'collapsed'; ?>"
                        aria-expanded="<?php echo in_array($current_page, ['sidebar-style-2.html', 'icon-menu.html']) ? 'true' : 'false'; ?>"
                    >
                        <i class="fas fa-th-list"></i>
                        <p>Bố Cục Thanh Bên</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo in_array($current_page, ['sidebar-style-2.html', 'icon-menu.html']) ? 'show' : ''; ?>" id="sidebarLayouts">
                        <ul class="nav nav-collapse">
                            <li class="<?php echo $current_page == 'sidebar-style-2.html' ? 'active' : ''; ?>">
                                <a href="sidebar-style-2.html">
                                    <span class="sub-item">Kiểu Thanh Bên 2</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'icon-menu.html' ? 'active' : ''; ?>">
                                <a href="icon-menu.html">
                                    <span class="sub-item">Thực Đơn Biểu Tượng</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?php echo in_array($current_page, ['forms/forms.html']) ? 'active' : ''; ?>">
                    <a
                        data-bs-toggle="collapse"
                        href="#forms"
                        class="<?php echo in_array($current_page, ['forms/forms.html']) ? '' : 'collapsed'; ?>"
                        aria-expanded="<?php echo in_array($current_page, ['forms/forms.html']) ? 'true' : 'false'; ?>"
                    >
                        <i class="fas fa-pen-square"></i>
                        <p>Biểu Mẫu</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo in_array($current_page, ['forms/forms.html']) ? 'show' : ''; ?>" id="forms">
                        <ul class="nav nav-collapse">
                            <li class="<?php echo $current_page == 'forms/forms.html' ? 'active' : ''; ?>">
                                <a href="forms/forms.html">
                                    <span class="sub-item">Biểu Mẫu Cơ Bản</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?php echo in_array($current_page, ['tables/tables.html', 'tables/datatables.html']) ? 'active' : ''; ?>">
                    <a
                        data-bs-toggle="collapse"
                        href="#tables"
                        class="<?php echo in_array($current_page, ['tables/tables.html', 'tables/datatables.html']) ? '' : 'collapsed'; ?>"
                        aria-expanded="<?php echo in_array($current_page, ['tables/tables.html', 'tables/datatables.html']) ? 'true' : 'false'; ?>"
                    >
                        <i class="fas fa-table"></i>
                        <p>Bảng</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo in_array($current_page, ['tables/tables.html', 'tables/datatables.html']) ? 'show' : ''; ?>" id="tables">
                        <ul class="nav nav-collapse">
                            <li class="<?php echo $current_page == 'tables/tables.html' ? 'active' : ''; ?>">
                                <a href="tables/tables.html">
                                    <span class="sub-item">Bảng Cơ Bản</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'tables/datatables.html' ? 'active' : ''; ?>">
                                <a href="tables/datatables.html">
                                    <span class="sub-item">Bảng Dữ Liệu</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?php echo in_array($current_page, ['maps/googlemaps.html', 'maps/jsvectormap.html']) ? 'active' : ''; ?>">
                    <a
                        data-bs-toggle="collapse"
                        href="#maps"
                        class="<?php echo in_array($current_page, ['maps/googlemaps.html', 'maps/jsvectormap.html']) ? '' : 'collapsed'; ?>"
                        aria-expanded="<?php echo in_array($current_page, ['maps/googlemaps.html', 'maps/jsvectormap.html']) ? 'true' : 'false'; ?>"
                    >
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Bản Đồ</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo in_array($current_page, ['maps/googlemaps.html', 'maps/jsvectormap.html']) ? 'show' : ''; ?>" id="maps">
                        <ul class="nav nav-collapse">
                            <li class="<?php echo $current_page == 'maps/googlemaps.html' ? 'active' : ''; ?>">
                                <a href="maps/googlemaps.html">
                                    <span class="sub-item">Bản Đồ Google</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'maps/jsvectormap.html' ? 'active' : ''; ?>">
                                <a href="maps/jsvectormap.html">
                                    <span class="sub-item">Bản Đồ Jsvector</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?php echo in_array($current_page, ['charts/charts.html', 'charts/sparkline.html']) ? 'active' : ''; ?>">
                    <a
                        data-bs-toggle="collapse"
                        href="#charts"
                        class="<?php echo in_array($current_page, ['charts/charts.html', 'charts/sparkline.html']) ? '' : 'collapsed'; ?>"
                        aria-expanded="<?php echo in_array($current_page, ['charts/charts.html', 'charts/sparkline.html']) ? 'true' : 'false'; ?>"
                    >
                        <i class="far fa-chart-bar"></i>
                        <p>Biểu Đồ</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo in_array($current_page, ['charts/charts.html', 'charts/sparkline.html']) ? 'show' : ''; ?>" id="charts">
                        <ul class="nav nav-collapse">
                            <li class="<?php echo $current_page == 'charts/charts.html' ? 'active' : ''; ?>">
                                <a href="charts/charts.html">
                                    <span class="sub-item">Biểu Đồ Js</span>
                                </a>
                            </li>
                            <li class="<?php echo $current_page == 'charts/sparkline.html' ? 'active' : ''; ?>">
                                <a href="charts/sparkline.html">
                                    <span class="sub-item">Biểu Đồ Sparkline</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?php echo $current_page == 'widgets.html' ? 'active' : ''; ?>">
                    <a href="widgets.html">
                        <i class="fas fa-desktop"></i>
                        <p>Tiện Ích</p>
                        <span class="badge badge-success">4</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $current_page == 'documentation/index.php' ? 'active' : ''; ?>">
                    <a href="documentation/index.php">
                        <i class="fas fa-file"></i>
                        <p>Tài Liệu</p>
                        <span class="badge badge-secondary">1</span>
                    </a>
                </li>
                <li class="nav-item <?php echo in_array($current_page, ['submenu_level1.html', 'submenu_level2.html']) ? 'active' : ''; ?>">
                    <a
                        data-bs-toggle="collapse"
                        href="#submenu"
                        class="<?php echo in_array($current_page, ['submenu_level1.html', 'submenu_level2.html']) ? '' : 'collapsed'; ?>"
                        aria-expanded="<?php echo in_array($current_page, ['submenu_level1.html', 'submenu_level2.html']) ? 'true' : 'false'; ?>"
                    >
                        <i class="fas fa-bars"></i>
                        <p>Cấp Độ Thực Đơn</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo in_array($current_page, ['submenu_level1.html', 'submenu_level2.html']) ? 'show' : ''; ?>" id="submenu">
                        <ul class="nav nav-collapse">
                            <li>
                                <a data-bs-toggle="collapse" href="#subnav1">
                                    <span class="sub-item">Cấp 1</span>
                                    <span class="caret"></span>
                                </a>
                                <div class="collapse" id="subnav1">
                                    <ul class="nav nav-collapse subnav">
                                        <li>
                                            <a href="#">
                                                <span class="sub-item">Cấp 2</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <span class="sub-item">Cấp 2</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a data-bs-toggle="collapse" href="#subnav2">
                                    <span class="sub-item">Cấp 1</span>
                                    <span class="caret"></span>
                                </a>
                                <div class="collapse" id="subnav2">
                                    <ul class="nav nav-collapse subnav">
                                        <li>
                                            <a href="#">
                                                <span class="sub-item">Cấp 2</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="#">
                                    <span class="sub-item">Cấp 1</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>