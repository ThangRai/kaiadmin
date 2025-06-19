<?php
ob_start();
session_start();
require 'header.php'; // Bao gồm tệp header.php chứa config.php

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $ward = $_POST['ward'] ?? '';
    $district = $_POST['district'] ?? '';
    $province = $_POST['province'] ?? '';
    $note = $_POST['note'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';

    // Tính tổng tiền
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    try {
        // Lưu đơn hàng vào bảng orders
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, email, address, ward, district, province, note, total, payment_method, created_at) 
                               VALUES (:name, :phone, :email, :address, :ward, :district, :province, :note, :total, :payment_method, NOW())");
        $stmt->execute([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'ward' => $ward,
            'district' => $district,
            'province' => $province,
            'note' => $note,
            'total' => $total,
            'payment_method' => $payment_method
        ]);

        $order_id = $pdo->lastInsertId();

        // Lưu chi tiết đơn hàng vào bảng order_details
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_details (order_id, product_id, title, price, quantity) 
                                   VALUES (:order_id, :product_id, :title, :price, :quantity)");
            $stmt->execute([
                'order_id' => $order_id,
                'product_id' => $item['id'],
                'title' => $item['title'],
                'price' => $item['price'],
                'quantity' => $item['quantity']
            ]);
        }

        // Xóa giỏ hàng
        unset($_SESSION['cart']);

        // Chuyển hướng đến trang cảm ơn
        header('Location: thank_you.php');
        exit;
    } catch (PDOException $e) {
        $error = "Lỗi khi đặt hàng: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Website Chính</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .checkout-container { padding: 20px; }
        .customer-info, .order-summary { padding: 15px; }
        .customer-info { background: #f8f9fa; border-radius: 8px; }
        .order-summary { background: #ffffff; border: 1px solid #ddd; border-radius: 8px; }
        .form-group label { font-weight: 500; }
        .order-item { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .order-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .order-item-details { flex-grow: 1; }
        .order-item-title { font-size: 1rem; font-weight: 500; }
        .order-item-quantity { display: flex; align-items: center; gap: 5px; }
        .order-item-quantity input { width: 50px; text-align: center; }
        .order-total { font-size: 1.2rem; font-weight: 600; color: #dc3545; margin-top: 10px; }
        .payment-method { margin: 15px 0; }
        .payment-method label { margin-right: 20px; }
        .btn-place-order { width: 100%; font-size: 1.2rem; }
        .error-message { color: #dc3545; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container checkout-container">
        <h2 class="mb-4">Thanh Toán</h2>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="row">
            <!-- Thông tin khách hàng (70%) -->
            <div class="col-lg-7 col-md-12 mb-4">
                <div class="customer-info">
                    <h4>Thông Tin Khách Hàng</h4>
                    <form id="checkout-form" method="POST" action="">
                        <div class="form-group">
                            <label for="name">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Số Điện Thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="address">Địa Chỉ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="province">Tỉnh/Thành Phố <span class="text-danger">*</span></label>
                            <select class="form-control" id="province" name="province" required>
                                <option value="">Chọn Tỉnh/Thành Phố</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="district">Quận/Huyện <span class="text-danger">*</span></label>
                            <select class="form-control" id="district" name="district" required>
                                <option value="">Chọn Quận/Huyện</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ward">Phường/Xã <span class="text-danger">*</span></label>
                            <select class="form-control" id="ward" name="ward" required>
                                <option value="">Chọn Phường/Xã</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="note">Ghi Chú</label>
                            <textarea class="form-control" id="note" name="note" rows="4"></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Thông tin đơn hàng (30%) -->
            <div class="col-lg-5 col-md-12">
                <div class="order-summary">
                    <h4>Đơn Hàng</h4>
                    <div id="order-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo htmlspecialchars($item['image'] ?: 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="order-item-details">
                                    <div class="order-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div class="order-item-price"><?php echo number_format($item['price'], 0); ?> đ</div>
                                    <div class="order-item-quantity">
                                        <span class="decrease-qty" data-id="<?php echo $item['id']; ?>">-</span>
                                        <input type="number" value="<?php echo $item['quantity']; ?>" min="1" data-id="<?php echo $item['id']; ?>">
                                        <span class="increase-qty" data-id="<?php echo $item['id']; ?>">+</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        Tổng: <span id="total-price"><?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $item) {
                                $total += $item['price'] * $item['quantity'];
                            }
                            echo number_format($total, 0);
                        ?> đ</span>
                    </div>
                    <div class="payment-method">
                        <h5>Phương Thức Thanh Toán</h5>
                        <div>
                            <input type="radio" id="cash" name="payment_method" value="cash" checked>
                            <label for="cash">Tiền Mặt</label>
                        </div>
                        <div>
                            <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                            <label for="bank_transfer">Chuyển Khoản</label>
                        </div>
                    </div>
                    <button type="submit" form="checkout-form" class="btn btn-primary btn-place-order">Đặt Hàng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tải danh sách tỉnh/thành phố
        $(document).ready(function() {
            $.ajax({
                url: 'https://provinces.open-api.vn/api/p/',
                method: 'GET',
                success: function(data) {
                    const provinceSelect = $('#province');
                    data.forEach(province => {
                        provinceSelect.append(`<option value="${province.name}" data-code="${province.code}">${province.name}</option>`);
                    });
                }
            });

            // Tải quận/huyện khi chọn tỉnh/thành phố
            $('#province').change(function() {
                const code = $(this).find(':selected').data('code');
                $('#district').empty().append('<option value="">Chọn Quận/Huyện</option>');
                $('#ward').empty().append('<option value="">Chọn Phường/Xã</option>');
                if (code) {
                    $.ajax({
                        url: `https://provinces.open-api.vn/api/p/${code}?depth=2`,
                        method: 'GET',
                        success: function(data) {
                            data.districts.forEach(district => {
                                $('#district').append(`<option value="${district.name}" data-code="${district.code}">${district.name}</option>`);
                            });
                        }
                    });
                }
            });

            // Tải phường/xã khi chọn quận/huyện
            $('#district').change(function() {
                const code = $(this).find(':selected').data('code');
                $('#ward').empty().append('<option value="">Chọn Phường/Xã</option>');
                if (code) {
                    $.ajax({
                        url: `https://provinces.open-api.vn/api/d/${code}?depth=2`,
                        method: 'GET',
                        success: function(data) {
                            data.wards.forEach(ward => {
                                $('#ward').append(`<option value="${ward.name}">${ward.name}</option>`);
                            });
                        }
                    });
                }
            });

            // Cập nhật số lượng sản phẩm
            $('.decrease-qty').click(function() {
                const id = $(this).data('id');
                const input = $(this).next('input');
                if (parseInt(input.val()) > 1) {
                    input.val(parseInt(input.val()) - 1);
                    updateCartItem(id, input.val());
                }
            });

            $('.increase-qty').click(function() {
                const id = $(this).data('id');
                const input = $(this).prev('input');
                input.val(parseInt(input.val()) + 1);
                updateCartItem(id, input.val());
            });

            function updateCartItem(id, quantity) {
                $.ajax({
                    url: 'update_cart.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id, quantity }),
                    success: function() {
                        updateOrderSummary();
                    }
                });
            }

            function updateOrderSummary() {
                $.ajax({
                    url: 'fetch_cart.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let total = 0;
                        $('#order-items').empty();
                        data.forEach(item => {
                            total += item.price * item.quantity;
                            $('#order-items').append(`
                                <div class="order-item">
                                    <img src="${item.image}" alt="${item.title}">
                                    <div class="order-item-details">
                                        <div class="order-item-title">${item.title}</div>
                                        <div class="order-item-price">${item.price.toLocaleString()} đ</div>
                                        <div class="order-item-quantity">
                                            <span class="decrease-qty" data-id="${item.id}">-</span>
                                            <input type="number" value="${item.quantity}" min="1" data-id="${item.id}">
                                            <span class="increase-qty" data-id="${item.id}">+</span>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });
                        $('#total-price').text(total.toLocaleString() + ' đ');

                        // Gắn lại sự kiện cho các nút mới
                        $('.decrease-qty').off('click').click(function() {
                            const id = $(this).data('id');
                            const input = $(this).next('input');
                            if (parseInt(input.val()) > 1) {
                                input.val(parseInt(input.val()) - 1);
                                updateCartItem(id, input.val());
                            }
                        });
                        $('.increase-qty').off('click').click(function() {
                            const id = $(this).data('id');
                            const input = $(this).prev('input');
                            input.val(parseInt(input.val()) + 1);
                            updateCartItem(id, input.val());
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>