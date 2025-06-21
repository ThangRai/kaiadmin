<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'database/config.php';
require_once 'include/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    log_debug("Unauthorized access attempt to index.php");
    header("Location: login.php");
    exit;
}

// Lấy dữ liệu thống kê
try {
    // Tổng số đơn hàng
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    // Tổng số sản phẩm
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Tổng số tài khoản
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Tổng doanh thu
    $stmt = $pdo->query("SELECT SUM(total) as total_revenue FROM orders WHERE status = 'completed'");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

    // Tạo mảng 12 tháng gần nhất
    $months = [];
    $revenues = [];
    $currentDate = new DateTime();
    for ($i = 11; $i >= 0; $i--) {
        $month = (clone $currentDate)->modify("-$i months")->format('Y-m');
        $months[] = $month;
        $revenues[$month] = 0; // Khởi tạo doanh thu bằng 0
    }

    // Thống kê doanh thu theo tháng (12 tháng gần nhất)
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue
        FROM orders
        WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month
    ");
    $stmt->execute();
    $revenue_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gán doanh thu vào mảng
    foreach ($revenue_data as $row) {
        $revenues[$row['month']] = (float)$row['revenue'];
    }

    // Chuyển mảng doanh thu thành dạng tuần tự
    $revenues = array_values($revenues);

    // Thống kê lượt truy cập (7 ngày gần nhất)
    $stmt = $pdo->prepare("
        SELECT DATE(visit_date) as date, SUM(visit_count) as visits
        FROM visits
        WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY date
        ORDER BY date
    ");
    $stmt->execute();
    $visit_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $visit_dates = [];
    $visit_counts = [];
    foreach ($visit_data as $row) {
        $visit_dates[] = $row['date'];
        $visit_counts[] = (int)$row['visits'];
    }

    // Thống kê tất cả khách hàng
    $stmt = $pdo->prepare("
        SELECT 
            customer_name, 
            phone, 
            email, 
            SUM(total) as total_spent
        FROM orders
        WHERE status = 'completed'
        GROUP BY customer_name, phone, email
        ORDER BY total_spent DESC
    ");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    log_debug('Statistics error: ' . $e->getMessage() . ' at line ' . $e->getLine());
    $_SESSION['toast_message'] = 'Lỗi tải dữ liệu thống kê!';
    $_SESSION['toast_type'] = 'error';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Tổng Quan - Kaiadmin</title>
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .chart-card {
            height: 350px; /* Chiều cao cố định cho card */
            display: flex;
            flex-direction: column;
        }
        .chart-card .card-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }
        .chart-card canvas {
            max-height: 280px !important; /* Chiều cao cố định cho canvas */
            width: 100% !important;
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
                        <h3 class="fw-bold mb-3">Tổng Quan</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="index.php"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Tổng quan</a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <!-- Tổng số đơn hàng -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Đơn hàng</p>
                                                <h4 class="card-title"><?php echo number_format($total_orders, 0); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tổng số sản phẩm -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Sản phẩm</p>
                                                <h4 class="card-title"><?php echo number_format($total_products, 0); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tổng số tài khoản -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Tài khoản</p>
                                                <h4 class="card-title"><?php echo number_format($total_users, 0); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tổng doanh thu -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Doanh thu</p>
                                                <h4 class="card-title"><?php echo number_format($total_revenue, 0); ?> đ</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <!-- Thống kê doanh thu -->
                        <div class="col-md-7">
                            <div class="card card-round">
                                <div class="card-header">
                                    <h4 class="card-title">Thống Kê Doanh Thu</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="revenueChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <!-- Thống kê truy cập -->
                        <div class="col-md-5">
                            <div class="card card-round">
                                <div class="card-header">
                                    <h4 class="card-title">Thống Kê Truy Cập</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="visitsChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-round">
                                <div class="card-header">
                                    <h4 class="card-title">Danh Sách Khách Hàng</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Tên Khách Hàng</th>
                                                    <th>Số Điện Thoại</th>
                                                    <th>Email</th>
                                                    <th>Tổng Chi Tiêu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($customers)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">Chưa có dữ liệu khách hàng!</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($customers as $customer): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                                            <td><?php echo htmlspecialchars($customer['email'] ?: 'N/A'); ?></td>
                                                            <td><?php echo number_format($customer['total_spent'], 0); ?> đ</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    <?php include 'include/custom-template.php'; ?>

            <?php include 'include/footer.php'; ?>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <!-- jQuery Scrollbar -->
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <!-- Chart JS -->
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>
    <!-- jQuery Sparkline -->
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <!-- Chart Circle -->
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>
    <!-- Datatables -->
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <!-- Sweet Alert -->
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <!-- Kaiadmin JS -->
    <script src="assets/js/kaiadmin.min.js"></script>
    <!-- Kaiadmin DEMO methods -->
    <script src="assets/js/setting-demo.js"></script>
    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
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

            // Biểu đồ doanh thu (Line Chart)
            var revenueCtx = document.getElementById('revenueChart').getContext('2d');
            var revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Doanh thu (VND)',
                        data: <?php echo json_encode($revenues); ?>,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#007bff',
                        pointBorderColor: '#ffffff',
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: '#007bff'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' đ';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });

            // Biểu đồ truy cập (Bar Chart)
            var visitsCtx = document.getElementById('visitsChart').getContext('2d');
            var visitsChart = new Chart(visitsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($visit_dates); ?>,
                    datasets: [{
                        label: 'Lượt truy cập',
                        data: <?php echo json_encode($visit_counts); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.6)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>