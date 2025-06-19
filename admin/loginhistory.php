<?php
ob_start();
session_start();
require 'database/config.php';
require_once 'include/functions.php';

// Kiểm tra đăng nhập và quyền admin
// if (!isset($_SESSION['user_id']) || !is_admin()) {
//     log_debug("Unauthorized access attempt to loginhistory.php");
//     header("Location: login.php");
//     exit;
// }

// Lấy tham số tìm kiếm và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Số bản ghi mỗi trang
$offset = ($page - 1) * $perPage;

// Xây dựng câu truy vấn
try {
    $query = "SELECT ul.id, ul.userId, ul.username, ul.userIp, ul.login_time, ul.status, u.username AS user_name
              FROM userlog ul
              LEFT JOIN users u ON ul.userId = u.id
              WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (ul.username LIKE :search OR ul.userIp LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // Đếm tổng số bản ghi
    $countStmt = $pdo->prepare(str_replace('SELECT ul.id, ul.userId, ul.username, ul.userIp, ul.login_time, ul.status, u.username AS user_name', 'SELECT COUNT(*)', $query));
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $perPage);

    // Thêm phân trang và sắp xếp
    $query .= " ORDER BY ul.login_time DESC LIMIT :offset, :perPage";
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $login_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    log_debug('Login history error: ' . $e->getMessage() . ' at line ' . $e->getLine());
    $_SESSION['error_message'] = 'Lỗi tải dữ liệu lịch sử đăng nhập!';
    $_SESSION['error_type'] = 'error';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Lịch Sử Đăng Nhập - Kaiadmin</title>
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon">
    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
                urls: ["assets/css/fonts.min.css"]
            },
            active: function() { sessionStorage.fonts = true; }
        });
    </script>
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <!-- iZitoast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'include/sidebar.php'; ?>
        <div class="main-panel">
            <?php include 'include/header.php'; ?>
            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Lịch Sử Đăng Nhập</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Lịch sử đăng nhập</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Danh Sách Lịch Sử Đăng Nhập</div>
                                        <div class="card-tools">
                                            <form method="GET" action="">
                                                <div class="input-group">
                                                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên hoặc IP" value="<?php echo htmlspecialchars($search); ?>">
                                                    <button class="btn btn-primary btn-sm" type="submit">Tìm</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Tên người dùng</th>
                                                    <th>IP</th>
                                                    <th>Thời gian đăng nhập</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($login_history)): ?>
                                                    <tr><td colspan="5" class="text-center">Không có dữ liệu</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($login_history as $log): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($log['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                                                            <td><?php echo htmlspecialchars($log['userIp']); ?></td>
                                                            <td><?php echo date('d/m/Y H:i:s', strtotime($log['login_time'])); ?></td>
                                                            <td>
                                                                <span class="badge <?php echo $log['status'] == 'success' ? 'badge-success' : 'badge-danger'; ?>">
                                                                    <?php echo $log['status'] == 'success' ? 'Thành công' : 'Thất bại'; ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Phân trang -->
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Trước</a>
                                                </li>
                                            <?php endif; ?>
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Sau</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'include/footer.php'; ?>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <!-- jQuery Scrollbar -->
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <!-- Datatables -->
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <!-- Sweet Alert -->
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <!-- Kaiadmin JS -->
    <script src="assets/js/kaiadmin.min.js"></script>
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script>
        $(document).ready(function() {
            // iZitoast notification
            <?php if (isset($_SESSION['error_message']) && isset($_SESSION['error_type'])): ?>
                iziToast.<?php echo $_SESSION['error_type']; ?>({
                    title: '<?php echo $_SESSION['title'] ?? ($_SESSION['error_type'] === 'success' ? 'Thành công' : 'Lỗi'); ?>',
                    message: '<?php echo $_SESSION['error_message']; ?>',
                    position: 'topRight',
                    timeout: 6000
                });
                <?php
                unset($_SESSION['error_message']);
                unset($_SESSION['error_type']);
                unset($_SESSION['title']);
                ?>
            <?php endif; ?>

            // Khởi tạo DataTables
            $('table').DataTable({
                "pageLength": <?php echo $perPage; ?>,
                "lengthChange": false,
                "searching": false,
                "ordering": false,
                "info": false,
                "paging": false
            });
        });
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>