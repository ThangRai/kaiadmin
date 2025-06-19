<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../database/config.php';

require_once(__DIR__ . '/functions.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// File log để debug
// $log_file = 'debug_sidebar.log';
// function log_debug($message) {
//     global $log_file;
//     file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
// }

// Kiểm tra kết nối CSDL
if (!$pdo) {
    log_debug("Database connection failed");
    $_SESSION['toast_message'] = 'Không thể kết nối đến cơ sở dữ liệu!';
    $_SESSION['toast_type'] = 'error';
    header("Location: login.php");
    exit;
}

$fullname = $_SESSION['fullname'] ?? 'Người dùng';
$avatar = $_SESSION['avatar'] ?? 'default.png';
$email = $_SESSION['email'] ?? 'Chưa cập nhật';
$user_id = $_SESSION['user_id'];

$current_page = basename($_SERVER['PHP_SELF']);

// Lấy tất cả menu items có is_visible = 1 và user có quyền can_view = 1
$stmt = $pdo->prepare("
    SELECT m.*, p.can_view 
    FROM menu_items m
    LEFT JOIN user_menu_permissions p ON m.id = p.menu_item_id AND p.user_id = ?
    WHERE m.is_visible = 1 AND p.can_view = 1
    ORDER BY m.parent_id ASC, m.position ASC
");
$stmt->execute([$user_id]);
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
log_debug('Menu items fetched for user_id ' . $user_id . ': ' . print_r($menu_items, true));

// Tổ chức menu thành danh sách cha-con
$parent_items = [];
$child_items = [];
foreach ($menu_items as $item) {
    if ($item['parent_id'] === null) {
        $parent_items[$item['id']] = $item;
    } else {
        $child_items[$item['parent_id']][] = $item;
    }
}
log_debug('Parent items: ' . print_r($parent_items, true));
log_debug('Child items: ' . print_r($child_items, true));

// Kiểm tra nếu không có menu nào
if (empty($parent_items)) {
    log_debug("No menu items available for user_id: $user_id");
}
?>
<style>
    .sidebar .nav-collapse li a .sub-item, .sidebar[data-background-color=white] .nav-collapse li a .sub-item {
    margin-left: 40px !important;
}
    .sidebar .nav-collapse li a .sub-item:before, .sidebar[data-background-color=white] .nav-collapse li a .sub-item:before {
    height: 18px !important;
    width: 18px !important;
    left: -37px !important;
}
</style>

<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="index.php" class="logo">
                <img src="assets/img/kaiadmin/logo_light.svg" alt="thương hiệu navbar" class="navbar-brand" height="20" />
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
                <?php if (empty($parent_items)): ?>
                    <li class="nav-item">
                        <p class="text-muted">Không có menu nào được phân quyền.</p>
                    </li>
                <?php else: ?>
                    <?php foreach ($parent_items as $parent): ?>
                        <?php if ($parent['is_section']): ?>
                            <li class="nav-section">
                                <span class="sidebar-mini-icon">
                                    <i class="<?php echo htmlspecialchars($parent['icon'] ?? ''); ?>"></i>
                                </span>
                                <h4 class="text-section"><?php echo htmlspecialchars($parent['name']); ?></h4>
                            </li>
                        <?php else: ?>
                            <?php
                            $has_children = isset($child_items[$parent['id']]) && count($child_items[$parent['id']]) > 0;
                            $is_active = ($current_page === ($parent['url'] ?? ''));
                            if ($has_children) {
                                foreach ($child_items[$parent['id']] as $child) {
                                    if ($current_page === ($child['url'] ?? '')) {
                                        $is_active = true;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <li class="nav-item <?php echo $is_active ? 'active' : ''; ?>">
                                <a
                                    <?php if ($has_children): ?>
                                        data-bs-toggle="collapse"
                                        href="#menu_<?php echo $parent['id']; ?>"
                                        class="<?php echo $is_active ? '' : 'collapsed'; ?>"
                                        aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>"
                                    <?php else: ?>
                                        href="<?php echo htmlspecialchars($parent['url'] ?? '#'); ?>"
                                    <?php endif; ?>
                                >
                                    <?php if ($parent['icon']): ?>
                                        <i class="<?php echo htmlspecialchars($parent['icon']); ?>"></i>
                                    <?php endif; ?>
                                    <p><?php echo htmlspecialchars($parent['name']); ?></p>
                                    <?php if ($has_children): ?>
                                        <span class="caret"></span>
                                    <?php endif; ?>
                                    <?php if ($parent['badge']): ?>
                                        <span class="badge"><?php echo htmlspecialchars($parent['badge']); ?></span>
                                    <?php endif; ?>
                                </a>
                                <?php if ($has_children): ?>
                                    <div class="collapse <?php echo $is_active ? 'show' : ''; ?>" id="menu_<?php echo $parent['id']; ?>">
                                        <ul class="nav nav-collapse">
                                            <?php foreach ($child_items[$parent['id']] as $child): ?>
                                                <li class="<?php echo $current_page === ($child['url'] ?? '') ? 'active' : ''; ?>">
                                                    <a href="<?php echo htmlspecialchars($child['url'] ?? '#'); ?>">
                                                        <span class="sub-item"><?php echo htmlspecialchars($child['name']); ?></span>
                                                        <?php if ($child['badge']): ?>
                                                            <span class="badge"><?php echo htmlspecialchars($child['badge']); ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php ob_end_flush(); ?>