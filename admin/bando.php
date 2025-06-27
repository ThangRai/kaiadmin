<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'database/config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check database connection
if (!$pdo) {
    $_SESSION['toast_message'] = 'Không thể kết nối đến cơ sở dữ liệu!';
    $_SESSION['toast_type'] = 'error';
    header("Location: bando.php");
    exit;
}

// Fetch provinces from API
function getProvinces() {
    $url = 'https://provinces.open-api.vn/api/';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?: [];
}

// Handle toggle status
if (isset($_POST['toggle_status'])) {
    $map_id = $_POST['map_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE maps SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $map_id]);
        $_SESSION['toast_message'] = 'Cập nhật trạng thái thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: bando.php");
    exit;
}

// Handle delete map
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM maps WHERE id = ?");
        $result = $stmt->execute([$delete_id]);
        $_SESSION['toast_message'] = $result ? 'Xóa bản đồ thành công!' : 'Xóa bản đồ thất bại!';
        $_SESSION['toast_type'] = $result ? 'success' : 'error';
    } catch (Exception $e) {
        $_SESSION['toast_message'] = 'Lỗi xóa!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: bando.php");
    exit;
}

// Handle add/edit map
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_map']) || isset($_POST['edit_map']))) {
    $pdo->beginTransaction();
    try {
        $title = trim($_POST['title']);
        $address = trim($_POST['address']);
        $province_code = $_POST['province_code'];
        $zoom = (int)$_POST['zoom'];
        $coordinates = trim($_POST['coordinates']);
        $embed_code = trim($_POST['embed_code']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validate inputs
        if (empty($title) || empty($address) || empty($province_code) || empty($coordinates) || empty($embed_code)) {
            throw new Exception('Vui lòng điền đầy đủ các trường bắt buộc!');
        }

        // Validate coordinates format (lat,lng)
        if (!preg_match('/^-?\d+\.\d+,-?\d+\.\d+$/', $coordinates)) {
            throw new Exception('Tọa độ không đúng định dạng (lat,lng)!');
        }

        if (isset($_POST['add_map'])) {
            $stmt = $pdo->prepare("INSERT INTO maps (title, address, province_code, zoom, coordinates, embed_code, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$title, $address, $province_code, $zoom, $coordinates, $embed_code, $is_active]);
        } else {
            $map_id = $_POST['map_id'];
            $stmt = $pdo->prepare("UPDATE maps SET title = ?, address = ?, province_code = ?, zoom = ?, coordinates = ?, embed_code = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $address, $province_code, $zoom, $coordinates, $embed_code, $is_active, $map_id]);
        }

        $pdo->commit();
        $_SESSION['toast_message'] = isset($_POST['add_map']) ? 'Thêm bản đồ thành công!' : 'Cập nhật bản đồ thành công!';
        $_SESSION['toast_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['toast_message'] = 'Lỗi lưu: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: bando.php");
    exit;
}

// Fetch maps with filters
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$province_code_filter = isset($_GET['province_code']) ? $_GET['province_code'] : '';

$where = ['1=1'];
$params = [];
if ($keyword) {
    $where[] = "title LIKE ?";
    $params[] = "%$keyword%";
}
if ($province_code_filter) {
    $where[] = "province_code = ?";
    $params[] = $province_code_filter;
}

$query = "SELECT * FROM maps WHERE " . implode(' AND ', $where) . " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$maps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch provinces
$provinces = getProvinces();

// Get active state
$method = isset($_GET['method']) ? $_GET['method'] : 'list';
$edit_map = null;
if ($method === 'frm' && isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM maps WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_map = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản lý Bản đồ - Kaiadmin</title>
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/css/bootstrap3/bootstrap-switch.min.css" rel="stylesheet">
    <style>
        .form-group label { font-weight: 500; }
        .form-header { margin-bottom: 20px; }
        .map-preview { width: 100%; height: 400px; border: 1px solid #ddd; margin-top: 10px; }
        .table-responsive { margin-bottom: 1rem; }
        .add-btn { position: absolute; top: 20px; right: 20px; }
        @media (max-width: 768px) {
            .add-btn { position: static; margin-bottom: 15px; }
            .form-header .btn-primary { padding: 6px; font-size: 0.9rem; }
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
                        <h3 class="fw-bold mb-3">Quản lý Bản đồ</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Bản đồ</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php if ($method === 'frm'): ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo $edit_map ? 'Sửa Bản đồ' : 'Thêm Bản đồ'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="form-header d-flex justify-content-end">
                                                <button type="submit" name="<?php echo $edit_map ? 'edit_map' : 'add_map'; ?>" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Lưu
                                                </button>
                                                <a href="bando.php" class="btn btn-secondary ml-2">
                                                    <i class="fas fa-times"></i> Hủy
                                                </a>
                                            </div>

                                            <div class="row">
                                                <!-- Left Column (5/12) -->
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label for="province_code">Tỉnh/Thành phố</label>
                                                        <select id="province_code" name="province_code" class="form-control" required>
                                                            <option value="">Chọn tỉnh/thành</option>
                                                            <?php foreach ($provinces as $province): ?>
                                                                <option value="<?php echo $province['code']; ?>" <?php echo $edit_map && $edit_map['province_code'] == $province['code'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($province['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Hiển thị</label>
                                                        <div>
                                                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $edit_map && $edit_map['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="is_active">Bật</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Xem bản đồ</label>
                                                        <div class="map-preview">
                                                            <?php if ($edit_map && $edit_map['embed_code']): ?>
                                                                <?php echo $edit_map['embed_code']; ?>
                                                            <?php else: ?>
                                                                <p>Chưa có mã nhúng bản đồ.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Right Column (7/12) -->
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <label for="title">Tên bản đồ</label>
                                                        <input type="text" id="title" name="title" class="form-control" value="<?php echo $edit_map ? htmlspecialchars($edit_map['title']) : ''; ?>" required placeholder="Nhập tên bản đồ">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="address">Địa chỉ</label>
                                                        <textarea id="address" name="address" class="form-control" required placeholder="Nhập địa chỉ chi tiết"><?php echo $edit_map ? htmlspecialchars($edit_map['address']) : ''; ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="zoom">Zoom</label>
                                                        <input type="number" id="zoom" name="zoom" class="form-control" value="<?php echo $edit_map ? htmlspecialchars($edit_map['zoom']) : '15'; ?>" min="1" max="20" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="coordinates">Tọa độ (lat,lng)</label>
                                                        <input type="text" id="coordinates" name="coordinates" class="form-control" value="<?php echo $edit_map ? htmlspecialchars($edit_map['coordinates']) : ''; ?>" required placeholder="VD: 21.028511,105.804817">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="embed_code">Mã nhúng</label>
                                                        <textarea id="embed_code" name="embed_code" class="form-control" required placeholder="Dán mã nhúng iframe từ Google Maps"><?php echo $edit_map ? htmlspecialchars($edit_map['embed_code']) : ''; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($edit_map): ?>
                                                <input type="hidden" name="map_id" value="<?php echo $edit_map['id']; ?>">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Danh sách Bản đồ</h4>
                                    </div>
                                    <div class="card-body">
                                        <a href="?method=frm" class="btn btn-primary add-btn"><i class="fas fa-map"></i> Thêm Bản đồ</a>
                                        <form method="GET" class="mb-4">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="keyword">Từ khóa</label>
                                                        <input type="text" id="keyword" name="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Nhập tên bản đồ">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="province_code">Tỉnh/Thành phố</label>
                                                        <select id="province_code" name="province_code" class="form-control">
                                                            <option value="">Tất cả</option>
                                                            <?php foreach ($provinces as $province): ?>
                                                                <option value="<?php echo $province['code']; ?>" <?php echo $province_code_filter == $province['code'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($province['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mt-4">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-search"></i> Tìm
                                                        </button>
                                                        <a href="bando.php" class="btn btn-secondary">
                                                            <i class="fas fa-sync-alt"></i> Reset
                                                        </a>
                                                    </div>

                                                </div>
                                            </div>
                                        </form>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Tên</th>
                                                        <th>Địa chỉ</th>
                                                        <th>Tọa độ</th>
                                                        <th>Hiển thị</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($maps as $map): ?>
                                                        <tr>
                                                            <td><?php echo $map['id']; ?></td>
                                                            <td><?php echo htmlspecialchars($map['title']); ?></td>
                                                            <td><?php echo htmlspecialchars($map['address']); ?></td>
                                                            <td><?php echo htmlspecialchars($map['coordinates']); ?></td>
                                                            <td>
                                                                <form method="POST" action="bando.php">
                                                                    <input type="hidden" name="map_id" value="<?php echo $map['id']; ?>">
                                                                    <input type="hidden" name="toggle_status" value="1">
                                                                    <input type="checkbox" name="is_active" class="toggle-switch" value="1" <?php echo $map['is_active'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <a href="?method=frm&edit_id=<?php echo $map['id']; ?>" class="btn btn-sm btn-warning">
                                                                    <i class="fas fa-edit"></i> Sửa
                                                                </a>
                                                                <a href="?delete_id=<?php echo $map['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                                    <i class="fas fa-trash-alt"></i> Xóa
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.3.4/dist/js/bootstrap-switch.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
                iziToast.<?php echo $_SESSION['toast_type']; ?>({
                    title: '<?php echo $_SESSION['toast_type'] === 'success' ? 'Thành công' : 'Lỗi'; ?>',
                    message: '<?php echo htmlspecialchars($_SESSION['toast_message']); ?>',
                    position: 'topRight',
                    timeout: 6000
                });
                <?php unset($_SESSION['toast_message']); unset($_SESSION['toast_type']); ?>
            <?php endif; ?>

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