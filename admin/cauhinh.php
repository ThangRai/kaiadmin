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

// Lấy cấu hình từ database
function getSetting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['value'] : $default;
}

// Lưu cấu hình
function saveSetting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
    return $stmt->execute([$key, $value, $value]);
}

// Xử lý form Tổng quan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_general'])) {
    $website_status = isset($_POST['website_status']) ? 1 : 0;
    $scroll_top = isset($_POST['scroll_top']) ? 1 : 0;
    $lock_copy = isset($_POST['lock_copy']) ? 1 : 0;
    $website_name = $_POST['website_name'] ?? '';
    $slogan = $_POST['slogan'] ?? '';
    $description = $_POST['description'] ?? '';

    // Upload favicon
    $favicon = getSetting($pdo, 'favicon');
    if (!empty($_FILES['favicon']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $favicon_name = time() . '_' . basename($_FILES['favicon']['name']);
        $favicon_path = $upload_dir . $favicon_name;
        if (move_uploaded_file($_FILES['favicon']['tmp_name'], $favicon_path)) {
            $favicon = '/kai/admin/uploads/' . $favicon_name;
        }
    }

    // Upload ảnh đại diện
    $og_image = getSetting($pdo, 'og_image');
    if (!empty($_FILES['og_image']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $og_image_name = time() . '_' . basename($_FILES['og_image']['name']);
        $og_image_path = $upload_dir . $og_image_name;
        if (move_uploaded_file($_FILES['og_image']['tmp_name'], $og_image_path)) {
            $og_image = '/kai/admin/uploads/' . $og_image_name;
        }
    }

    saveSetting($pdo, 'website_status', $website_status);
    saveSetting($pdo, 'favicon', $favicon);
    saveSetting($pdo, 'scroll_top', $scroll_top);
    saveSetting($pdo, 'lock_copy', $lock_copy);
    saveSetting($pdo, 'website_name', $website_name);
    saveSetting($pdo, 'slogan', $slogan);
    saveSetting($pdo, 'description', $description);
    saveSetting($pdo, 'og_image', $og_image);

    $_SESSION['toast_message'] = 'Cập nhật cấu hình tổng quan thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=general");
    exit;
}

// Xử lý thêm/sửa liên hệ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_contact'])) {
    $id = !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;
    $link = $_POST['link'];
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;

    // Upload ảnh
    $image = $id ? getSetting($pdo, "contact_image_$id") : null;
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = '/kai/admin/uploads/' . $image_name;
        }
    }

    if ($id) {
        // Sửa
        $sql = "UPDATE contact_info SET link = ?, is_visible = ?";
        $params = [$link, $is_visible];
        if ($image) {
            $sql .= ", image = ?";
            $params[] = $image;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        if ($image) {
            saveSetting($pdo, "contact_image_$id", $image);
        }
    } else {
        // Thêm
        $stmt = $pdo->prepare("INSERT INTO contact_info (image, link, is_visible) VALUES (?, ?, ?)");
        $result = $stmt->execute([$image, $link, $is_visible]);
        if ($result && $image) {
            $new_id = $pdo->lastInsertId();
            saveSetting($pdo, "contact_image_$new_id", $image);
        }
    }

    $_SESSION['toast_message'] = $id ? 'Cập nhật liên hệ thành công!' : 'Thêm liên hệ thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=contact");
    exit;
}

// Xử lý xóa liên hệ
if (isset($_GET['delete_contact_id'])) {
    $id = $_GET['delete_contact_id'];
    $stmt = $pdo->prepare("DELETE FROM contact_info WHERE id = ?");
    $result = $stmt->execute([$id]);
    saveSetting($pdo, "contact_image_$id", '');

    $_SESSION['toast_message'] = $result ? 'Xóa liên hệ thành công!' : 'Xóa liên hệ thất bại!';
    $_SESSION['toast_type'] = $result ? 'success' : 'error';
    header("Location: cauhinh.php?tab=contact");
    exit;
}

// Xử lý bật/tắt liên hệ (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_contact_visible'])) {
    $id = $_POST['id'];
    $is_visible = $_POST['is_visible'];
    $stmt = $pdo->prepare("UPDATE contact_info SET is_visible = ? WHERE id = ?");
    $result = $stmt->execute([(int)$is_visible, $id]);
    echo json_encode(['success' => $result]);
    exit;
}

// Xử lý form Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_email'])) {
    $smtp = [
        'host' => $_POST['smtp_host'],
        'port' => $_POST['smtp_port'],
        'username' => $_POST['smtp_username'],
        'password' => $_POST['smtp_password'],
        'encryption' => $_POST['smtp_encryption'],
    ];
    saveSetting($pdo, 'smtp', json_encode($smtp));

    $_SESSION['toast_message'] = 'Cập nhật cấu hình email thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=email");
    exit;
}

// Xử lý form Màu sắc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_colors'])) {
    $colors = [
        'body' => ['bg' => $_POST['body_bg'], 'text' => $_POST['body_text']],
        'menu' => ['bg' => $_POST['menu_bg'], 'text' => $_POST['menu_text']],
        'top' => ['bg' => $_POST['top_bg'], 'text' => $_POST['top_text']],
        'banner' => ['bg' => $_POST['banner_bg'], 'text' => $_POST['banner_text']],
        'title' => ['bg' => $_POST['title_bg'], 'text' => $_POST['title_text']],
        'footer' => ['bg' => $_POST['footer_bg'], 'text' => $_POST['footer_text']],
        'timeline' => ['bg' => $_POST['timeline_bg'], 'text' => $_POST['timeline_text']],
    ];
    saveSetting($pdo, 'colors', json_encode($colors));

    $_SESSION['toast_message'] = 'Cập nhật màu sắc thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=colors");
    exit;
}

// Xử lý form Phông chữ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_fonts'])) {
    $fonts = [
        'body' => ['family' => $_POST['body_font'], 'style' => $_POST['body_style'], 'size' => $_POST['body_size']],
        'menu' => ['family' => $_POST['menu_font'], 'style' => $_POST['menu_style'], 'size' => $_POST['menu_size']],
        'top' => ['family' => $_POST['top_font'], 'style' => $_POST['top_style'], 'size' => $_POST['top_size']],
        'banner' => ['family' => $_POST['banner_font'], 'style' => $_POST['banner_style'], 'size' => $_POST['banner_size']],
        'title' => ['family' => $_POST['title_font'], 'style' => $_POST['title_style'], 'size' => $_POST['title_size']],
        'footer' => ['family' => $_POST['footer_font'], 'style' => $_POST['footer_style'], 'size' => $_POST['footer_size']],
        'timeline' => ['family' => $_POST['timeline_font'], 'style' => $_POST['timeline_style'], 'size' => $_POST['timeline_size']],
    ];
    saveSetting($pdo, 'fonts', json_encode($fonts));

    $_SESSION['toast_message'] = 'Cập nhật phông chữ thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=fonts");
    exit;
}

// Xử lý form Mã nhúng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_embed'])) {
    $embed_codes = [
        'head' => $_POST['embed_head'],
        'body_start' => $_POST['embed_body_start'],
        'body_end' => $_POST['embed_body_end'],
    ];
    saveSetting($pdo, 'embed_codes', json_encode($embed_codes));

    $_SESSION['toast_message'] = 'Cập nhật mã nhúng thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=embed");
    exit;
}

// Xử lý form Vận chuyển
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_shipping'])) {
    $shipping_config = [
        'inner_cities' => [],
        'inner_city_fees' => [],
        'outer_city_fees' => [],
        'free_shipping_threshold' => $_POST['free_shipping_threshold'] ?? 0
    ];

    // Xử lý khu vực nội thành
    if (!empty($_POST['inner_cities'])) {
        foreach ($_POST['inner_cities'] as $city) {
            $shipping_config['inner_cities'][] = [
                'province_id' => $city['province_id'] ?? '',
                'district_id' => $city['district_id'] ?? '',
                'ward_id' => $city['ward_id'] ?? '',
                'street' => $city['street'] ?? ''
            ];
        }
    }

    // Xử lý phí nội thành
    if (!empty($_POST['inner_city_fees'])) {
        foreach ($_POST['inner_city_fees'] as $fee) {
            if (!empty($fee['amount'])) {
                $shipping_config['inner_city_fees'][] = [
                    'description' => $fee['description'] ?? '',
                    'amount' => (float)$fee['amount']
                ];
            }
        }
    }

    // Xử lý phí ngoại thành
    if (!empty($_POST['outer_city_fees'])) {
        foreach ($_POST['outer_city_fees'] as $fee) {
            if (!empty($fee['amount'])) {
                $shipping_config['outer_city_fees'][] = [
                    'description' => $fee['description'] ?? '',
                    'amount' => (float)$fee['amount']
                ];
            }
        }
    }

    saveSetting($pdo, 'shipping_config', json_encode($shipping_config));

    $_SESSION['toast_message'] = 'Cập nhật cấu hình vận chuyển thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=shipping");
    exit;
}

// Xử lý form Thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment'])) {
    $payment_config = [
        'cod' => isset($_POST['cod_enabled']) ? 1 : 0,
        'bank_transfer' => [
            'enabled' => isset($_POST['bank_transfer_enabled']) ? 1 : 0,
            'bank_name' => $_POST['bank_name'] ?? '',
            'account_number' => $_POST['account_number'] ?? '',
            'account_holder' => $_POST['account_holder'] ?? '',
            'transfer_content' => $_POST['transfer_content'] ?? '',
        ]
    ];

    // Upload mã QR
    $qr_code = getSetting($pdo, 'bank_transfer_qr_code');
    if (!empty($_FILES['qr_code']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $qr_code_name = time() . '_' . basename($_FILES['qr_code']['name']);
        $qr_code_path = $upload_dir . $qr_code_name;
        if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $qr_code_path)) {
            $qr_code = '/kai/admin/uploads/' . $qr_code_name;
        }
    }

    saveSetting($pdo, 'payment_config', json_encode($payment_config));
    saveSetting($pdo, 'bank_transfer_qr_code', $qr_code);

    $_SESSION['toast_message'] = 'Cập nhật cấu hình thanh toán thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=payment");
    exit;
}

// Xử lý form Cấu trúc dữ liệu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_brand_data'])) {
    $brand_name = $_POST['brand_name'] ?? '';
    $address_json = trim($_POST['address_json']);
    $created_at = date('Y-m-d H:i:s');

    // Xử lý upload logo
    $logo = getSetting($pdo, 'brand_data') ? json_decode(getSetting($pdo, 'brand_data'), true)['logo'] ?? '' : '';
    if (!empty($_FILES['logo']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $logo_name = time() . '_' . basename($_FILES['logo']['name']);
        $logo_path = $upload_dir . $logo_name;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
            $logo = '/kai/admin/uploads/' . $logo_name;
        }
    }

    // Chuẩn bị dữ liệu JSON
    $brand_data = [
        'brand_name' => $brand_name,
        'logo' => $logo,
        'address_json' => $address_json,
        'created_at' => $created_at
    ];
    saveSetting($pdo, 'brand_data', json_encode($brand_data));

    $_SESSION['toast_message'] = 'Cập nhật cấu trúc dữ liệu thành công!';
    $_SESSION['toast_type'] = 'success';
    header("Location: cauhinh.php?tab=brand_data");
    exit;
}

// Lấy dữ liệu
$website_status = getSetting($pdo, 'website_status', '1');
$favicon = getSetting($pdo, 'favicon');
$scroll_top = getSetting($pdo, 'scroll_top', '1');
$lock_copy = getSetting($pdo, 'lock_copy', '0');
$website_name = getSetting($pdo, 'website_name', '');
$slogan = getSetting($pdo, 'slogan', '');
$description = getSetting($pdo, 'description', '');
$og_image = getSetting($pdo, 'og_image', '');
$smtp = json_decode(getSetting($pdo, 'smtp', '{}'), true);
$colors = json_decode(getSetting($pdo, 'colors', '{}'), true);
$fonts = json_decode(getSetting($pdo, 'fonts', '{}'), true);
$embed_codes = json_decode(getSetting($pdo, 'embed_codes', '{}'), true);
$shipping_config = json_decode(getSetting($pdo, 'shipping_config', '{}'), true);
$payment_config = json_decode(getSetting($pdo, 'payment_config', '{}'), true);
$bank_transfer_qr_code = getSetting($pdo, 'bank_transfer_qr_code', '');
$brand_data = json_decode(getSetting($pdo, 'brand_data', '{}'), true);
$brand_name = $brand_data['brand_name'] ?? '';
$logo = $brand_data['logo'] ?? '';
$address_json = $brand_data['address_json'] ?? '';
$created_at = $brand_data['created_at'] ?? date('Y-m-d H:i:s', strtotime('2025-06-26 13:47:00 +07:00'));

// Lấy danh sách liên hệ
$stmt = $pdo->query("SELECT * FROM contact_info ORDER BY id DESC");
$contact_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tab hiện tại từ URL
$current_tab = $_GET['tab'] ?? 'general';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
    <title>Cấu hình Website - Kaiadmin</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <!-- iZitoast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <!-- Spectrum Colorpicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css" />
    <style>
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .tab-content {
            padding: 20px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }
        .contact-image, .og-image, .qr-image {
            max-width: 70px;
            height: auto;
        }
        .colorpicker {
            width: 100%;
        }
        .form-group label {
            font-weight: 500;
        }
        .form-header {
            margin-bottom: 20px;
        }
        .form-header .btn-primary {
            padding: 8px 20px;
        }
        .inner-city-group, .fee-group {
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .inner-city-group .form-row, .fee-group .form-row {
            margin-bottom: 10px;
        }
        .remove-btn {
            margin-top: 32px;
        }
        @media (max-width: 768px) {
            .tab-content {
                padding: 10px;
            }
            .table-responsive {
                margin-bottom: 1rem;
            }
            .form-header {
                margin-bottom: 15px;
            }
            .form-header .btn-primary {
                padding: 6px 15px;
                font-size: 0.9rem;
            }
            .remove-btn {
                margin-top: 0;
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
                        <h3 class="fw-bold mb-3">Cấu hình Website</h3>
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
                                <a href="#">Cấu hình</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Cấu hình</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs" id="configTabs">
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'general' ? 'active' : ''; ?>" id="general-tab" data-toggle="tab" href="#general">
                                                <i class="fas fa-cog"></i> Tổng quan
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'contact' ? 'active' : ''; ?>" id="contact-tab" data-toggle="tab" href="#contact">
                                                <i class="fas fa-address-card"></i> Thông tin liên hệ
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'email' ? 'active' : ''; ?>" id="email-tab" data-toggle="tab" href="#email">
                                                <i class="fas fa-envelope"></i> Email
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'colors' ? 'active' : ''; ?>" id="colors-tab" data-toggle="tab" href="#colors">
                                                <i class="fas fa-palette"></i> Màu sắc
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'fonts' ? 'active' : ''; ?>" id="fonts-tab" data-toggle="tab" href="#fonts">
                                                <i class="fas fa-font"></i> Phông chữ
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'embed' ? 'active' : ''; ?>" id="embed-tab" data-toggle="tab" href="#embed">
                                                <i class="fas fa-code"></i> Mã nhúng
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'shipping' ? 'active' : ''; ?>" id="shipping-tab" data-toggle="tab" href="#shipping">
                                                <i class="fas fa-truck"></i> Vận chuyển
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'payment' ? 'active' : ''; ?>" id="payment-tab" data-toggle="tab" href="#payment">
                                                <i class="fas fa-credit-card"></i> Thanh toán
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'brand_data' ? 'active' : ''; ?>" id="brand-data-tab" data-toggle="tab" href="#brand_data">
                                                <i class="fas fa-database"></i> Cấu trúc dữ liệu
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="configTabsContent">
                                        <!-- Tab Tổng quan -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'general' ? 'show active' : ''; ?>" id="general">
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="form-header d-flex justify-content-end">
                                                    <button type="submit" name="save_general" class="btn btn-primary">
                                                        <i class="fas fa-save"></i> Lưu
                                                    </button>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tên Website</label>
                                                    <input type="text" name="website_name" class="form-control" value="<?php echo htmlspecialchars($website_name); ?>" placeholder="Nhập tên website">
                                                </div>
                                                <div class="form-group">
                                                    <label>Slogan</label>
                                                    <input type="text" name="slogan" class="form-control" value="<?php echo htmlspecialchars($slogan); ?>" placeholder="Nhập slogan">
                                                </div>
                                                <div class="form-group">
                                                    <label>Mô tả Website</label>
                                                    <textarea name="description" class="form-control" rows="4" placeholder="Nhập mô tả website"><?php echo htmlspecialchars($description); ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Ảnh đại diện (OG Image)</label>
                                                    <input type="file" name="og_image" class="form-control" accept="image/*">
                                                    <?php if ($og_image): ?>
                                                        <img src="<?php echo htmlspecialchars($og_image); ?>" alt="OG Image" class="og-image mt-2">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Favicon Website</label>
                                                    <input type="file" name="favicon" class="form-control" accept="image/*">
                                                    <?php if ($favicon): ?>
                                                        <img src="<?php echo htmlspecialchars($favicon); ?>" alt="Favicon" class="mt-2" style="max-width: 50px;">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Bật/Tắt Website</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="website_status" class="form-check-input" <?php echo $website_status ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Bật website</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nút cuộn lên đầu trang</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="scroll_top" class="form-check-input" <?php echo $scroll_top ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Bật nút cuộn</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Khóa copy nội dung</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="lock_copy" class="form-check-input" <?php echo $lock_copy ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Bật khóa copy</label>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- Tab Thông tin liên hệ -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'contact' ? 'show active' : ''; ?>" id="contact">
                                            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addContactModal">
                                                <i class="fas fa-plus"></i> Thêm liên hệ
                                            </button>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Ảnh</th>
                                                            <th>Liên kết</th>
                                                            <th>Hiển thị</th>
                                                            <th>Hành động</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($contact_info as $contact): ?>
                                                            <tr>
                                                                <td><?php echo $contact['id']; ?></td>
                                                                <td>
                                                                    <?php if ($contact['image']): ?>
                                                                        <img src="<?php echo htmlspecialchars($contact['image']); ?>" class="contact-image" alt="Contact">
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($contact['link']); ?></td>
                                                                <td>
                                                                    <i class="fas toggle-visibility <?php echo $contact['is_visible'] ? 'fa-eye text-success' : 'fa-eye-slash text-danger'; ?>" 
                                                                       data-id="<?php echo $contact['id']; ?>" 
                                                                       data-visible="<?php echo $contact['is_visible']; ?>"></i>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-warning edit-contact" data-id="<?php echo $contact['id']; ?>" data-toggle="modal" data-target="#editContactModal">Sửa</button>
                                                                    <a href="cauhinh.php?delete_contact_id=<?php echo $contact['id']; ?>&tab=contact" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- Tab Email -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'email' ? 'show active' : ''; ?>" id="email">
                                            <form method="POST">
                                                <div class="form-header d-flex justify-content-end">
<button type="submit" name="save_email" class="btn btn-primary">
    <i class="fas fa-save"></i> Lưu
</button>
                                                </div>
                                                <div class="form-group">
                                                    <label>SMTP Host</label>
                                                    <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($smtp['host'] ?? ''); ?>" placeholder="smtp.gmail.com">
                                                </div>
                                                <div class="form-group">
                                                    <label>SMTP Port</label>
                                                    <input type="number" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($smtp['port'] ?? ''); ?>" placeholder="587">
                                                </div>
                                                <div class="form-group">
                                                    <label>SMTP Username</label>
                                                    <input type="text" name="smtp_username" class="form-control" value="<?php echo htmlspecialchars($smtp['username'] ?? ''); ?>" placeholder="your-email@gmail.com">
                                                </div>
                                                <div class="form-group">
                                                    <label>SMTP Password</label>
                                                    <input type="password" name="smtp_password" class="form-control" value="<?php echo htmlspecialchars($smtp['password'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>SMTP Encryption</label>
                                                    <select name="smtp_encryption" class="form-control">
                                                        <option value="none" <?php echo ($smtp['encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>Không mã hóa</option>
                                                        <option value="tls" <?php echo ($smtp['encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                                        <option value="ssl" <?php echo ($smtp['encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- Tab Màu sắc -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'colors' ? 'show active' : ''; ?>" id="colors">
                                            <form method="POST">
                                                <div class="form-header d-flex justify-content-end">
<button type="submit" name="save_colors" class="btn btn-primary">
    <i class="fas fa-save"></i> Lưu
</button>
                                                </div>
                                                <?php
                                                $elements = ['body', 'menu', 'top', 'banner', 'title', 'footer', 'timeline'];
                                                foreach ($elements as $element): ?>
                                                    <div class="form-group">
                                                        <label><?php echo ucfirst($element); ?></label>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Màu nền</label>
                                                                <input type="text" name="<?php echo $element; ?>_bg" class="form-control colorpicker" value="<?php echo htmlspecialchars($colors[$element]['bg'] ?? '#ffffff'); ?>">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Màu chữ</label>
                                                                <input type="text" name="<?php echo $element; ?>_text" class="form-control colorpicker" value="<?php echo htmlspecialchars($colors[$element]['text'] ?? '#000000'); ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </form>
                                        </div>
                                        <!-- Tab Phông chữ -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'fonts' ? 'show active' : ''; ?>" id="fonts">
                                            <form method="POST">
                                                <div class="form-header d-flex justify-content-end">
                                                    <button type="submit" name="save_fonts" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                                                </div>
                                                <?php
                                                $font_families = ['Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana', 'Georgia', 'Palatino', 'Garamond', 'Public Sans'];
                                                foreach ($elements as $element): ?>
                                                    <div class="form-group">
                                                        <label><?php echo ucfirst($element); ?></label>
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Phông chữ</label>
                                                                <select name="<?php echo $element; ?>_font" class="form-control">
                                                                    <?php foreach ($font_families as $font): ?>
                                                                        <option value="<?php echo $font; ?>" <?php echo ($fonts[$element]['family'] ?? '') === $font ? 'selected' : ''; ?>><?php echo $font; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Kiểu chữ</label>
                                                                <select name="<?php echo $element; ?>_style" class="form-control">
                                                                    <option value="normal" <?php echo ($fonts[$element]['style'] ?? '') === 'normal' ? 'selected' : ''; ?>>Bình thường</option>
                                                                    <option value="bold" <?php echo ($fonts[$element]['style'] ?? '') === 'bold' ? 'selected' : ''; ?>>Đậm</option>
                                                                    <option value="italic" <?php echo ($fonts[$element]['style'] ?? '') === 'italic' ? 'selected' : ''; ?>>Nghiêng</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Kích cỡ (px)</label>
                                                                <input type="number" name="<?php echo $element; ?>_size" class="form-control" value="<?php echo htmlspecialchars($fonts[$element]['size'] ?? '16'); ?>" min="8" max="100">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </form>
                                        </div>
                                        <!-- Tab Mã nhúng -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'embed' ? 'show active' : ''; ?>" id="embed">
                                            <form method="POST">
                                                <div class="form-header d-flex justify-content-end">
                                                    <button type="submit" name="save_embed" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                                                </div>
                                                <div class="form-group">
                                                    <label>Mã nhúng đầu trang (<head>)</label>
                                                    <textarea name="embed_head" class="form-control" rows="5"><?php echo htmlspecialchars($embed_codes['head'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Mã nhúng sau <body></label>
                                                    <textarea name="embed_body_start" class="form-control" rows="5"><?php echo htmlspecialchars($embed_codes['body_start'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Mã nhúng cuối trang (</body>)</label>
                                                    <textarea name="embed_body_end" class="form-control" rows="5"><?php echo htmlspecialchars($embed_codes['body_end'] ?? ''); ?></textarea>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- Tab Vận chuyển -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'shipping' ? 'show active' : ''; ?>" id="shipping">
                                            <form method="POST" id="shipping-form">
                                                <div class="form-header d-flex justify-content-end">
                                                    <button type="submit" name="save_shipping" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                                                </div>
                                                <!-- Khu vực nội thành -->
                                                <div class="form-group">
                                                    <label>Khu vực nội thành</label>
                                                    <div id="inner-cities-container">
                                                        <?php 
                                                        $inner_cities = $shipping_config['inner_cities'] ?? [];
                                                        if (empty($inner_cities)) {
                                                            $inner_cities[] = ['province_id' => '', 'district_id' => '', 'ward_id' => '', 'street' => ''];
                                                        }
                                                        foreach ($inner_cities as $index => $city): ?>
                                                            <div class="inner-city-group" data-province-id="<?php echo htmlspecialchars($city['province_id']); ?>" data-district-id="<?php echo htmlspecialchars($city['district_id']); ?>" data-ward-id="<?php echo htmlspecialchars($city['ward_id']); ?>">
                                                                <div class="form-row">
                                                                    <div class="col-md-3">
                                                                        <label>Tỉnh/Thành</label>
                                                                        <select name="inner_cities[<?php echo $index; ?>][province_id]" class="form-control province-select">
                                                                            <option value="">Chọn tỉnh/thành</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label>Quận/Huyện</label>
                                                                        <select name="inner_cities[<?php echo $index; ?>][district_id]" class="form-control district-select" disabled>
                                                                            <option value="">Chọn quận/huyện</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label>Phường/Xã</label>
                                                                        <select name="inner_cities[<?php echo $index; ?>][ward_id]" class="form-control ward-select" disabled>
                                                                            <option value="">Chọn phường/xã</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <label>Số nhà</label>
                                                                        <input type="text" name="inner_cities[<?php echo $index; ?>][street]" class="form-control" value="<?php echo htmlspecialchars($city['street']); ?>">
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <button type="button" class="btn btn-danger remove-btn remove-city">Xóa</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <button type="button" class="btn btn-secondary mt-2" id="add-city">Thêm khu vực</button>
                                                </div>
                                                <!-- Phí nội thành -->
                                                <div class="form-group">
                                                    <label>Phí vận chuyển nội thành</label>
                                                    <div id="inner-city-fees-container">
                                                        <?php 
                                                        $inner_fees = $shipping_config['inner_city_fees'] ?? [];
                                                        if (empty($inner_fees)) {
                                                            $inner_fees[] = ['description' => '', 'amount' => ''];
                                                        }
                                                        foreach ($inner_fees as $index => $fee): ?>
                                                            <div class="fee-group">
                                                                <div class="form-row">
                                                                    <div class="col-md-6">
                                                                        <label>Mô tả</label>
                                                                        <input type="text" name="inner_city_fees[<?php echo $index; ?>][description]" class="form-control" value="<?php echo htmlspecialchars($fee['description']); ?>">
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <label>Giá (VNĐ)</label>
                                                                        <input type="number" name="inner_city_fees[<?php echo $index; ?>][amount]" class="form-control" value="<?php echo htmlspecialchars($fee['amount']); ?>" min="0">
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <button type="button" class="btn btn-danger remove-btn remove-fee">Xóa</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <button type="button" class="btn btn-secondary mt-2" id="add-inner-fee">Thêm phí nội thành</button>
                                                </div>
                                                <!-- Phí ngoại thành -->
                                                <div class="form-group">
                                                    <label>Phí vận chuyển ngoại thành</label>
                                                    <div id="outer-city-fees-container">
                                                        <?php 
                                                        $outer_fees = $shipping_config['outer_city_fees'] ?? [];
                                                        if (empty($outer_fees)) {
                                                            $outer_fees[] = ['description' => '', 'amount' => ''];
                                                        }
                                                        foreach ($outer_fees as $index => $fee): ?>
                                                            <div class="fee-group">
                                                                <div class="form-row">
                                                                    <div class="col-md-6">
                                                                        <label>Mô tả</label>
                                                                        <input type="text" name="outer_city_fees[<?php echo $index; ?>][description]" class="form-control" value="<?php echo htmlspecialchars($fee['description']); ?>">
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <label>Giá (VNĐ)</label>
                                                                        <input type="number" name="outer_city_fees[<?php echo $index; ?>][amount]" class="form-control" value="<?php echo htmlspecialchars($fee['amount']); ?>" min="0">
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <button type="button" class="btn btn-danger remove-btn remove-fee">Xóa</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <button type="button" class="btn btn-secondary mt-2" id="add-outer-fee">Thêm phí ngoại thành</button>
                                                </div>
                                                <!-- Miễn phí vận chuyển -->
                                                <div class="form-group">
                                                    <label>Miễn phí vận chuyển nếu đơn hàng lớn hơn (VNĐ)</label>
                                                    <input type="number" name="free_shipping_threshold" class="form-control" value="<?php echo htmlspecialchars($shipping_config['free_shipping_threshold'] ?? '0'); ?>" min="0">
                                                </div>
                                            </form>
                                        </div>
                                        <!-- Tab Thanh toán -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'payment' ? 'show active' : ''; ?>" id="payment">
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="form-header d-flex justify-content-end">
                                                    <button type="submit" name="save_payment" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                                                </div>
                                                <div class="form-group">
                                                    <label>Thanh toán khi nhận hàng (COD)</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="cod_enabled" class="form-check-input" <?php echo ($payment_config['cod'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Bật COD</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Chuyển khoản ngân hàng</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="bank_transfer_enabled" class="form-check-input" <?php echo ($payment_config['bank_transfer']['enabled'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Bật chuyển khoản ngân hàng</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tên ngân hàng</label>
                                                    <input type="text" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($payment_config['bank_transfer']['bank_name'] ?? ''); ?>" placeholder="Nhập tên ngân hàng">
                                                </div>
                                                <div class="form-group">
                                                    <label>Số tài khoản</label>
                                                    <input type="text" name="account_number" class="form-control" value="<?php echo htmlspecialchars($payment_config['bank_transfer']['account_number'] ?? ''); ?>" placeholder="Nhập số tài khoản">
                                                </div>
                                                <div class="form-group">
                                                    <label>Chủ tài khoản</label>
                                                    <input type="text" name="account_holder" class="form-control" value="<?php echo htmlspecialchars($payment_config['bank_transfer']['account_holder'] ?? ''); ?>" placeholder="Nhập tên chủ tài khoản">
                                                </div>
                                                <div class="form-group">
                                                    <label>Nội dung chuyển khoản</label>
                                                    <input type="text" name="transfer_content" class="form-control" value="<?php echo htmlspecialchars($payment_config['bank_transfer']['transfer_content'] ?? ''); ?>" placeholder="Nhập nội dung chuyển khoản">
                                                </div>
                                                <div class="form-group">
                                                    <label>Mã QR thanh toán</label>
                                                    <input type="file" name="qr_code" class="form-control" accept="image/*">
                                                    <?php if ($bank_transfer_qr_code): ?>
                                                        <img src="<?php echo htmlspecialchars($bank_transfer_qr_code); ?>" alt="QR Code" class="qr-image mt-2">
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- Tab Cấu trúc dữ liệu -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'brand_data' ? 'show active' : ''; ?>" id="brand_data">
                                            <?php
                                            // Lấy dữ liệu hiện tại
                                            $brand_data = json_decode(getSetting($pdo, 'brand_data', '{}'), true);
                                            $brand_name = $brand_data['brand_name'] ?? '';
                                            $logo = $brand_data['logo'] ?? '';
                                            $address_json = $brand_data['address_json'] ?? '';
                                            $created_at = $brand_data['created_at'] ?? date('Y-m-d H:i:s');
                                            ?>

                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="form-header d-flex justify-content-end">
                                                    <button type="submit" name="save_brand_data" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tên thương hiệu</label>
                                                    <input type="text" name="brand_name" class="form-control" value="<?php echo htmlspecialchars($brand_name); ?>" placeholder="Nhập tên thương hiệu" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Logo thương hiệu</label>
                                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                                    <?php if ($logo): ?>
                                                        <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo" class="mt-2" style="max-width: 100px;">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Mã dữ liệu địa chỉ (JSON)</label>
                                                    <textarea name="address_json" class="form-control" rows="6" placeholder='{"@type": "PostalAddress", "streetAddress": "561A, Điện Biên Phủ, Phường 26, Quận Bình Thạnh", "addressLocality": "Quận Bình Thạnh", "addressRegion": "Thành Phố Hồ Chí Minh", "postalCode": "700000", "addressCountry": "VN"}' required><?php echo htmlspecialchars($address_json); ?></textarea>
                                                    <small class="form-text text-muted">Nhập theo định dạng JSON của Schema.org (PostalAddress).</small>
                                                </div>
                                                <div class="form-group">
                                                    <label>Ngày tạo</label>
                                                    <input type="text" name="created_at" class="form-control" value="<?php echo htmlspecialchars($created_at); ?>" readonly>
                                                    <small class="form-text text-muted">Ngày tạo sẽ được cập nhật khi lưu.</small>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm liên hệ -->
    <div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addContactModalLabel">Thêm liên hệ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Ảnh</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Liên kết</label>
                            <input type="text" name="link" class="form-control" required placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_visible" class="form-check-input" checked> Hiển thị
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" name="save_contact" class="btn btn-primary">Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa liên hệ -->
    <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editContactModalLabel">Sửa liên hệ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="contact_id" id="edit_contact_id">
                        <div class="form-group">
                            <label>Ảnh</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <img id="edit_contact_image" class="contact-image mt-2" style="display: none;" alt="Contact">
                        </div>
                        <div class="form-group">
                            <label>Liên kết</label>
                            <input type="text" name="link" id="edit_contact_link" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_visible" id="edit_contact_visible" class="form-check-input"> Hiển thị
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" name="save_contact" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'include/custom-template.php'; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.js"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo colorpicker
            $(".colorpicker").spectrum({
                showInput: true,
                showPalette: false,
                showButtons: false,
                preferredFormat: "hex",
                showInitial: true,
                clickoutFiresChange: true
            });

            // Xử lý bật/tắt liên hệ
            $('.toggle-visibility').on('click', function() {
                var $this = $(this);
                var id = $this.data('id');
                var is_visible = $this.data('visible') ? 0 : 1;

                $.ajax({
                    url: 'cauhinh.php',
                    method: 'POST',
                    data: { toggle_contact_visible: true, id: id, is_visible: is_visible },
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

            // Xử lý modal sửa liên hệ
            $('.edit-contact').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: 'fetch_contact.php',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(data) {
                        $('#edit_contact_id').val(data.id);
                        $('#edit_contact_link').val(data.link);
                        $('#edit_contact_visible').prop('checked', data.is_visible == 1);
                        if (data.image) {
                            $('#edit_contact_image').attr('src', data.image).show();
                        } else {
                            $('#edit_contact_image').hide();
                        }
                    },
                    error: function() {
                        iziToast.error({
                            title: 'Lỗi',
                            message: 'Không thể tải dữ liệu liên hệ',
                            position: 'topRight'
                        });
                    }
                });
            });

            // Xử lý thêm khu vực nội thành
            let cityIndex = <?php echo count($inner_cities); ?>;
            $('#add-city').on('click', function() {
                const newCityHtml = `
                    <div class="inner-city-group">
                        <div class="form-row">
                            <div class="col-md-3">
                                <label>Tỉnh/Thành</label>
                                <select name="inner_cities[${cityIndex}][province_id]" class="form-control province-select">
                                    <option value="">Chọn tỉnh/thành</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Quận/Huyện</label>
                                <select name="inner_cities[${cityIndex}][district_id]" class="form-control district-select" disabled>
                                    <option value="">Chọn quận/huyện</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Phường/Xã</label>
                                <select name="inner_cities[${cityIndex}][ward_id]" class="form-control ward-select" disabled>
                                    <option value="">Chọn phường/xã</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Số nhà</label>
                                <input type="text" name="inner_cities[${cityIndex}][street]" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-btn remove-city">Xóa</button>
                            </div>
                        </div>
                    </div>`;
                $('#inner-cities-container').append(newCityHtml);
                loadProvinces($(`select[name="inner_cities[${cityIndex}][province_id]"]`));
                cityIndex++;
            });

            // Xử lý xóa khu vực nội thành
            $(document).on('click', '.remove-city', function() {
                if ($('.inner-city-group').length > 1) {
                    $(this).closest('.inner-city-group').remove();
                } else {
                    iziToast.warning({
                        title: 'Cảnh báo',
                        message: 'Phải có ít nhất một khu vực nội thành',
                        position: 'topRight'
                    });
                }
            });

            // Xử lý thêm phí nội thành
            let innerFeeIndex = <?php echo count($inner_fees); ?>;
            $('#add-inner-fee').on('click', function() {
                const newFeeHtml = `
                    <div class="fee-group">
                        <div class="form-row">
                            <div class="col-md-6">
                                <label>Mô tả</label>
                                <input type="text" name="inner_city_fees[${innerFeeIndex}][description]" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label>Giá (VNĐ)</label>
                                <input type="number" name="inner_city_fees[${innerFeeIndex}][amount]" class="form-control" min="0">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-btn remove-fee">Xóa</button>
                            </div>
                        </div>
                    </div>`;
                $('#inner-city-fees-container').append(newFeeHtml);
                innerFeeIndex++;
            });

            // Xử lý thêm phí ngoại thành
            let outerFeeIndex = <?php echo count($outer_fees); ?>;
            $('#add-outer-fee').on('click', function() {
                const newFeeHtml = `
                    <div class="fee-group">
                        <div class="form-row">
                            <div class="col-md-6">
                                <label>Mô tả</label>
                                <input type="text" name="outer_city_fees[${outerFeeIndex}][description]" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label>Giá (VNĐ)</label>
                                <input type="number" name="outer_city_fees[${outerFeeIndex}][amount]" class="form-control" min="0">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-btn remove-fee">Xóa</button>
                            </div>
                        </div>
                    </div>`;
                $('#outer-city-fees-container').append(newFeeHtml);
                outerFeeIndex++;
            });

            // Xử lý xóa phí
            $(document).on('click', '.remove-fee', function() {
                if ($(this).closest('.fee-group').siblings('.fee-group').length > 0) {
                    $(this).closest('.fee-group').remove();
                } else {
                    iziToast.warning({
                        title: 'Cảnh báo',
                        message: 'Phải có ít nhất một mức phí',
                        position: 'topRight'
                    });
                }
            });

            // Tải danh sách tỉnh/thành
            function loadProvinces($select, selectedId = '') {
                $.ajax({
                    url: 'https://provinces.open-api.vn/api/p/',
                    method: 'GET',
                    async: false,
                    success: function(data) {
                        $select.empty().append('<option value="">Chọn tỉnh/thành</option>');
                        data.forEach(province => {
                            $select.append(`<option value="${province.code}" ${selectedId == province.code ? 'selected' : ''}>${province.name}</option>`);
                        });
                    },
                    error: function() {
                        iziToast.error({
                            title: 'Lỗi',
                            message: 'Không thể tải danh sách tỉnh/thành',
                            position: 'topRight'
                        });
                    }
                });
            }

            // Tải danh sách quận/huyện
            function loadDistricts($provinceSelect, $districtSelect, selectedId = '') {
                const provinceId = $provinceSelect.val();
                $districtSelect.empty().append('<option value="">Chọn quận/huyện</option>').prop('disabled', true);
                if (provinceId) {
                    $.ajax({
                        url: `https://provinces.open-api.vn/api/p/${provinceId}?depth=2`,
                        method: 'GET',
                        async: false,
                        success: function(data) {
                            $districtSelect.prop('disabled', false);
                            data.districts.forEach(district => {
                                $districtSelect.append(`<option value="${district.code}" ${selectedId == district.code ? 'selected' : ''}>${district.name}</option>`);
                            });
                        },
                        error: function() {
                            iziToast.error({
                                title: 'Lỗi',
                                message: 'Không thể tải danh sách quận/huyện',
                                position: 'topRight'
                            });
                        }
                    });
                }
            }

            // Tải danh sách phường/xã
            function loadWards($districtSelect, $wardSelect, selectedId = '') {
                const districtId = $districtSelect.val();
                $wardSelect.empty().append('<option value="">Chọn phường/xã</option>').prop('disabled', true);
                if (districtId) {
                    $.ajax({
                        url: `https://provinces.open-api.vn/api/d/${districtId}?depth=2`,
                        method: 'GET',
                        async: false,
                        success: function(data) {
                            $wardSelect.prop('disabled', false);
                            data.wards.forEach(ward => {
                                $wardSelect.append(`<option value="${ward.code}" ${selectedId == ward.code ? 'selected' : ''}>${ward.name}</option>`);
                            });
                        },
                        error: function() {
                            iziToast.error({
                                title: 'Lỗi',
                                message: 'Không thể tải danh sách phường/xã',
                                position: 'topRight'
                            });
                        }
                    });
                }
            }

            // Khởi tạo danh sách tỉnh/thành cho tất cả select
            $('.inner-city-group').each(function() {
                const $cityGroup = $(this);
                const $provinceSelect = $cityGroup.find('.province-select');
                const $districtSelect = $cityGroup.find('.district-select');
                const $wardSelect = $cityGroup.find('.ward-select');

                const provinceId = $cityGroup.data('province-id') || '';
                const districtId = $cityGroup.data('district-id') || '';
                const wardId = $cityGroup.data('ward-id') || '';

                // Tải tỉnh/thành và gán giá trị đã chọn
                loadProvinces($provinceSelect, provinceId);

                // Tải quận/huyện nếu có provinceId
                if (provinceId) {
                    loadDistricts($provinceSelect, $districtSelect, districtId);
                }

                // Tải phường/xã nếu có districtId
                if (districtId) {
                    loadWards($districtSelect, $wardSelect, wardId);
                }
            });

            // Xử lý thay đổi tỉnh/thành
            $(document).on('change', '.province-select', function() {
                const $cityGroup = $(this).closest('.inner-city-group');
                const $districtSelect = $cityGroup.find('.district-select');
                const $wardSelect = $cityGroup.find('.ward-select');
                loadDistricts($(this), $districtSelect);
                $wardSelect.empty().append('<option value="">Chọn phường/xã</option>').prop('disabled', true);
            });

            // Xử lý thay đổi quận/huyện
            $(document).on('change', '.district-select', function() {
                const $cityGroup = $(this).closest('.inner-city-group');
                const $wardSelect = $cityGroup.find('.ward-select');
                loadWards($(this), $wardSelect);
            });

            

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
        });
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>