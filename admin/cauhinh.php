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

    saveSetting($pdo, 'website_status', $website_status);
    saveSetting($pdo, 'favicon', $favicon);
    saveSetting($pdo, 'scroll_top', $scroll_top);
    saveSetting($pdo, 'lock_copy', $lock_copy);

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

// Lấy dữ liệu
$website_status = getSetting($pdo, 'website_status', '1');
$favicon = getSetting($pdo, 'favicon');
$scroll_top = getSetting($pdo, 'scroll_top', '1');
$lock_copy = getSetting($pdo, 'lock_copy', '0');
$smtp = json_decode(getSetting($pdo, 'smtp', '{}'), true);
$colors = json_decode(getSetting($pdo, 'colors', '{}'), true);
$fonts = json_decode(getSetting($pdo, 'fonts', '{}'), true);
$embed_codes = json_decode(getSetting($pdo, 'embed_codes', '{}'), true);

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
    <!-- Bootstrap Colorpicker CSS -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.4.0/css/bootstrap-colorpicker.min.css"> -->

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
        .contact-image {
            max-width: 100px;
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
                                            <a class="nav-link <?php echo $current_tab === 'general' ? 'active' : ''; ?>" id="general-tab" data-toggle="tab" href="#general">Tổng quan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'contact' ? 'active' : ''; ?>" id="contact-tab" data-toggle="tab" href="#contact">Thông tin liên hệ</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'email' ? 'active' : ''; ?>" id="email-tab" data-toggle="tab" href="#email">Email</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'colors' ? 'active' : ''; ?>" id="colors-tab" data-toggle="tab" href="#colors">Màu sắc</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'fonts' ? 'active' : ''; ?>" id="fonts-tab" data-toggle="tab" href="#fonts">Phông chữ</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $current_tab === 'embed' ? 'active' : ''; ?>" id="embed-tab" data-toggle="tab" href="#embed">Mã nhúng</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="configTabsContent">
                                        <!-- Tab Tổng quan -->
                                        <div class="tab-pane fade <?php echo $current_tab === 'general' ? 'show active' : ''; ?>" id="general">
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="form-header d-flex justify-content-end">
                                                    <button type="submit" name="save_general" class="btn btn-primary">Lưu</button>
                                                </div>
                                                <div class="form-group">
                                                    <label>Bật/Tắt Website</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="website_status" class="form-check-input" <?php echo $website_status ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Bật website</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Favicon Website</label>
                                                    <input type="file" name="favicon" class="form-control" accept="image/*">
                                                    <?php if ($favicon): ?>
                                                        <img src="<?php echo htmlspecialchars($favicon); ?>" alt="Favicon" style="max-width: 50px; margin-top: 10px;">
                                                    <?php endif; ?>
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
                                            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addContactModal">Thêm liên hệ</button>
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
                                                    <button type="submit" name="save_email" class="btn btn-primary">Lưu</button>
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
                                                        <option value="none" <?php echo ($smtp['encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
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
                                                    <button type="submit" name="save_colors" class="btn btn-primary">Lưu</button>
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
                                                    <button type="submit" name="save_fonts" class="btn btn-primary">Lưu</button>
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
                                                    <button type="submit" name="save_embed" class="btn btn-primary">Lưu</button>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

</div> <!-- Close container -->
<?php include 'include/footer.php'; ?>
</div> <!-- Close main-panel -->
</div> <!-- Close wrapper -->

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