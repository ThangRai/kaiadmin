<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'database/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lấy danh sách danh mục với bộ lọc
$where = [];
$params = [];
$search_name = $_GET['search_name'] ?? '';
$is_visible = $_GET['is_visible'] ?? '';

if ($search_name) {
    $where[] = "name LIKE ?";
    $params[] = "%$search_name%";
}
if ($is_visible !== '') {
    $where[] = "is_visible = ?";
    $params[] = (int)$is_visible;
}

// Lấy danh mục cha
$sql_parent = "SELECT * FROM menu_items WHERE parent_id IS NULL";
if ($where) {
    $sql_parent .= " AND " . implode(" AND ", $where);
}
$sql_parent .= " ORDER BY position ASC";
$stmt_parent = $pdo->prepare($sql_parent);
$stmt_parent->execute($params);
$parent_items = $stmt_parent->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục con và tổ chức danh sách phân cấp
$menu_items = [];
foreach ($parent_items as $parent) {
    $menu_items[] = $parent;
    // Lấy danh mục con
    $sql_child = "SELECT * FROM menu_items WHERE parent_id = ?";
    if ($where) {
        $sql_child .= " AND " . implode(" AND ", $where);
    }
    $sql_child .= " ORDER BY position ASC";
    $stmt_child = $pdo->prepare($sql_child);
    $stmt_child->execute([$parent['id']]);
    $child_items = $stmt_child->fetchAll(PDO::FETCH_ASSOC);
    foreach ($child_items as $child) {
        $menu_items[] = $child;
    }
}

// Lấy danh sách danh mục cha cho modal
$stmt = $pdo->query("SELECT id, name FROM menu_items WHERE parent_id IS NULL AND is_section = 0 ORDER BY position ASC");
$modal_parent_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý thêm danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu'])) {
    $name = $_POST['name'];
    $icon = $_POST['icon'] ?? null;
    $url = $_POST['url'] ?? null;
    $badge = $_POST['badge'] ?? null;
    $parent_id = $_POST['parent_id'] ?: null;
    $position = $_POST['position'] ?? 0;
    $is_section = isset($_POST['is_section']) ? 1 : 0;
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO menu_items (name, icon, url, badge, parent_id, position, is_section, is_visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$name, $icon, $url, $badge, $parent_id, $position, $is_section, $is_visible]);

    if ($result) {
        $_SESSION['toast_message'] = 'Thêm danh mục thành công!';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_message'] = 'Thêm danh mục thất bại.';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: manage_menu.php");
    exit;
}

// Xử lý sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_menu'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $icon = $_POST['icon'] ?? null;
    $url = $_POST['url'] ?? null;
    $badge = $_POST['badge'] ?? null;
    $parent_id = $_POST['parent_id'] ?: null;
    $position = $_POST['position'] ?? 0;
    $is_section = isset($_POST['is_section']) ? 1 : 0;
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, icon = ?, url = ?, badge = ?, parent_id = ?, position = ?, is_section = ?, is_visible = ? WHERE id = ?");
    $result = $stmt->execute([$name, $icon, $url, $badge, $parent_id, $position, $is_section, $is_visible, $id]);

    if ($result) {
        $_SESSION['toast_message'] = 'Cập nhật danh mục thành công!';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_message'] = 'Cập nhật danh mục thất bại.';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: manage_menu.php");
    exit;
}

// Xử lý xóa danh mục
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        $_SESSION['toast_message'] = 'Xóa danh mục thành công!';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_message'] = 'Xóa danh mục thất bại.';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: manage_menu.php");
    exit;
}

// Xử lý bật/tắt hiển thị (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_visible'])) {
    $id = $_POST['id'];
    $is_visible = $_POST['is_visible'];

    $stmt = $pdo->prepare("UPDATE menu_items SET is_visible = ? WHERE id = ?");
    $result = $stmt->execute([(int)$is_visible, $id]);

    echo json_encode(['success' => $result]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Menu - Kaiadmin</title>
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
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <!-- iZitoast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>

        /* Style select số lượng hiển thị */
        .dataTables_length select {
            border-radius: 4px;
            padding: 5px;
            border: 1px solid #d1d3e2;
            background-color: #fff;
            color: #333;
        }
        .dataTables_length label {
            color: #333;
            font-size: 0.9rem;
        }
        /* Style danh mục con */
        .child-menu {
            padding-left: 20px;
        }
        /* Style số tiền tố */
        .prefix-parent, .prefix-child {
            display: inline-block;
            color: #fff;
            padding: 2px 8px;
            border-radius: 12px;
            margin-right: 5px;
            font-size: 0.9rem;
        }
        .prefix-parent {
            background-color: #dc3545; /* Màu đỏ */
        }
        .prefix-child {
            background-color: #28a745; /* Màu xanh lá */
        }
        /* Style icon picker */
        .icon-picker {
            display: none;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: #fff;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            position: absolute;
            z-index: 1000;
            width: 100%;
        }
        .icon-picker .icon-search {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .icon-picker .icon-item {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            cursor: pointer;
            border-radius: 4px;
            margin: 5px;
        }
        .icon-picker .icon-item:hover {
            background: #f8f9fa;
        }
        .icon-picker .icon-item.selected {
            background: #007bff;
            color: #fff;
        }
        .icon-preview {
            margin-left: 10px;
            font-size: 1.2rem;
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .icon-picker {
                position: static;
                width: 100%;
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
                        <h3 class="fw-bold mb-3">Quản lý Menu</h3>
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
                                <a href="#">Quản lý Menu</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h4 class="card-title">Danh sách danh mục</h4>
                                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addMenuModal">
                                            <i class="fas fa-plus"></i> Thêm mới
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Bộ lọc -->
                                    <form class="filter-form mb-4" method="GET">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label>Tìm kiếm tên</label>
                                                    <input type="text" name="search_name" class="form-control" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="Nhập tên danh mục">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Trạng thái</label>
                                                    <select name="is_visible" class="form-control">
                                                        <option value="">Tất cả</option>
                                                        <option value="1" <?php echo $is_visible === '1' ? 'selected' : ''; ?>>Hiển thị</option>
                                                        <option value="0" <?php echo $is_visible === '0' ? 'selected' : ''; ?>>Ẩn</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Lọc</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- Danh sách -->
                                    <div class="table-responsive">
                                        <table id="menuTable" class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>STT</th>
                                                    <th>Tiêu đề</th>
                                                    <th>Hiển thị</th>
                                                    <th>Hành động</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $stt = 1; ?>
                                                <?php foreach ($menu_items as $item): ?>
                                                    <tr <?php echo $item['parent_id'] ? 'class="child-menu"' : ''; ?>>
                                                        <td><?php echo $item['id']; ?></td>
                                                        <td><?php echo $stt++; ?></td>
                                                        <td>
                                                            <?php
                                                            if ($item['parent_id']) {
                                                                echo '<span class="prefix-child">2</span>' . htmlspecialchars($item['name']);
                                                            } else {
                                                                echo '<span class="prefix-parent">1</span>' . htmlspecialchars($item['name']);
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <i class="fas toggle-visibility <?php echo $item['is_visible'] ? 'fa-eye text-success' : 'fa-eye-slash text-danger'; ?>" 
                                                               data-id="<?php echo $item['id']; ?>" 
                                                               data-visible="<?php echo $item['is_visible']; ?>"></i>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-warning edit-menu" data-id="<?php echo $item['id']; ?>" data-bs-toggle="modal" data-bs-target="#editMenuModal">
                                                                <i class="fas fa-edit"></i> Sửa
                                                            </button>
                                                            <a href="manage_menu.php?delete_id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                                <i class="fas fa-trash"></i> Xoá
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm danh mục -->
    <div class="modal fade" id="addMenuModal" tabindex="-1" aria-labelledby="addMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMenuModalLabel">Thêm danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tên danh mục</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Icon</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-primary select-icon">Chọn icon</button>
                                <span class="icon-preview"></span>
                                <input type="hidden" name="icon" id="add_icon">
                            </div>
                            <div class="icon-picker" id="add_icon_picker">
                                <input type="text" class="icon-search" placeholder="Tìm icon...">
                                <div class="icon-list"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>URL</label>
                            <input type="text" name="url" class="form-control" placeholder="index.php">
                        </div>
                        <div class="form-group">
                            <label>Badge</label>
                            <input type="text" name="badge" class="form-control" placeholder='<span class="badge badge-success">4</span>'>
                        </div>
                        <div class="form-group">
                            <label>Danh mục cha</label>
                            <select name="parent_id" class="form-control">
                                <option value="">Không có</option>
                                <?php foreach ($modal_parent_items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Thứ tự</label>
                            <input type="number" name="position" class="form-control" value="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_section" class="form-check-input"> Là section
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_visible" class="form-check-input" checked> Hiển thị
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="add_menu" class="btn btn-primary">Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa danh mục -->
    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMenuModalLabel">Sửa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Tên danh mục</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Icon</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-primary select-icon">Chọn icon</button>
                                <span class="icon-preview"></span>
                                <input type="hidden" name="icon" id="edit_icon">
                            </div>
                            <div class="icon-picker" id="edit_icon_picker">
                                <input type="text" class="icon-search" placeholder="Tìm icon...">
                                <div class="icon-list"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>URL</label>
                            <input type="text" name="url" id="edit_url" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Badge</label>
                            <input type="text" name="badge" id="edit_badge" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Danh mục cha</label>
                            <select name="parent_id" id="edit_parent_id" class="form-control">
                                <option value="">Không có</option>
                                <?php foreach ($modal_parent_items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Thứ tự</label>
                            <input type="number" name="position" id="edit_position" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_section" id="edit_is_section" class="form-check-input"> Là section
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_visible" id="edit_is_visible" class="form-check-input"> Hiển thị
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="edit_menu" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>
    <script src="assets/js/setting-demo.js"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo DataTables
            $('#menuTable').DataTable({
                order: [[1, 'asc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                },
                searching: false,
                lengthChange: true,
                lengthMenu: [10, 25, 50, 100]
            });

            // Danh sách icon Font Awesome Solid (mở rộng để demo tìm kiếm)
            const icons = [
                "fas fa-address-book",
                "fas fa-address-card",
                "fas fa-adjust",
                "fas fa-air-freshener",
                "fas fa-align-center",
                "fas fa-align-justify",
                "fas fa-align-left",
                "fas fa-align-right",
                "fas fa-allergies",
                "fas fa-ambulance",
                "fas fa-american-sign-language-interpreting",
                "fas fa-anchor",
                "fas fa-angle-double-down",
                "fas fa-angle-double-left",
                "fas fa-angle-double-right",
                "fas fa-angle-double-up",
                "fas fa-angle-down",
                "fas fa-angle-left",
                "fas fa-angle-right",
                "fas fa-angle-up",
                "fas fa-angry",
                "fas fa-apple-alt",
                "fas fa-archive",
                "fas fa-archway",
                "fas fa-arrow-alt-circle-down",
                "fas fa-arrow-alt-circle-left",
                "fas fa-arrow-alt-circle-right",
                "fas fa-arrow-alt-circle-up",
                "fas fa-arrow-circle-down",
                "fas fa-arrow-circle-left",
                "fas fa-arrow-circle-right",
                "fas fa-arrow-circle-up",
                "fas fa-arrow-down",
                "fas fa-arrow-left",
                "fas fa-arrow-right",
                "fas fa-arrow-up",
                "fas fa-arrows-alt",
                "fas fa-arrows-alt-h",
                "fas fa-arrows-alt-v",
                "fas fa-assistive-listening-systems",
                "fas fa-asterisk",
                "fas fa-at",
                "fas fa-atlas",
                "fas fa-atom",
                "fas fa-audio-description",
                "fas fa-award",
                "fas fa-backspace",
                "fas fa-backward",
                "fas fa-balance-scale",
                "fas fa-ban",
                "fas fa-band-aid",
                "fas fa-barcode",
                "fas fa-bars",
                "fas fa-baseball-ball",
                "fas fa-basketball-ball",
                "fas fa-bath",
                "fas fa-battery-empty",
                "fas fa-battery-full",
                "fas fa-battery-half",
                "fas fa-battery-quarter",
                "fas fa-battery-three-quarters",
                "fas fa-bed",
                "fas fa-beer",
                "fas fa-bell",
                "fas fa-bell-slash",
                "fas fa-bezier-curve",
                "fas fa-bicycle",
                "fas fa-binoculars",
                "fas fa-birthday-cake",
                "fas fa-blender",
                "fas fa-blind",
                "fas fa-bold",
                "fas fa-bolt",
                "fas fa-bomb",
                "fas fa-bone",
                "fas fa-bong",
                "fas fa-book",
                "fas fa-book-open",
                "fas fa-book-reader",
                "fas fa-bookmark",
                "fas fa-bowling-ball",
                "fas fa-box",
                "fas fa-box-open",
                "fas fa-boxes",
                "fas fa-braille",
                "fas fa-brain",
                "fas fa-briefcase",
                "fas fa-briefcase-medical",
                "fas fa-broadcast-tower",
                "fas fa-broom",
                "fas fa-brush",
                "fas fa-bug",
                "fas fa-building",
                "fas fa-bullhorn",
                "fas fa-bullseye",
                "fas fa-burn",
                "fas fa-bus",
                "fas fa-bus-alt",
                "fas fa-calculator",
                "fas fa-calendar",
                "fas fa-calendar-alt",
                "fas fa-calendar-check",
                "fas fa-calendar-minus",
                "fas fa-calendar-plus",
                "fas fa-calendar-times",
                "fas fa-camera",
                "fas fa-camera-retro",
                "fas fa-cannabis",
                "fas fa-capsules",
                "fas fa-car",
                "fas fa-car-alt",
                "fas fa-car-battery",
                "fas fa-car-crash",
                "fas fa-car-side",
                "fas fa-caret-down",
                "fas fa-caret-left",
                "fas fa-caret-right",
                "fas fa-caret-square-down",
                "fas fa-caret-square-left",
                "fas fa-caret-square-right",
                "fas fa-caret-square-up",
                "fas fa-caret-up",
                "fas fa-cart-arrow-down",
                "fas fa-cart-plus",
                "fas fa-certificate",
                "fas fa-chalkboard",
                "fas fa-chalkboard-teacher",
                "fas fa-charging-station",
                "fas fa-chart-area",
                "fas fa-chart-bar",
                "fas fa-chart-line",
                "fas fa-chart-pie",
                "fas fa-check",
                "fas fa-check-circle",
                "fas fa-check-double",
                "fas fa-check-square",
                "fas fa-chess",
                "fas fa-chess-bishop",
                "fas fa-chess-board",
                "fas fa-chess-king",
                "fas fa-chess-knight",
                "fas fa-chess-pawn",
                "fas fa-chess-queen",
                "fas fa-chess-rook",
                "fas fa-chevron-circle-down",
                "fas fa-chevron-circle-left",
                "fas fa-chevron-circle-right",
                "fas fa-chevron-circle-up",
                "fas fa-chevron-down",
                "fas fa-chevron-left",
                "fas fa-chevron-right",
                "fas fa-chevron-up",
                "fas fa-child",
                "fas fa-church",
                "fas fa-circle",
                "fas fa-circle-notch",
                "fas fa-clipboard",
                "fas fa-clipboard-check",
                "fas fa-clipboard-list",
                "fas fa-clock",
                "fas fa-clone",
                "fas fa-closed-captioning",
                "fas fa-cloud",
                "fas fa-cloud-download-alt",
                "fas fa-cloud-upload-alt",
                "fas fa-cocktail",
                "fas fa-code",
                "fas fa-code-branch",
                "fas fa-coffee",
                "fas fa-cog",
                "fas fa-cogs",
                "fas fa-coins",
                "fas fa-columns",
                "fas fa-comment",
                "fas fa-comment-alt",
                "fas fa-comment-dots",
                "fas fa-comment-slash",
                "fas fa-comments",
                "fas fa-compact-disc",
                "fas fa-compass",
                "fas fa-compress",
                "fas fa-concierge-bell",
                "fas fa-cookie",
                "fas fa-cookie-bite",
                "fas fa-copy",
                "fas fa-copyright",
                "fas fa-couch",
                "fas fa-credit-card",
                "fas fa-crop",
                "fas fa-crop-alt",
                "fas fa-crosshairs",
                "fas fa-crow",
                "fas fa-crown",
                "fas fa-cube",
                "fas fa-cubes",
                "fas fa-cut",
                "fas fa-database",
                "fas fa-deaf",
                "fas fa-desktop",
                "fas fa-diagnoses",
                "fas fa-dice",
                "fas fa-dice-five",
                "fas fa-dice-four",
                "fas fa-dice-one",
                "fas fa-dice-six",
                "fas fa-dice-three",
                "fas fa-dice-two",
                "fas fa-digital-tachograph",
                "fas fa-directions",
                "fas fa-divide",
                "fas fa-dizzy",
                "fas fa-dna",
                "fas fa-dollar-sign",
                "fas fa-dolly",
                "fas fa-dolly-flatbed",
                "fas fa-donate",
                "fas fa-door-closed",
                "fas fa-door-open",
                "fas fa-dot-circle",
                "fas fa-dove",
                "fas fa-download",
                "fas fa-drafting-compass",
                "fas fa-draw-polygon",
                "fas fa-drum",
                "fas fa-drum-steelpan",
                "fas fa-dumbbell",
                "fas fa-edit",
                "fas fa-eject",
                "fas fa-ellipsis-h",
                "fas fa-ellipsis-v",
                "fas fa-envelope",
                "fas fa-envelope-open",
                "fas fa-envelope-square",
                "fas fa-equals",
                "fas fa-eraser",
                "fas fa-euro-sign",
                "fas fa-exchange-alt",
                "fas fa-exclamation",
                "fas fa-exclamation-circle",
                "fas fa-exclamation-triangle",
                "fas fa-expand",
                "fas fa-expand-arrows-alt",
                "fas fa-external-link-alt",
                "fas fa-external-link-square-alt",
                "fas fa-eye",
                "fas fa-eye-dropper",
                "fas fa-eye-slash",
                "fas fa-fast-backward",
                "fas fa-fast-forward",
                "fas fa-fax",
                "fas fa-feather",
                "fas fa-feather-alt",
                "fas fa-female",
                "fas fa-fighter-jet",
                "fas fa-file",
                "fas fa-file-alt",
                "fas fa-file-archive",
                "fas fa-file-audio",
                "fas fa-file-code",
                "fas fa-file-contract",
                "fas fa-file-download",
                "fas fa-file-excel",
                "fas fa-file-export",
                "fas fa-file-image",
                "fas fa-file-import",
                "fas fa-file-invoice",
                "fas fa-file-invoice-dollar",
                "fas fa-file-medical",
                "fas fa-file-medical-alt",
                "fas fa-file-pdf",
                "fas fa-file-powerpoint",
                "fas fa-file-prescription",
                "fas fa-file-signature",
                "fas fa-file-upload",
                "fas fa-file-video",
                "fas fa-file-word",
                "fas fa-fill",
                "fas fa-fill-drip",
                "fas fa-film",
                "fas fa-filter",
                "fas fa-fingerprint",
                "fas fa-fire",
                "fas fa-fire-extinguisher",
                "fas fa-first-aid",
                "fas fa-fish",
                "fas fa-flag",
                "fas fa-flag-checkered",
                "fas fa-flask",
                "fas fa-flushed",
                "fas fa-folder",
                "fas fa-folder-open",
                "fas fa-font",
                "fas fa-football-ball",
                "fas fa-forward",
                "fas fa-frog",
                "fas fa-frown",
                "fas fa-frown-open",
                "fas fa-futbol",
                "fas fa-gamepad",
                "fas fa-gas-pump",
                "fas fa-gavel",
                "fas fa-gem",
                "fas fa-genderless",
                "fas fa-gift",
                "fas fa-glass-martini",
                "fas fa-glass-martini-alt",
                "fas fa-glasses",
                "fas fa-globe",
                "fas fa-globe-africa",
                "fas fa-globe-americas",
                "fas fa-globe-asia",
                "fas fa-golf-ball",
                "fas fa-graduation-cap",
                "fas fa-greater-than",
                "fas fa-greater-than-equal",
                "fas fa-grimace",
                "fas fa-grin",
                "fas fa-grin-alt",
                "fas fa-grin-beam",
                "fas fa-grin-beam-sweat",
                "fas fa-grin-hearts",
                "fas fa-grin-squint",
                "fas fa-grin-squint-tears",
                "fas fa-grin-stars",
                "fas fa-grin-tears",
                "fas fa-grin-tongue",
                "fas fa-grin-tongue-squint",
                "fas fa-grin-tongue-wink",
                "fas fa-grin-wink",
                "fas fa-grip-horizontal",
                "fas fa-grip-vertical",
                "fas fa-h-square",
                "fas fa-hand-holding",
                "fas fa-hand-holding-heart",
                "fas fa-hand-holding-usd",
                "fas fa-hand-lizard",
                "fas fa-hand-paper",
                "fas fa-hand-peace",
                "fas fa-hand-point-down",
                "fas fa-hand-point-left",
                "fas fa-hand-point-right",
                "fas fa-hand-point-up",
                "fas fa-hand-pointer",
                "fas fa-hand-rock",
                "fas fa-hand-scissors",
                "fas fa-hand-spock",
                "fas fa-hands",
                "fas fa-hands-helping",
                "fas fa-handshake",
                "fas fa-hashtag",
                "fas fa-hdd",
                "fas fa-heading",
                "fas fa-headphones",
                "fas fa-headphones-alt",
                "fas fa-headset",
                "fas fa-heart",
                "fas fa-heartbeat",
                "fas fa-helicopter",
                "fas fa-highlighter",
                "fas fa-history",
                "fas fa-hockey-puck",
                "fas fa-home",
                "fas fa-hospital",
                "fas fa-hospital-alt",
                "fas fa-hospital-symbol",
                "fas fa-hot-tub",
                "fas fa-hotel",
                "fas fa-hourglass",
                "fas fa-hourglass-end",
                "fas fa-hourglass-half",
                "fas fa-hourglass-start",
                "fas fa-i-cursor",
                "fas fa-id-badge",
                "fas fa-id-card",
                "fas fa-id-card-alt",
                "fas fa-image",
                "fas fa-images",
                "fas fa-inbox",
                "fas fa-indent",
                "fas fa-industry",
                "fas fa-infinity",
                "fas fa-info",
                "fas fa-info-circle",
                "fas fa-italic",
                "fas fa-joint",
                "fas fa-key",
                "fas fa-keyboard",
                "fas fa-kiss",
                "fas fa-kiss-beam",
                "fas fa-kiss-wink-heart",
                "fas fa-kiwi-bird",
                "fas fa-language",
                "fas fa-laptop",
                "fas fa-laptop-code",
                "fas fa-laugh",
                "fas fa-laugh-beam",
                "fas fa-laugh-squint",
                "fas fa-laugh-wink",
                "fas fa-layer-group",
                "fas fa-leaf",
                "fas fa-lemon",
                "fas fa-less-than",
                "fas fa-less-than-equal",
                "fas fa-level-down-alt",
                "fas fa-level-up-alt",
                "fas fa-life-ring",
                "fas fa-lightbulb",
                "fas fa-link",
                "fas fa-lira-sign",
                "fas fa-list",
                "fas fa-list-alt",
                "fas fa-list-ol",
                "fas fa-list-ul",
                "fas fa-location-arrow",
                "fas fa-lock",
                "fas fa-lock-open",
                "fas fa-long-arrow-alt-down",
                "fas fa-long-arrow-alt-left",
                "fas fa-long-arrow-alt-right",
                "fas fa-long-arrow-alt-up",
                "fas fa-low-vision",
                "fas fa-luggage-cart",
                "fas fa-magic",
                "fas fa-magnet",
                "fas fa-male",
                "fas fa-map",
                "fas fa-map-marked",
                "fas fa-map-marked-alt",
                "fas fa-map-marker",
                "fas fa-map-marker-alt",
                "fas fa-map-pin",
                "fas fa-map-signs",
                "fas fa-marker",
                "fas fa-mars",
                "fas fa-mars-double",
                "fas fa-mars-stroke",
                "fas fa-mars-stroke-h",
                "fas fa-mars-stroke-v",
                "fas fa-medal",
                "fas fa-medkit",
                "fas fa-meh",
                "fas fa-meh-blank",
                "fas fa-meh-rolling-eyes",
                "fas fa-memory",
                "fas fa-mercury",
                "fas fa-microchip",
                "fas fa-microphone",
                "fas fa-microphone-alt",
                "fas fa-microphone-alt-slash",
                "fas fa-microphone-slash",
                "fas fa-microscope",
                "fas fa-minus",
                "fas fa-minus-circle",
                "fas fa-minus-square",
                "fas fa-mobile",
                "fas fa-mobile-alt",
                "fas fa-money-bill",
                "fas fa-money-bill-alt",
                "fas fa-money-bill-wave",
                "fas fa-money-bill-wave-alt",
                "fas fa-money-check",
                "fas fa-money-check-alt",
                "fas fa-monument",
                "fas fa-moon",
                "fas fa-mortar-pestle",
                "fas fa-motorcycle",
                "fas fa-mouse-pointer",
                "fas fa-music",
                "fas fa-neuter",
                "fas fa-newspaper",
                "fas fa-not-equal",
                "fas fa-notes-medical",
                "fas fa-object-group",
                "fas fa-object-ungroup",
                "fas fa-oil-can",
                "fas fa-outdent",
                "fas fa-paint-brush",
                "fas fa-paint-roller",
                "fas fa-palette",
                "fas fa-pallet",
                "fas fa-paper-plane",
                "fas fa-paperclip",
                "fas fa-parachute-box",
                "fas fa-paragraph",
                "fas fa-parking",
                "fas fa-passport",
                "fas fa-paste",
                "fas fa-pause",
                "fas fa-pause-circle",
                "fas fa-paw",
                "fas fa-pen",
                "fas fa-pen-alt",
                "fas fa-pen-fancy",
                "fas fa-pen-nib",
                "fas fa-pen-square",
                "fas fa-pencil-alt",
                "fas fa-pencil-ruler",
                "fas fa-people-carry",
                "fas fa-percent",
                "fas fa-percentage",
                "fas fa-phone",
                "fas fa-phone-slash",
                "fas fa-phone-square",
                "fas fa-phone-volume",
                "fas fa-piggy-bank",
                "fas fa-pills",
                "fas fa-plane",
                "fas fa-plane-arrival",
                "fas fa-plane-departure",
                "fas fa-play",
                "fas fa-play-circle",
                "fas fa-plug",
                "fas fa-plus",
                "fas fa-plus-circle",
                "fas fa-plus-square",
                "fas fa-podcast",
                "fas fa-poo",
                "fas fa-poop",
                "fas fa-portrait",
                "fas fa-pound-sign",
                "fas fa-power-off",
                "fas fa-prescription",
                "fas fa-prescription-bottle",
                "fas fa-prescription-bottle-alt",
                "fas fa-print",
                "fas fa-procedures",
                "fas fa-project-diagram",
                "fas fa-puzzle-piece",
                "fas fa-qrcode",
                "fas fa-question",
                "fas fa-question-circle",
                "fas fa-quidditch",
                "fas fa-quote-left",
                "fas fa-quote-right",
                "fas fa-random",
                "fas fa-receipt",
                "fas fa-recycle",
                "fas fa-redo",
                "fas fa-redo-alt",
                "fas fa-registered",
                "fas fa-reply",
                "fas fa-reply-all",
                "fas fa-retweet",
                "fas fa-ribbon",
                "fas fa-road",
                "fas fa-robot",
                "fas fa-rocket",
                "fas fa-route",
                "fas fa-rss",
                "fas fa-rss-square",
                "fas fa-ruble-sign",
                "fas fa-ruler",
                "fas fa-ruler-combined",
                "fas fa-ruler-horizontal",
                "fas fa-ruler-vertical",
                "fas fa-rupee-sign",
                "fas fa-sad-cry",
                "fas fa-sad-tear",
                "fas fa-save",
                "fas fa-school",
                "fas fa-screwdriver",
                "fas fa-search",
                "fas fa-search-minus",
                "fas fa-search-plus",
                "fas fa-seedling",
                "fas fa-server",
                "fas fa-shapes",
                "fas fa-share",
                "fas fa-share-alt",
                "fas fa-share-alt-square",
                "fas fa-share-square",
                "fas fa-shekel-sign",
                "fas fa-shield-alt",
                "fas fa-ship",
                "fas fa-shipping-fast",
                "fas fa-shoe-prints",
                "fas fa-shopping-bag",
                "fas fa-shopping-basket",
                "fas fa-shopping-cart",
                "fas fa-shower",
                "fas fa-shuttle-van",
                "fas fa-sign",
                "fas fa-sign-in-alt",
                "fas fa-sign-language",
                "fas fa-sign-out-alt",
                "fas fa-signal",
                "fas fa-signature",
                "fas fa-sitemap",
                "fas fa-skull",
                "fas fa-sliders-h",
                "fas fa-smile",
                "fas fa-smile-beam",
                "fas fa-smile-wink",
                "fas fa-smoking",
                "fas fa-smoking-ban",
                "fas fa-snowflake",
                "fas fa-solar-panel",
                "fas fa-sort",
                "fas fa-sort-alpha-down",
                "fas fa-sort-alpha-up",
                "fas fa-sort-amount-down",
                "fas fa-sort-amount-up",
                "fas fa-sort-down",
                "fas fa-sort-numeric-down",
                "fas fa-sort-numeric-up",
                "fas fa-sort-up",
                "fas fa-spa",
                "fas fa-space-shuttle",
                "fas fa-spinner",
                "fas fa-splotch",
                "fas fa-spray-can",
                "fas fa-square",
                "fas fa-square-full",
                "fas fa-stamp",
                "fas fa-star",
                "fas fa-star-half",
                "fas fa-star-half-alt",
                "fas fa-star-of-life",
                "fas fa-step-backward",
                "fas fa-step-forward",
                "fas fa-stethoscope",
                "fas fa-sticky-note",
                "fas fa-stop",
                "fas fa-stop-circle",
                "fas fa-stopwatch",
                "fas fa-store",
                "fas fa-store-alt",
                "fas fa-stream",
                "fas fa-street-view",
                "fas fa-strikethrough",
                "fas fa-stroopwafel",
                "fas fa-subscript",
                "fas fa-subway",
                "fas fa-suitcase",
                "fas fa-suitcase-rolling",
                "fas fa-sun",
                "fas fa-superscript",
                "fas fa-surprise",
                "fas fa-swatchbook",
                "fas fa-swimmer",
                "fas fa-swimming-pool",
                "fas fa-sync",
                "fas fa-sync-alt",
                "fas fa-syringe",
                "fas fa-table",
                "fas fa-table-tennis",
                "fas fa-tablet",
                "fas fa-tablet-alt",
                "fas fa-tablets",
                "fas fa-tachometer-alt",
                "fas fa-tag",
                "fas fa-tags",
                "fas fa-tape",
                "fas fa-tasks",
                "fas fa-taxi",
                "fas fa-teeth",
                "fas fa-teeth-open",
                "fas fa-terminal",
                "fas fa-text-height",
                "fas fa-text-width",
                "fas fa-th",
                "fas fa-th-large",
                "fas fa-th-list",
                "fas fa-theater-masks",
                "fas fa-thermometer",
                "fas fa-thermometer-empty",
                "fas fa-thermometer-full",
                "fas fa-thermometer-half",
                "fas fa-thermometer-quarter",
                "fas fa-thermometer-three-quarters",
                "fas fa-thumbs-down",
                "fas fa-thumbs-up",
                "fas fa-thumbtack",
                "fas fa-ticket-alt",
                "fas fa-times",
                "fas fa-times-circle",
                "fas fa-tint",
                "fas fa-tint-slash",
                "fas fa-tired",
                "fas fa-toggle-off",
                "fas fa-toggle-on",
                "fas fa-toolbox",
                "fas fa-tooth",
                "fas fa-trademark",
                "fas fa-traffic-light",
                "fas fa-train",
                "fas fa-transgender",
                "fas fa-transgender-alt",
                "fas fa-trash",
                "fas fa-trash-alt",
                "fas fa-tree",
                "fas fa-trophy",
                "fas fa-truck",
                "fas fa-truck-loading",
                "fas fa-truck-monster",
                "fas fa-truck-moving",
                "fas fa-truck-pickup",
                "fas fa-tshirt",
                "fas fa-tty",
                "fas fa-tv",
                "fas fa-umbrella",
                "fas fa-umbrella-beach",
                "fas fa-underline",
                "fas fa-undo",
                "fas fa-undo-alt",
                "fas fa-universal-access",
                "fas fa-university",
                "fas fa-unlink",
                "fas fa-unlock",
                "fas fa-unlock-alt",
                "fas fa-upload",
                "fas fa-user",
                "fas fa-user-alt",
                "fas fa-user-alt-slash",
                "fas fa-user-astronaut",
                "fas fa-user-check",
                "fas fa-user-circle",
                "fas fa-user-clock",
                "fas fa-user-cog",
                "fas fa-user-edit",
                "fas fa-user-friends",
                "fas fa-user-graduate",
                "fas fa-user-lock",
                "fas fa-user-md",
                "fas fa-user-minus",
                "fas fa-user-ninja",
                "fas fa-user-plus",
                "fas fa-user-secret",
                "fas fa-user-shield",
                "fas fa-user-slash",
                "fas fa-user-tag",
                "fas fa-user-tie",
                "fas fa-user-times",
                "fas fa-users",
                "fas fa-users-cog",
                "fas fa-utensil-spoon",
                "fas fa-utensils",
                "fas fa-vector-square",
                "fas fa-venus",
                "fas fa-venus-double",
                "fas fa-venus-mars",
                "fas fa-vial",
                "fas fa-vials",
                "fas fa-video",
                "fas fa-video-slash",
                "fas fa-volleyball-ball",
                "fas fa-volume-down",
                "fas fa-volume-off",
                "fas fa-volume-up",
                "fas fa-walking",
                "fas fa-wallet",
                "fas fa-warehouse",
                "fas fa-weight",
                "fas fa-weight-hanging",
                "fas fa-wheelchair",
                "fas fa-wifi",
                "fas fa-window-close",
                "fas fa-window-maximize",
                "fas fa-window-minimize",
                "fas fa-window-restore",
                "fas fa-wine-glass",
                "fas fa-wine-glass-alt",
                "fas fa-won-sign",
                "fas fa-wrench",
                "fas fa-x-ray",
                "fas fa-yen-sign",
                "far fa-address-book",
                "far fa-address-card",
                "far fa-angry",
                "far fa-arrow-alt-circle-down",
                "far fa-arrow-alt-circle-left",
                "far fa-arrow-alt-circle-right",
                "far fa-arrow-alt-circle-up",
                "far fa-bell",
                "far fa-bell-slash",
                "far fa-bookmark",
                "far fa-building",
                "far fa-calendar",
                "far fa-calendar-alt",
                "far fa-calendar-check",
                "far fa-calendar-minus",
                "far fa-calendar-plus",
                "far fa-calendar-times",
                "far fa-caret-square-down",
                "far fa-caret-square-left",
                "far fa-caret-square-right",
                "far fa-caret-square-up",
                "far fa-chart-bar",
                "far fa-check-circle",
                "far fa-check-square",
                "far fa-circle",
                "far fa-clipboard",
                "far fa-clock",
                "far fa-clone",
                "far fa-closed-captioning",
                "far fa-comment",
                "far fa-comment-alt",
                "far fa-comment-dots",
                "far fa-comments",
                "far fa-compass",
                "far fa-copy",
                "far fa-copyright",
                "far fa-credit-card",
                "far fa-dizzy",
                "far fa-dot-circle",
                "far fa-edit",
                "far fa-envelope",
                "far fa-envelope-open",
                "far fa-eye",
                "far fa-eye-slash",
                "far fa-file",
                "far fa-file-alt",
                "far fa-file-archive",
                "far fa-file-audio",
                "far fa-file-code",
                "far fa-file-excel",
                "far fa-file-image",
                "far fa-file-pdf",
                "far fa-file-powerpoint",
                "far fa-file-video",
                "far fa-file-word",
                "far fa-flag",
                "far fa-flushed",
                "far fa-folder",
                "far fa-folder-open",
                "far fa-frown",
                "far fa-frown-open",
                "far fa-futbol",
                "far fa-gem",
                "far fa-grimace",
                "far fa-grin",
                "far fa-grin-alt",
                "far fa-grin-beam",
                "far fa-grin-beam-sweat",
                "far fa-grin-hearts",
                "far fa-grin-squint",
                "far fa-grin-squint-tears",
                "far fa-grin-stars",
                "far fa-grin-tears",
                "far fa-grin-tongue",
                "far fa-grin-tongue-squint",
                "far fa-grin-tongue-wink",
                "far fa-grin-wink",
                "far fa-hand-lizard",
                "far fa-hand-paper",
                "far fa-hand-peace",
                "far fa-hand-point-down",
                "far fa-hand-point-left",
                "far fa-hand-point-right",
                "far fa-hand-point-up",
                "far fa-hand-pointer",
                "far fa-hand-rock",
                "far fa-hand-scissors",
                "far fa-hand-spock",
                "far fa-handshake",
                "far fa-hdd",
                "far fa-heart",
                "far fa-hospital",
                "far fa-hourglass",
                "far fa-id-badge",
                "far fa-id-card",
                "far fa-image",
                "far fa-images",
                "far fa-keyboard",
                "far fa-kiss",
                "far fa-kiss-beam",
                "far fa-kiss-wink-heart",
                "far fa-laugh",
                "far fa-laugh-beam",
                "far fa-laugh-squint",
                "far fa-laugh-wink",
                "far fa-lemon",
                "far fa-life-ring",
                "far fa-lightbulb",
                "far fa-list-alt",
                "far fa-map",
                "far fa-meh",
                "far fa-meh-blank",
                "far fa-meh-rolling-eyes",
                "far fa-minus-square",
                "far fa-money-bill-alt",
                "far fa-moon",
                "far fa-newspaper",
                "far fa-object-group",
                "far fa-object-ungroup",
                "far fa-paper-plane",
                "far fa-pause-circle",
                "far fa-play-circle",
                "far fa-plus-square",
                "far fa-question-circle",
                "far fa-registered",
                "far fa-sad-cry",
                "far fa-sad-tear",
                "far fa-save",
                "far fa-share-square",
                "far fa-smile",
                "far fa-smile-beam",
                "far fa-smile-wink",
                "far fa-snowflake",
                "far fa-square",
                "far fa-star",
                "far fa-star-half",
                "far fa-sticky-note",
                "far fa-stop-circle",
                "far fa-sun",
                "far fa-surprise",
                "far fa-thumbs-down",
                "far fa-thumbs-up",
                "far fa-times-circle",
                "far fa-tired",
                "far fa-trash-alt",
                "far fa-user",
                "far fa-user-circle",
                "far fa-window-close",
                "far fa-window-maximize",
                "far fa-window-minimize",
                "far fa-window-restore",
                "fab fa-500px",
                "fab fa-accessible-icon",
                "fab fa-accusoft",
                "fab fa-adn",
                "fab fa-adversal",
                "fab fa-affiliatetheme",
                "fab fa-algolia",
                "fab fa-amazon",
                "fab fa-amazon-pay",
                "fab fa-amilia",
                "fab fa-android",
                "fab fa-angellist",
                "fab fa-angrycreative",
                "fab fa-angular",
                "fab fa-app-store",
                "fab fa-app-store-ios",
                "fab fa-apper",
                "fab fa-apple",
                "fab fa-apple-pay",
                "fab fa-asymmetrik",
                "fab fa-audible",
                "fab fa-autoprefixer",
                "fab fa-avianex",
                "fab fa-aviato",
                "fab fa-aws",
                "fab fa-bandcamp",
                "fab fa-behance",
                "fab fa-behance-square",
                "fab fa-bimobject",
                "fab fa-bitbucket",
                "fab fa-bitcoin",
                "fab fa-bity",
                "fab fa-black-tie",
                "fab fa-blackberry",
                "fab fa-blogger",
                "fab fa-blogger-b",
                "fab fa-bluetooth",
                "fab fa-bluetooth-b",
                "fab fa-btc",
                "fab fa-buromobelexperte",
                "fab fa-buysellads",
                "fab fa-cc-amazon-pay",
                "fab fa-cc-amex",
                "fab fa-cc-apple-pay",
                "fab fa-cc-diners-club",
                "fab fa-cc-discover",
                "fab fa-cc-jcb",
                "fab fa-cc-mastercard",
                "fab fa-cc-paypal",
                "fab fa-cc-stripe",
                "fab fa-cc-visa",
                "fab fa-centercode",
                "fab fa-chrome",
                "fab fa-cloudscale",
                "fab fa-cloudsmith",
                "fab fa-cloudversify",
                "fab fa-codepen",
                "fab fa-codiepie",
                "fab fa-connectdevelop",
                "fab fa-contao",
                "fab fa-cpanel",
                "fab fa-creative-commons",
                "fab fa-creative-commons-by",
                "fab fa-creative-commons-nc",
                "fab fa-creative-commons-nc-eu",
                "fab fa-creative-commons-nc-jp",
                "fab fa-creative-commons-nd",
                "fab fa-creative-commons-pd",
                "fab fa-creative-commons-pd-alt",
                "fab fa-creative-commons-remix",
                "fab fa-creative-commons-sa",
                "fab fa-creative-commons-sampling",
                "fab fa-creative-commons-sampling-plus",
                "fab fa-creative-commons-share",
                "fab fa-css3",
                "fab fa-css3-alt",
                "fab fa-cuttlefish",
                "fab fa-d-and-d",
                "fab fa-dashcube",
                "fab fa-delicious",
                "fab fa-deploydog",
                "fab fa-deskpro",
                "fab fa-deviantart",
                "fab fa-digg",
                "fab fa-digital-ocean",
                "fab fa-discord",
                "fab fa-discourse",
                "fab fa-dochub",
                "fab fa-docker",
                "fab fa-draft2digital",
                "fab fa-dribbble",
                "fab fa-dribbble-square",
                "fab fa-dropbox",
                "fab fa-drupal",
                "fab fa-dyalog",
                "fab fa-earlybirds",
                "fab fa-ebay",
                "fab fa-edge",
                "fab fa-elementor",
                "fab fa-ello",
                "fab fa-ember",
                "fab fa-empire",
                "fab fa-envira",
                "fab fa-erlang",
                "fab fa-ethereum",
                "fab fa-etsy",
                "fab fa-expeditedssl",
                "fab fa-facebook",
                "fab fa-facebook-f",
                "fab fa-facebook-messenger",
                "fab fa-facebook-square",
                "fab fa-firefox",
                "fab fa-first-order",
                "fab fa-first-order-alt",
                "fab fa-firstdraft",
                "fab fa-flickr",
                "fab fa-flipboard",
                "fab fa-fly",
                "fab fa-font-awesome",
                "fab fa-font-awesome-alt",
                "fab fa-font-awesome-flag",
                "fab fa-fonticons",
                "fab fa-fonticons-fi",
                "fab fa-fort-awesome",
                "fab fa-fort-awesome-alt",
                "fab fa-forumbee",
                "fab fa-foursquare",
                "fab fa-free-code-camp",
                "fab fa-freebsd",
                "fab fa-fulcrum",
                "fab fa-galactic-republic",
                "fab fa-galactic-senate",
                "fab fa-get-pocket",
                "fab fa-gg",
                "fab fa-gg-circle",
                "fab fa-git",
                "fab fa-git-square",
                "fab fa-github",
                "fab fa-github-alt",
                "fab fa-github-square",
                "fab fa-gitkraken",
                "fab fa-gitlab",
                "fab fa-gitter",
                "fab fa-glide",
                "fab fa-glide-g",
                "fab fa-gofore",
                "fab fa-goodreads",
                "fab fa-goodreads-g",
                "fab fa-google",
                "fab fa-google-drive",
                "fab fa-google-play",
                "fab fa-google-plus",
                "fab fa-google-plus-g",
                "fab fa-google-plus-square",
                "fab fa-google-wallet",
                "fab fa-gratipay",
                "fab fa-grav",
                "fab fa-gripfire",
                "fab fa-grunt",
                "fab fa-gulp",
                "fab fa-hacker-news",
                "fab fa-hacker-news-square",
                "fab fa-hackerrank",
                "fab fa-hips",
                "fab fa-hire-a-helper",
                "fab fa-hooli",
                "fab fa-hornbill",
                "fab fa-hotjar",
                "fab fa-houzz",
                "fab fa-html5",
                "fab fa-hubspot",
                "fab fa-imdb",
                "fab fa-instagram",
                "fab fa-internet-explorer",
                "fab fa-ioxhost",
                "fab fa-itunes",
                "fab fa-itunes-note",
                "fab fa-java",
                "fab fa-jedi-order",
                "fab fa-jenkins",
                "fab fa-joget",
                "fab fa-joomla",
                "fab fa-js",
                "fab fa-js-square",
                "fab fa-jsfiddle",
                "fab fa-kaggle",
                "fab fa-keybase",
                "fab fa-keycdn",
                "fab fa-kickstarter",
                "fab fa-kickstarter-k",
                "fab fa-korvue",
                "fab fa-laravel",
                "fab fa-lastfm",
                "fab fa-lastfm-square",
                "fab fa-leanpub",
                "fab fa-less",
                "fab fa-line",
                "fab fa-linkedin",
                "fab fa-linkedin-in",
                "fab fa-linode",
                "fab fa-linux",
                "fab fa-lyft",
                "fab fa-magento",
                "fab fa-mailchimp",
                "fab fa-mandalorian",
                "fab fa-markdown",
                "fab fa-mastodon",
                "fab fa-maxcdn",
                "fab fa-medapps",
                "fab fa-medium",
                "fab fa-medium-m",
                "fab fa-medrt",
                "fab fa-meetup",
                "fab fa-megaport",
                "fab fa-microsoft",
                "fab fa-mix",
                "fab fa-mixcloud",
                "fab fa-mizuni",
                "fab fa-modx",
                "fab fa-monero",
                "fab fa-napster",
                "fab fa-neos",
                "fab fa-nimblr",
                "fab fa-nintendo-switch",
                "fab fa-node",
                "fab fa-node-js",
                "fab fa-npm",
                "fab fa-ns8",
                "fab fa-nutritionix",
                "fab fa-odnoklassniki",
                "fab fa-odnoklassniki-square",
                "fab fa-old-republic",
                "fab fa-opencart",
                "fab fa-openid",
                "fab fa-opera",
                "fab fa-optin-monster",
                "fab fa-osi",
                "fab fa-page4",
                "fab fa-pagelines",
                "fab fa-palfed",
                "fab fa-patreon",
                "fab fa-paypal",
                "fab fa-periscope",
                "fab fa-phabricator",
                "fab fa-phoenix-framework",
                "fab fa-phoenix-squadron",
                "fab fa-php",
                "fab fa-pied-piper",
                "fab fa-pied-piper-alt",
                "fab fa-pied-piper-hat",
                "fab fa-pied-piper-pp",
                "fab fa-pinterest",
                "fab fa-pinterest-p",
                "fab fa-pinterest-square",
                "fab fa-playstation",
                "fab fa-product-hunt",
                "fab fa-pushed",
                "fab fa-python",
                "fab fa-qq",
                "fab fa-quinscape",
                "fab fa-quora",
                "fab fa-r-project",
                "fab fa-ravelry",
                "fab fa-react",
                "fab fa-readme",
                "fab fa-rebel",
                "fab fa-red-river",
                "fab fa-reddit",
                "fab fa-reddit-alien",
                "fab fa-reddit-square",
                "fab fa-rendact",
                "fab fa-renren",
                "fab fa-replyd",
                "fab fa-researchgate",
                "fab fa-resolving",
                "fab fa-rev",
                "fab fa-rocketchat",
                "fab fa-rockrms",
                "fab fa-safari",
                "fab fa-sass",
                "fab fa-schlix",
                "fab fa-scribd",
                "fab fa-searchengin",
                "fab fa-sellcast",
                "fab fa-sellsy",
                "fab fa-servicestack",
                "fab fa-shirtsinbulk",
                "fab fa-shopware",
                "fab fa-simplybuilt",
                "fab fa-sistrix",
                "fab fa-sith",
                "fab fa-skyatlas",
                "fab fa-skype",
                "fab fa-slack",
                "fab fa-slack-hash",
                "fab fa-slideshare",
                "fab fa-snapchat",
                "fab fa-snapchat-ghost",
                "fab fa-snapchat-square",
                "fab fa-soundcloud",
                "fab fa-speakap",
                "fab fa-spotify",
                "fab fa-squarespace",
                "fab fa-stack-exchange",
                "fab fa-stack-overflow",
                "fab fa-staylinked",
                "fab fa-steam",
                "fab fa-steam-square",
                "fab fa-steam-symbol",
                "fab fa-sticker-mule",
                "fab fa-strava",
                "fab fa-stripe",
                "fab fa-stripe-s",
                "fab fa-studiovinari",
                "fab fa-stumbleupon",
                "fab fa-stumbleupon-circle",
                "fab fa-superpowers",
                "fab fa-supple",
                "fab fa-teamspeak",
                "fab fa-telegram",
                "fab fa-telegram-plane",
                "fab fa-tencent-weibo",
                "fab fa-themeco",
                "fab fa-themeisle",
                "fab fa-trade-federation",
                "fab fa-trello",
                "fab fa-tripadvisor",
                "fab fa-tumblr",
                "fab fa-tumblr-square",
                "fab fa-twitch",
                "fab fa-twitter",
                "fab fa-twitter-square",
                "fab fa-typo3",
                "fab fa-uber",
                "fab fa-uikit",
                "fab fa-uniregistry",
                "fab fa-untappd",
                "fab fa-usb",
                "fab fa-ussunnah",
                "fab fa-vaadin",
                "fab fa-viacoin",
                "fab fa-viadeo",
                "fab fa-viadeo-square",
                "fab fa-viber",
                "fab fa-vimeo",
                "fab fa-vimeo-square",
                "fab fa-vimeo-v",
                "fab fa-vine",
                "fab fa-vk",
                "fab fa-vnv",
                "fab fa-vuejs",
                "fab fa-weebly",
                "fab fa-weibo",
                "fab fa-weixin",
                "fab fa-whatsapp",
                "fab fa-whatsapp-square",
                "fab fa-whmcs",
                "fab fa-wikipedia-w",
                "fab fa-windows",
                "fab fa-wix",
                "fab fa-wolf-pack-battalion",
                "fab fa-wordpress",
                "fab fa-wordpress-simple",
                "fab fa-wpbeginner",
                "fab fa-wpexplorer",
                "fab fa-wpforms",
                "fab fa-xbox",
                "fab fa-xing",
                "fab fa-xing-square",
                "fab fa-y-combinator",
                "fab fa-yahoo",
                "fab fa-yandex",
                "fab fa-yandex-international",
                "fab fa-yelp",
                "fab fa-yoast",
                "fab fa-youtube",
                "fab fa-youtube-square",
                "fab fa-zhihu",
                // Thêm icon khác từ Font Awesome Free Solid
            ];

            // Render icon picker
            function renderIconPicker(pickerId, inputId, previewId) {
                const picker = $(pickerId);
                const iconList = picker.find('.icon-list');
                icons.forEach(icon => {
                    iconList.append(`<div class="icon-item" data-icon="${icon}"><i class="${icon}"></i></div>`);
                });

                // Xử lý chọn icon
                iconList.on('click', '.icon-item', function() {
                    const selectedIcon = $(this).data('icon');
                    $(inputId).val(selectedIcon);
                    $(previewId).html(`<i class="${selectedIcon}"></i>`);
                    picker.find('.icon-item').removeClass('selected');
                    $(this).addClass('selected');
                    picker.hide();
                });

                // Xử lý tìm kiếm icon
                picker.find('.icon-search').on('input', function() {
                    const query = $(this).val().toLowerCase();
                    iconList.find('.icon-item').each(function() {
                        const iconName = $(this).data('icon').replace('fas fa-', '');
                        $(this).toggle(iconName.includes(query));
                    });
                });
            }

            // Khởi tạo icon picker cho modal thêm
            renderIconPicker('#add_icon_picker', '#add_icon', '#addMenuModal .icon-preview');

            // Khởi tạo icon picker cho modal sửa
            renderIconPicker('#edit_icon_picker', '#edit_icon', '#editMenuModal .icon-preview');

            // Toggle icon picker
            $('.select-icon').on('click', function() {
                const picker = $(this).closest('.form-group').find('.icon-picker');
                $('.icon-picker').not(picker).hide();
                picker.toggle();
            });

            // Ẩn icon picker khi click ra ngoài
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.icon-picker, .select-icon').length) {
                    $('.icon-picker').hide();
                }
            });

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

            // Xử lý modal sửa
            $('.edit-menu').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: 'fetch_menu.php',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(data) {
                        $('#edit_id').val(data.id);
                        $('#edit_name').val(data.name);
                        $('#edit_icon').val(data.icon);
                        $('#editMenuModal .icon-preview').html(data.icon ? `<i class="${data.icon}"></i>` : '');
                        $('#edit_url').val(data.url);
                        $('#edit_badge').val(data.badge);
                        $('#edit_parent_id').val(data.parent_id);
                        $('#edit_position').val(data.position);
                        $('#edit_is_section').prop('checked', data.is_section == 1);
                        $('#edit_is_visible').prop('checked', data.is_visible == 1);
                        // Đánh dấu icon đã chọn
                        $('#edit_icon_picker .icon-item').removeClass('selected');
                        if (data.icon) {
                            $(`#edit_icon_picker .icon-item[data-icon="${data.icon}"]`).addClass('selected');
                        }
                    },
                    error: function() {
                        iziToast.error({
                            title: 'Lỗi',
                            message: 'Không thể tải dữ liệu danh mục',
                            position: 'topRight'
                        });
                    }
                });
            });

            // Xử lý bật/tắt hiển thị
            $('.toggle-visibility').on('click', function() {
                var $this = $(this);
                var id = $this.data('id');
                var is_visible = $this.data('visible') ? 0 : 1;

                $.ajax({
                    url: 'manage_menu.php',
                    method: 'POST',
                    data: { toggle_visible: true, id: id, is_visible: is_visible },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $this.data('visible', is_visible);
                            $this.toggleClass('fa-eye fa-eye-slash text-success text-danger');
                            iziToast.success({
                                title: 'Thành công',
                                message: 'Cập nhật trạng thái hiển thị thành công',
                                position: 'topRight'
                            });
                        } else {
                            iziToast.error({
                                title: 'Lỗi',
                                message: 'Cập nhật trạng thái thất bại',
                                position: 'topRight'
                            });
                        }
                    },
                    error: function() {
                        iziToast.error({
                            title: 'Lỗi',
                            message: 'Không thể kết nối server',
                            position: 'topRight'
                        });
                    }
                });
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