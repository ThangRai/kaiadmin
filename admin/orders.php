<?php
ob_start();
session_start();
require 'database/config.php';
require_once 'include/functions.php';
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    log_debug("Unauthorized access attempt to orders.php");
    header("Location: login.php");
    exit;
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$pdo) {
    log_debug("Database connection failed");
    $_SESSION['toast_message'] = 'Không thể kết nối đến cơ sở dữ liệu!';
    $_SESSION['toast_type'] = 'error';
    header("Location: orders.php");
    exit;
}

// Hàm chuyển trạng thái sang tiếng Việt
function getStatusLabel($status) {
    switch ($status) {
        case 'pending': return 'Đang xử lý';
        case 'completed': return 'Đã giao';
        case 'cancelled': return 'Hủy';
        default: return 'Không xác định';
    }
}

// Xử lý gửi email
if (isset($_GET['send_email']) && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    if ($order_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT o.*, 
                                 CASE 
                                     WHEN o.payment_method = 'cash' THEN 'Tiền mặt'
                                     WHEN o.payment_method = 'bank_transfer' THEN 'Chuyển khoản'
                                     ELSE 'Không xác định'
                                 END AS payment_method_label
                                 FROM orders o 
                                 WHERE o.id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                $stmt = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
                $stmt->execute([$order_id]);
                $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Tạo nội dung email
                $mail_content = "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <h2>Thông tin đơn hàng #{$order['id']}</h2>
                    <h3>Thông tin khách hàng</h3>
                    <p><strong>Tên:</strong> " . htmlspecialchars($order['customer_name']) . "</p>
                    <p><strong>Số điện thoại:</strong> " . htmlspecialchars($order['phone']) . "</p>
                    <p><strong>Email:</strong> " . htmlspecialchars($order['email'] ?: 'N/A') . "</p>
                    <p><strong>Địa chỉ:</strong> " . htmlspecialchars($order['address']) . ", " . 
                    htmlspecialchars($order['ward']) . ", " . htmlspecialchars($order['district']) . ", " . 
                    htmlspecialchars($order['province']) . "</p>
                    <p><strong>Trạng thái:</strong> " . getStatusLabel($order['status']) . "</p>
                    <p><strong>Tổng tiền:</strong> " . number_format($order['total'], 0) . " đ</p>
                    <p><strong>Phương thức thanh toán:</strong> " . htmlspecialchars($order['payment_method_label']) . "</p>
                    <h3>Chi tiết đơn hàng</h3>
                    <table>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                        </tr>";
                
                foreach ($order_details as $item) {
                    $mail_content .= "
                        <tr>
                            <td>" . htmlspecialchars($item['title']) . "</td>
                            <td>" . number_format($item['price'], 0) . " đ</td>
                            <td>" . htmlspecialchars($item['quantity']) . "</td>
                            <td>" . number_format($item['price'] * $item['quantity'], 0) . " đ</td>
                        </tr>";
                }
                
                $mail_content .= "
                    </table>
                    <p><strong>Ghi chú:</strong> " . htmlspecialchars($order['note'] ?: 'N/A') . "</p>
                </body>
                </html>";

                // Cấu hình PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'badaotulong123@gmail.com'; // Thay bằng email của bạn
                    $mail->Password = 'nihu fluz qcla wgmh'; // Thay bằng mật khẩu ứng dụng
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('badaotulong123@gmail.com', 'Kai Admin Thắng Rai');
                    $mail->addAddress($order['email']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Thông tin đơn hàng #' . $order['id'];
                    $mail->Body = $mail_content;

                    $mail->send();
                    $_SESSION['toast_message'] = 'Gửi email thành công!';
                    $_SESSION['toast_type'] = 'success';
                } catch (Exception $e) {
                    log_debug('Email error: ' . $mail->ErrorInfo);
                    $_SESSION['toast_message'] = 'Lỗi gửi email: ' . $mail->ErrorInfo;
                    $_SESSION['toast_type'] = 'error';
                }
            } else {
                $_SESSION['toast_message'] = 'Đơn hàng không tồn tại!';
                $_SESSION['toast_type'] = 'error';
            }
        } catch (Exception $e) {
            log_debug('Fetch order for email error: ' . $e->getMessage());
            $_SESSION['toast_message'] = 'Lỗi xử lý email!';
            $_SESSION['toast_type'] = 'error';
        }
    }
    header("Location: orders.php");
    exit;
}

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['toggle_status'])) {
    $order_id = $_POST['order_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    if ($order_id && in_array($status, ['pending', 'completed', 'cancelled'])) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            $_SESSION['toast_message'] = 'Cập nhật trạng thái đơn hàng thành công!';
            $_SESSION['toast_type'] = 'success';
        } catch (Exception $e) {
            log_debug('Toggle status error: ' . $e->getMessage() . ' at line ' . $e->getLine());
            $_SESSION['toast_message'] = 'Lỗi cập nhật trạng thái: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
        }
    } else {
        log_debug("Invalid order_id or status: order_id=$order_id, status=$status");
        $_SESSION['toast_message'] = 'Dữ liệu không hợp lệ!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: orders.php");
    exit;
}

// Xử lý xóa đơn hàng
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($delete_id > 0) {
        $pdo->beginTransaction();
        try {
            // Kiểm tra xem đơn hàng có tồn tại không
            $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
            $stmt->execute([$delete_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Đơn hàng không tồn tại với ID: $delete_id");
            }

            // Xóa chi tiết đơn hàng
            $stmt = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
            $stmt->execute([$delete_id]);

            // Xóa đơn hàng
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $result = $stmt->execute([$delete_id]);

            $pdo->commit();
            $_SESSION['toast_message'] = $result ? 'Xóa đơn hàng thành công!' : 'Xóa đơn hàng thất bại!';
            $_SESSION['toast_type'] = $result ? 'success' : 'error';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = 'Delete error: ' . $e->getMessage() . ' at line ' . $e->getLine();
            log_debug($error_message);
            error_log($error_message); // Ghi vào tệp nhật ký PHP
            $_SESSION['toast_message'] = $error_message;
            $_SESSION['toast_type'] = 'error';
        }
    } else {
        $error_message = "Invalid delete_id: $delete_id";
        log_debug($error_message);
        error_log($error_message);
        $_SESSION['toast_message'] = 'ID đơn hàng không hợp lệ!';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: orders.php");
    exit;
}

// Xử lý hiển thị chi tiết đơn hàng
$show_form = false;
$order = null;
$order_details = [];
if (isset($_GET['method']) && $_GET['method'] === 'frm' && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    if ($order_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT o.*, 
                                   CASE 
                                       WHEN o.payment_method = 'cash' THEN 'Tiền mặt'
                                       WHEN o.payment_method = 'bank_transfer' THEN 'Chuyển khoản'
                                       ELSE 'Không xác định'
                                   END AS payment_method_label
                                   FROM orders o 
                                   WHERE o.id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                $stmt = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
                $stmt->execute([$order_id]);
                $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $show_form = true;
            } else {
                log_debug("Order not found: order_id=$order_id");
                $_SESSION['toast_message'] = 'Đơn hàng không tồn tại!';
                $_SESSION['toast_type'] = 'error';
                header("Location: orders.php");
                exit;
            }
        } catch (Exception $e) {
            log_debug('Fetch order details error: ' . $e->getMessage() . ' at line ' . $e->getLine());
            $_SESSION['toast_message'] = 'Lỗi tải chi tiết đơn hàng!';
            $_SESSION['toast_type'] = 'error';
            header("Location: orders.php");
            exit;
        }
    } else {
        log_debug("Invalid order_id: $order_id");
        $_SESSION['toast_message'] = 'ID đơn hàng không hợp lệ!';
        $_SESSION['toast_type'] = 'error';
        header("Location: orders.php");
        exit;
    }
} else {
    try {
        $stmt = $pdo->query("SELECT o.*, 
                             CASE 
                                 WHEN o.payment_method = 'cash' THEN 'Tiền mặt'
                                 WHEN o.payment_method = 'bank_transfer' THEN 'Chuyển khoản'
                                 ELSE 'Không xác định'
                             END AS payment_method_label
                             FROM orders o 
                             ORDER BY o.created_at DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        log_debug('Fetch orders error: ' . $e->getMessage() . ' at line ' . $e->getLine());
        $_SESSION['toast_message'] = 'Lỗi tải danh sách đơn hàng!';
        $_SESSION['toast_type'] = 'error';
        header("Location: orders.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Quản Lý Đơn Hàng - Kaiadmin</title>
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon">
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: {"families"},
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
    <style>
        .order-details-table { width: 100%; border-collapse: collapse; }
        .order-details-table th, .order-details-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .order-details-table th { background-color: #f8f9fa; }
        .order-details-table img { width: 50px; height: 50px; object-fit: cover; }
        .table-responsive { margin-bottom: 1rem; }
        .form-group label { font-weight: 500; }
        .table th, .table td { vertical-align: middle; }
        .card-body .form-group p { margin-bottom: 0.5rem; }
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
                        <h3 class="fw-bold mb-3">Quản Lý Đơn Hàng</h3>
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
                                <a href="#">Quản lý đơn hàng</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title"><?php echo $show_form ? 'Chi Tiết Đơn Hàng #' . $order['id'] : 'Danh Sách Đơn Hàng'; ?></h4>
                                </div>
                                <div class="card-body">
                                    <?php if ($show_form): ?>
                                        <div class="form-group">
                                            <h6>Thông Tin Khách Hàng</h6>
                                            <p><strong>Tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?: 'N/A'); ?></p>
                                            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                                            <p><strong>Phường/Xã:</strong> <?php echo htmlspecialchars($order['ward']); ?></p>
                                            <p><strong>Quận/Huyện:</strong> <?php echo htmlspecialchars($order['district']); ?></p>
                                            <p><strong>Tỉnh/Thành phố:</strong> <?php echo htmlspecialchars($order['province']); ?></p>
                                            <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['note'] ?: 'N/A'); ?></p>
                                            <p><strong>Trạng thái:</strong> <?php echo getStatusLabel($order['status']); ?></p>
                                            <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total'], 0); ?> đ</p>
                                            <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order['payment_method_label']); ?></p>
                                        </div>
                                        <div class="form-group">
                                            <h6>Sản Phẩm Đặt Hàng</h6>
                                            <table class="order-details-table">
                                                <thead>
                                                    <tr>
                                                        <th>Sản Phẩm</th>
                                                        <th>Giá</th>
                                                        <th>Số Lượng</th>
                                                        <th>Tổng</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($order_details)): ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center">Không có sản phẩm nào!</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($order_details as $item): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                                                <td><?php echo number_format($item['price'], 0); ?> đ</td>
                                                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                                <td><?php echo number_format($item['price'] * $item['quantity'], 0); ?> đ</td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="form-group text-right">
                                            <a href="orders.php" class="btn btn-secondary">Quay lại</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Tên Khách Hàng</th>
                                                        <th>Số Điện Thoại</th>
                                                        <th>Tổng Tiền</th>
                                                        <th>Phương Thức Thanh Toán</th>
                                                        <th>Trạng Thái</th>
                                                        <th>Ngày Tạo</th>
                                                        <th>Thao Tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($orders)): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center">Không có đơn hàng nào!</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($orders as $order): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                                <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                                                <td><?php echo number_format($order['total'], 0); ?> đ</td>
                                                                <td><?php echo htmlspecialchars($order['payment_method_label']); ?></td>
                                                                <form method="POST" action="orders.php">
                                                                    <td>
                                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                        <input type="hidden" name="toggle_status" value="1">
                                                                        <select name="status" class="form-control toggle-status" onchange="this.form.submit()">
                                                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Đang xử lý</option>
                                                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Đã giao</option>
                                                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Hủy</option>
                                                                        </select>
                                                                    </form>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                                                <td>
                                                                    <a href="orders.php?method=frm&order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info view-order">
                                                                        <i class="fas fa-eye"></i> Chi tiết
                                                                    </a>
                                                                    <a href="?delete_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger delete-order">
                                                                        <i class="fas fa-trash"></i> Xóa
                                                                    </a>
                                                                    <a href="?send_email=1&order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary send-email">
                                                                        <i class="fas fa-envelope"></i> Gửi email
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'include/footer.php'; ?>
        <?php include 'include/custom-template.php'; ?>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">    <!-- iZitoast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
    <script>
$(document).ready(function() {
    // Xử lý thông báo iZitoast
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

    // Xử lý nút Xóa
    $('.delete-order').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        console.log('Delete href:', href); // Debug href
        Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: 'Đặt hàng sẽ bị xóa vĩnh viễn!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Confirmed, redirecting to:', href); // Debug chuyển hướng
                window.location.href = href;
            }
        });
    });

    // Xử lý nút Gửi email
    $('.send-email').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        console.log('Send email href:', href); // Debug href
        Swal.fire({
            title: 'Xác nhận gửi email?',
            text: 'Thông tin đơn hàng sẽ được gửi đến khách hàng!',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Gửi',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Confirmed, redirecting to:', href); // Debug chuyển hướng
                window.location.href = href;
            }
        });
    });
});
</script>
    <?php ob_end_flush(); ?>
</body>
</html>