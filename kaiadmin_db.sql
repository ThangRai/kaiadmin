-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th6 19, 2025 lúc 07:13 AM
-- Phiên bản máy phục vụ: 8.4.3
-- Phiên bản PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `kaiadmin_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `title_vi` varchar(255) NOT NULL,
  `parent_id` int DEFAULT '0',
  `module_id` int NOT NULL,
  `display_position` text,
  `is_active` tinyint(1) DEFAULT '1',
  `h1_content` varchar(255) DEFAULT NULL,
  `description_vi` text,
  `content_vi` text,
  `slug_vi` varchar(255) DEFAULT NULL,
  `link_vi` varchar(255) DEFAULT NULL,
  `link_target` varchar(10) DEFAULT NULL,
  `seo_title_vi` varchar(255) DEFAULT NULL,
  `seo_description_vi` text,
  `seo_keywords_vi` text,
  `avatar` text,
  `timeline_image` text,
  `background_image` text,
  `gallery_images` text,
  `position` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `title_vi`, `parent_id`, `module_id`, `display_position`, `is_active`, `h1_content`, `description_vi`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `seo_title_vi`, `seo_description_vi`, `seo_keywords_vi`, `avatar`, `timeline_image`, `background_image`, `gallery_images`, `position`, `created_at`) VALUES
(1, 'Trang chủ', 0, 2, 'menu_main', 1, 'Trang chủ', '<p>&lt;style&gt; .danhmucconhome-item .image { border-style: solid; width: 100%; overflow: inherit; }</p>\r\n', '<p>&lt;style&gt; .danhmucconhome-item .image { border-style: solid; width: 100%; overflow: inherit; } .danhmucconhome-item .image img { width: 100%; height: 100%; border-radius: unset; opacity: .8; margin: unset; } &lt;/style&gt;</p>\r\n', 'trang-ch', '', '_self', 'Trang chủ', '// Chuyển hướng bằng JS nếu header thất bại\r\n    echo \'<script>window.location.href=\"?page=quantri\";</script>\';\r\n    exit;', 'Trang chủ', '/kai/admin/uploads/1750233866_avatar_info1.jpg', NULL, NULL, NULL, 0, '2025-06-18 15:03:51'),
(2, 'Giới thiệu', 0, 3, 'menu_main', 1, '', '<p>+ Nhiệm vụ của Web Số sẽ c&agrave;i đặt v&agrave; tối ưu quảng c&aacute;o Google cho qu&yacute; kh&aacute;ch trong qu&aacute; tr&igrave;nh hoạt động của quảng c&aacute;o.</p>\r\n', '<p>+ Thay đổi mục ti&ecirc;u quảng c&aacute;o v&agrave; từ kh&oacute;a cho ph&ugrave; hợp với dịch vụ của qu&yacute; kh&aacute;ch. Chi ph&iacute; web Số sẽ nhận 15% dựa v&agrave;o số tiền kh&aacute;ch h&agrave;ng nạp v&agrave;o t&agrave;i khoản quảng c&aacute;o. VD: Qu&yacute; kh&aacute;ch nạp 5tr v&agrave;o tk quảng c&aacute;o, th&igrave; cần chuyển khoản 5.750.000 v&agrave; Web Số sẽ nhận 750.000 ph&iacute; quản l&yacute; quảng c&aacute;o v&agrave; chi ph&iacute; nạp tiền v&agrave;o Google. % Chi ph&iacute; c&oacute; thể thay đổi t&ugrave;y v&agrave;o kh&aacute;ch h&agrave;ng chạy ng&acirc;n s&aacute;ch nhiều hay &iacute;t.</p>\r\n', 'gioi-thieu', '', '_self', 'Giới thiệu', '+ Nhiệm vụ của Web Số sẽ cài đặt và tối ưu quảng cáo Google cho quý khách trong quá trình hoạt động của quảng cáo.', 'Giới thiệu', '/kai/admin/uploads/1750254428_avatar_1915ff9b-380b-4117-b3f9-a58ce21dc6ad.png', NULL, NULL, NULL, 1, '2025-06-18 15:32:00'),
(3, 'Sản phẩm', 0, 4, 'menu_main,trang_chu', 1, '', '', '', 'san-pham', '', '_self', 'Sản phẩm', 'Trái cây tươi ngon, nhập mỗi ngày, đa dạng từ táo, nho, cam đến sầu riêng. Cam kết chất lượng, an toàn vệ sinh, giao hàng nhanh tại nhà.', '', NULL, NULL, NULL, NULL, 2, '2025-06-18 15:33:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `column_settings`
--

CREATE TABLE `column_settings` (
  `id` int NOT NULL,
  `content_type` varchar(50) NOT NULL,
  `items_per_row_tiny` int DEFAULT '2',
  `items_per_row_sm` int DEFAULT '2',
  `items_per_row_md` int DEFAULT '2',
  `items_per_row_lg` int DEFAULT '3',
  `items_per_row_xl` int DEFAULT '6',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `column_settings`
--

INSERT INTO `column_settings` (`id`, `content_type`, `items_per_row_tiny`, `items_per_row_sm`, `items_per_row_md`, `items_per_row_lg`, `items_per_row_xl`, `created_at`, `updated_at`) VALUES
(1, 'products', 2, 2, 2, 3, 3, '2025-06-19 01:16:52', '2025-06-19 02:29:21'),
(2, 'services', 2, 2, 2, 3, 6, '2025-06-19 01:16:52', '2025-06-19 01:16:52'),
(3, 'blog', 2, 2, 2, 3, 6, '2025-06-19 01:16:52', '2025-06-19 01:16:52'),
(4, 'projects', 2, 2, 2, 3, 6, '2025-06-19 01:16:52', '2025-06-19 01:16:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `is_visible` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `contact_info`
--

INSERT INTO `contact_info` (`id`, `image`, `link`, `is_visible`) VALUES
(6, '/kai/admin/uploads/1750167809_facebook.png', 'https://www.facebook.com/HelloIamNgocMinh', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `info`
--

CREATE TABLE `info` (
  `id` int NOT NULL,
  `title_vi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int DEFAULT '0',
  `module_id` int NOT NULL DEFAULT '3',
  `display_position` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `h1_content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_vi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `content_vi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `slug_vi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_vi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_target` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '_self',
  `seo_title_vi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description_vi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `seo_keywords_vi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gallery_images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `position` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `info`
--

INSERT INTO `info` (`id`, `title_vi`, `parent_id`, `module_id`, `display_position`, `is_active`, `h1_content`, `description_vi`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `seo_title_vi`, `seo_description_vi`, `seo_keywords_vi`, `gallery_images`, `position`, `created_at`) VALUES
(2, 'Về chúng tôi', 2, 3, 'trang_chu', 1, 'Về chúng tôi', '<h3>m&ocirc; tả</h3>\r\n', '<h3>✅ Mẹo th&ecirc;m:</h3>\r\n\r\n<p>Nếu bạn muốn hiện t&ecirc;n nằm <strong>tr&ecirc;n g&oacute;c ảnh như watermark</strong>, c&oacute; thể d&ugrave;ng CSS để chồng chữ l&ecirc;n ảnh &ndash; m&igrave;nh cũng c&oacute; thể gi&uacute;p nếu bạn muốn theo kiểu đ&oacute;.</p>\r\n\r\n<p>Bạn c&oacute; muốn chữ nằm <strong>tr&ecirc;n ảnh, g&oacute;c tr&ecirc;n tr&aacute;i</strong> kh&ocirc;ng? Hay chỉ cần kiểu văn bản ph&iacute;a tr&ecirc;n như tr&ecirc;n l&agrave; đủ?</p>\r\n', 've-chung', '', '_self', 'Về chúng tôi', 'ALTER TABLE info ADD COLUMN parent_id INT DEFAULT 0 AFTER title_vi,\r\nADD FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;', 'Về chúng tôi', NULL, 0, '2025-06-18 14:31:11');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `logos`
--

CREATE TABLE `logos` (
  `id` int NOT NULL,
  `title_vi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desktop_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_target` enum('_self','_blank') COLLATE utf8mb4_unicode_ci DEFAULT '_self',
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `position` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `logos`
--

INSERT INTO `logos` (`id`, `title_vi`, `desktop_image`, `mobile_image`, `link_vi`, `link_target`, `width`, `height`, `is_active`, `position`, `created_at`) VALUES
(1, 'logo', '/kai/admin/uploads/1750253207_desktop_fahicon.png', '/kai/admin/uploads/1750253207_mobile_fahicon.png', 'http://localhost/kai/', '_self', NULL, 100, 1, 0, '2025-06-18 13:26:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `position` int DEFAULT '0',
  `is_section` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_visible` tinyint(1) DEFAULT '1',
  `module` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `icon`, `url`, `badge`, `parent_id`, `position`, `is_section`, `created_at`, `is_visible`, `module`) VALUES
(1, 'Bảng Điều Khiển', 'fas fa-home', 'index.php', '', NULL, 1, 0, '2025-06-17 11:22:38', 1, NULL),
(2, 'Thành Phần', 'fa fa-ellipsis-h', NULL, NULL, NULL, 2, 1, '2025-06-17 11:22:38', 0, NULL),
(3, 'Cơ Bản', 'fas fa-layer-group', NULL, NULL, NULL, 3, 0, '2025-06-17 11:22:38', 0, NULL),
(4, 'Hình Đại Diện', NULL, 'avatars.php', NULL, 3, 1, 0, '2025-06-17 11:22:38', 1, NULL),
(5, 'Nút', NULL, 'components/buttons.html', NULL, 3, 2, 0, '2025-06-17 11:22:38', 1, NULL),
(6, 'Tiện Ích', 'fas fa-desktop', 'widgets.html', '', NULL, 4, 0, '2025-06-17 11:22:38', 0, NULL),
(7, 'Trang cá nhân', 'fas fa-user', 'profile.php', NULL, NULL, 5, 0, '2025-06-17 11:22:38', 0, NULL),
(8, 'Quản lý Menu', 'fas fa-cog', 'manage_menu.php', NULL, NULL, 6, 0, '2025-06-17 11:26:28', 0, NULL),
(9, 'Font Awesome Icons', '', 'font-awesome-icons.php', '', 16, 6, 0, '2025-06-17 11:52:02', 1, NULL),
(11, 'Cấu hình', 'fas fa-cogs', 'adminweb/?op=cauhinh', '', NULL, 2, 0, '2025-06-17 12:24:49', 1, NULL),
(12, 'Cấu hình chung', '', 'cauhinh.php', '', 11, 0, 0, '2025-06-17 12:25:09', 1, NULL),
(13, 'S-ADMIN', 'fas fa-user-secret', 'sadmin.php', '', 11, 20, 0, '2025-06-17 15:16:40', 1, NULL),
(14, 'Tài khoản', 'fas fa-user-alt', '', '', NULL, 14, 0, '2025-06-18 01:08:18', 1, NULL),
(15, 'Quản trị - Author', '', 'quantri.php', '', 14, 14, 0, '2025-06-18 01:09:34', 1, NULL),
(16, 'Menu admin', 'fas fa-align-justify', '', '', NULL, 15, 0, '2025-06-18 04:18:53', 1, NULL),
(17, 'Danh sách Menu Admin', '', 'manage_menu.php', '', 16, 0, 0, '2025-06-18 04:26:27', 1, NULL),
(18, 'Full Quyền', 'fas fa-user-friends', 'grant_full_permissions.php', '', NULL, 100, 0, '2025-06-18 04:27:28', 1, NULL),
(19, 'Cấu hình cột', '', 'cauhinhcot.php', '', 11, 0, 0, '2025-06-18 05:07:23', 1, NULL),
(20, 'Đơn hàng', 'fas fa-shopping-cart', 'orders.php', '', NULL, 3, 0, '2025-06-18 06:01:23', 1, NULL),
(21, 'Liên hệ', 'fas fa-envelope', '', '', NULL, 4, 0, '2025-06-18 06:02:47', 1, NULL),
(22, 'Danh sách liên hệ', '', 'danhsachlienhe.php', '', 21, 0, 0, '2025-06-18 06:09:24', 1, NULL),
(23, 'Đăng ký nhận mail', '', 'dangkynhanmail.php', '', 21, 0, 0, '2025-06-18 06:10:00', 1, NULL),
(24, 'Bản đồ', '', 'bando.php', '', 21, 0, 0, '2025-06-18 06:10:11', 1, NULL),
(25, 'Menu', 'fas fa-align-justify', '', '', NULL, 5, 0, '2025-06-18 06:11:20', 1, NULL),
(26, 'Danh sách menu', '', 'categories.php', '', 25, 0, 0, '2025-06-18 06:11:42', 1, NULL),
(27, 'Giới thiệu', 'fas fa-info-circle', '', '', NULL, 7, 0, '2025-06-18 06:14:56', 1, NULL),
(28, 'Danh sách bài viết giới thiệu', '', 'info.php', '', 27, 0, 0, '2025-06-18 06:15:36', 1, NULL),
(29, 'Sản Phẩm', 'fas fa-th-large', '', '', NULL, 8, 0, '2025-06-18 06:16:57', 1, NULL),
(30, 'Danh sách sản phẩm', '', 'product.php', '', 29, 0, 0, '2025-06-18 06:18:00', 1, NULL),
(31, 'Bình luận', '', 'binhluan.php', '', 29, 0, 0, '2025-06-18 06:18:19', 1, NULL),
(32, 'Cấu hình khuyến mãi', '', 'khuyenmai.php', '', 29, 0, 0, '2025-06-18 06:18:37', 1, NULL),
(33, 'Dịch vụ', 'fas fa-file', '', '', NULL, 8, 0, '2025-06-18 06:19:17', 1, NULL),
(34, 'Danh sách bài viết Dịch vụ', '', 'sevice.php', '', 33, 0, 0, '2025-06-18 06:19:40', 1, NULL),
(35, 'Kích thước hình Dịch vụ', '', 'sizeimgservice.php', '', 33, 0, 0, '2025-06-18 06:20:07', 1, NULL),
(36, 'Quản lý Hình - Video', 'fas fa-images', '', '', NULL, 9, 0, '2025-06-18 06:20:38', 1, NULL),
(37, 'Hình slideshow', '', 'slideshow.php', '', 36, 0, 0, '2025-06-18 06:21:09', 1, NULL),
(38, 'Banner - Logo', '', 'logo.php', '', 36, 0, 0, '2025-06-18 06:21:29', 1, NULL),
(39, 'Thư viện ảnh', 'fas fa-image', '', '', NULL, 10, 0, '2025-06-18 06:21:48', 1, NULL),
(40, 'Danh sách thư viện ảnh', '', 'gallery.php', '', 39, 0, 0, '2025-06-18 06:23:12', 1, NULL),
(41, 'Quản lý khác', 'fas fa-plus-square', '', '', NULL, 11, 0, '2025-06-18 06:23:47', 1, NULL),
(42, 'Thông tin chung', '', '', '', 41, 0, 0, '2025-06-18 06:24:00', 1, NULL),
(43, 'Đối tác', '', 'doitac.php', '', 41, 0, 0, '2025-06-18 06:24:20', 1, NULL),
(44, 'Ý kiến khách hàng', '', 'ykienkhachhang.php', '', 41, 0, 0, '2025-06-18 06:24:44', 1, NULL),
(45, 'Lịch sử đăng nhập', '', 'loginhistory.php', '', 14, 22, 0, '2025-06-18 06:26:36', 1, NULL),
(46, 'Kích thước hình bài viết giới thiệu', '', 'sizeimggioithieu.php', '', 27, 30, 0, '2025-06-18 06:32:14', 1, NULL),
(47, 'Ngôn ngữ', '', 'ngonngu.php', '', 11, 15, 0, '2025-06-18 06:33:32', 1, NULL),
(48, 'Module', '', 'danhmuc_type.php', '', 25, 0, 0, '2025-06-18 07:00:13', 1, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `modules`
--

CREATE TABLE `modules` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `option_name` varchar(100) NOT NULL,
  `action_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `modules`
--

INSERT INTO `modules` (`id`, `title`, `position`, `option_name`, `action_name`, `is_active`, `created_at`) VALUES
(2, 'Trang chủ', 1, 'home', '', 1, '2025-06-18 14:45:07'),
(3, 'Giới thiệu', 2, 'intro', '', 1, '2025-06-18 14:45:07'),
(4, 'Sản phẩm', 3, 'product', '', 1, '2025-06-18 14:45:07'),
(5, 'Dịch vụ', 4, 'service', '', 1, '2025-06-18 14:45:07'),
(6, 'Nội dung', 5, 'content', '', 1, '2025-06-18 14:45:07'),
(7, 'Dự án', 6, 'project', '', 1, '2025-06-18 14:45:07'),
(8, 'Tuyển dụng', 7, 'recruitment', '', 1, '2025-06-18 14:45:07'),
(9, 'Video', 8, 'video', '', 1, '2025-06-18 14:45:07'),
(10, 'Hỏi đáp', 9, 'question', '', 1, '2025-06-18 14:45:07'),
(11, 'Thực đơn', 10, 'thucdon', '', 1, '2025-06-18 14:45:07'),
(12, 'Bất Động Sản', 11, 'batdongsan', '', 1, '2025-06-18 14:45:07'),
(13, 'Dự án Bất Động Sản', 12, 'duanbatdongsan', '', 1, '2025-06-18 14:45:07'),
(14, 'Đối tác', 13, 'partner', '', 1, '2025-06-18 14:45:07'),
(15, 'Download', 14, 'download', '', 1, '2025-06-18 14:45:07'),
(16, 'Thư viện ảnh', 15, 'gallery', '', 1, '2025-06-18 14:45:07'),
(17, 'Form liên hệ', 16, 'formlienhe', '', 1, '2025-06-18 14:45:07'),
(18, 'Ý Kiến khách hàng', 17, 'customerreview', '', 1, '2025-06-18 14:45:07'),
(19, 'Liên hệ', 18, 'contact', '', 1, '2025-06-18 14:45:07'),
(20, 'Khác', 19, 'other', '', 1, '2025-06-18 14:45:07'),
(21, 'Hotdeal', 20, 'hotdeal', '', 1, '2025-06-18 14:45:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `ward` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `note` text,
  `total` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer') DEFAULT 'cash',
  `created_at` datetime NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `phone`, `email`, `address`, `ward`, `district`, `province`, `note`, `total`, `payment_method`, `created_at`, `status`) VALUES
(4, 'Vernon Floyd', '+1 (335) 526-5585', 'hudesuqofy@mailinator.com', 'Architecto reiciendi', 'Xã Mỹ Thạnh An', 'Thành phố Bến Tre', 'Tỉnh Bến Tre', 'Dolorem est dolor un', 90000000.00, 'cash', '2025-06-19 12:11:29', 'completed'),
(5, 'Nguyễn Văn Hùng', '+84 912 345 678', 'nguyenvanhung@gmail.com', '123 Đường Láng', 'Phường Láng Thượng', 'Quận Đống Đa', 'Thành phố Hà Nội', 'Giao hàng nhanh', 1500000.00, 'bank_transfer', '2025-06-19 09:30:00', 'completed'),
(6, 'Trần Thị Mai', '+84 987 654 321', 'tranthimai@yahoo.com', '45 Nguyễn Huệ', 'Phường Bến Nghé', 'Quận 1', 'Thành phố Hồ Chí Minh', 'Không gọi trước', 25000000.00, 'cash', '2025-06-19 10:15:00', 'completed'),
(8, 'Phạm Minh Tuấn', '+84 901 234 567', 'phamminhtuan@mailinator.com', '56 Trần Phú', 'Phường Minh An', 'Thành phố Hội An', 'Tỉnh Quảng Nam', 'Giao trong giờ hành chính', 7500000.00, 'cash', '2025-06-19 12:30:00', 'completed'),
(9, 'Hoàng Thị Lan', '+84 925 678 123', 'hoangthilan@gmail.com', '89 Phạm Văn Đồng', 'Phường Xuân Đỉnh', 'Quận Bắc Từ Liêm', 'Thành phố Hà Nội', 'Kiểm tra hàng trước', 3000000.00, 'bank_transfer', '2025-06-19 13:00:00', 'completed'),
(10, 'Đỗ Thị Hồng Nhung', '+84 933 456 789', 'dothihongnhung@gmail.com', '12 Nguyễn Văn Cừ', 'Phường Cầu Ông Lãnh', 'Quận 1', 'Thành phố Hồ Chí Minh', 'Giao hàng trước 17h', 3200000.00, 'bank_transfer', '2025-06-15 08:45:00', 'completed'),
(11, 'Bùi Văn Nam', '+84 976 123 654', 'buivannam@outlook.com', '67 Lê Đại Hành', 'Phường Lê Đại Hành', 'Quận Hai Bà Trưng', 'Thành phố Hà Nội', 'Hàng cần nguyên đai', 850000.00, 'cash', '2025-06-16 14:20:00', 'completed'),
(12, 'Vũ Thị Thu Hà', '+84 918 789 123', 'vuthithuha@yahoo.com', '34 Hùng Vương', 'Phường Điện Biên', 'Quận Ba Đình', 'Thành phố Hà Nội', 'Không giao cuối tuần', 12000000.00, 'bank_transfer', '2025-06-17 10:30:00', 'completed'),
(13, 'Trương Minh Đức', '+84 905 321 987', 'truongminhduc@mailinator.com', '89 Nguyễn Trãi', 'Phường Khâm Thiên', 'Quận Đống Đa', 'Thành phố Hà Nội', 'Gọi trước khi giao', 4500000.00, 'cash', '2025-06-18 16:15:00', 'completed'),
(16, 'Trần Văn Hùng', '+84 988 123 456', 'tranvanhung@yahoo.com', '123 Lý Thường Kiệt', 'Phường Trần Hưng Đạo', 'Quận 5', 'Thành phố Hồ Chí Minh', 'Kiểm tra hàng', 15000000.00, 'cash', '2024-09-15 14:30:00', 'completed'),
(17, 'Lê Thị Minh Thư', '+84 935 789 123', 'lethiminhthu@outlook.com', '78 Phạm Ngũ Lão', 'Phường Phạm Ngũ Lão', 'Quận 1', 'Thành phố Hồ Chí Minh', 'Giao trong ngày', 5000000.00, 'bank_transfer', '2024-11-20 11:15:00', 'completed'),
(18, 'Phạm Văn Quang', '+84 902 321 987', 'phamvanquang@mailinator.com', '45 Lê Văn Sỹ', 'Phường 13', 'Quận 3', 'Thành phố Hồ Chí Minh', 'Không gọi trước', 8000000.00, 'cash', '2025-01-25 16:45:00', 'completed'),
(19, 'Hoàng Minh Tâm', '+84 927 654 321', 'hoangminhtam@gmail.com', '89 Hùng Vương', 'Phường Thắng Nhì', 'Thành phố Vũng Tàu', 'Tỉnh Bà Rịa - Vũng Tàu', 'Hàng dễ vỡ', 3500000.00, 'bank_transfer', '2025-03-10 10:00:00', 'completed');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `quantity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `title`, `price`, `quantity`) VALUES
(4, 4, 1, 'Chuối', 90000000.00, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `title_vi` varchar(255) NOT NULL,
  `category_id` int NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `current_price` decimal(15,2) DEFAULT '0.00',
  `original_price` decimal(15,2) DEFAULT '0.00',
  `weight` decimal(10,2) DEFAULT '0.00',
  `display_position` text,
  `is_active` tinyint(1) DEFAULT '1',
  `stock_status` enum('in_stock','out_of_stock') DEFAULT 'in_stock',
  `h1_content` varchar(255) DEFAULT NULL,
  `description_vi` text,
  `content_vi` text,
  `slug_vi` varchar(255) DEFAULT NULL,
  `link_vi` varchar(255) DEFAULT NULL,
  `link_target` varchar(10) DEFAULT NULL,
  `seo_title_vi` varchar(255) DEFAULT NULL,
  `seo_description_vi` text,
  `seo_keywords_vi` text,
  `gallery_images` text,
  `position` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `title_vi`, `category_id`, `product_code`, `current_price`, `original_price`, `weight`, `display_position`, `is_active`, `stock_status`, `h1_content`, `description_vi`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `seo_title_vi`, `seo_description_vi`, `seo_keywords_vi`, `gallery_images`, `position`, `created_at`) VALUES
(1, 'Chuối', 3, '001', 90000000.00, 190000000.00, 1.00, 'menu_top,trang_chu', 1, 'in_stock', '', '<p>Lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiusmod te incididunt</p>\r\n', '<p>Lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiusmod te incididunt</p>\r\n', 'chuối', '', '_self', 'Quả mơ', 'Lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiusmod te incididunt', 'Quả mơ', '/kai/admin/uploads/1750309872_gallery_0_fruite-item-4.jpg', 2, '2025-06-18 16:23:24'),
(3, 'Aut nulla qui reicie', 3, 'Aut est deserunt du', 269.00, 108.00, 90.00, 'menu_top,menu_main,trang_chu,lay_mo_ta,duoi', 1, 'in_stock', 'Consequat Molestiae', '<p>Sint et sint, conseq.</p>\r\n', '<p>Et quo dolore pariat.</p>\r\n', 'Deserunt reprehender', 'Est et pariatur Vi', '_self', 'Corrupti ad ullamco', 'Velit dolor irure a', 'Fugiat elit dolorem', '/kai/admin/uploads/1750251873_gallery_0_fruite-item-5.jpg', 87, '2025-06-18 20:04:15'),
(4, 'Est soluta praesent', 3, 'Voluptate impedit v', 631.00, 115.00, 63.00, 'menu_top,menu_main,lay_noi_dung,duoi', 1, 'in_stock', 'Minim est voluptas b', '<p>Anim lorem consequat.</p>\r\n', '<p>Aut cupiditate minus.</p>\r\n', 'Quia duis ab suscipi', 'Omnis animi ut unde', '_blank', 'Rem excepteur quisqu', 'At dolore temporibus', 'Ex rerum dolorem ali', '/kai/admin/uploads/1750251962_gallery_0_fruite-item-3.jpg', 45, '2025-06-18 20:04:58'),
(5, 'Qui dolore maxime ea', 3, 'Voluptatibus molesti', 908.00, 621.00, 41.00, 'menu_top,menu_main,trang_chu,an_tieu_de,noi_bat,duoi', 1, 'in_stock', 'Eu aliquam molestias', '<p>Temporibus amet, eu .</p>\r\n', '<p>Mollitia eligendi se.</p>\r\n', 'Occaecat et non volu', 'Sint ratione dolorem', '_blank', 'Est anim est non ut', 'Ullamco quidem volup', 'Corrupti iure quas', '/kai/admin/uploads/1750251929_gallery_0_fruite-item-2.jpg', 97, '2025-06-18 20:05:29'),
(6, 'Eu quis nulla et har', 3, 'Eaque inventore a sa', 591.00, 579.00, 0.00, 'menu_top,menu_main,lay_mo_ta,duoi', 1, 'in_stock', 'Sint consequatur ut', '<p>Alias sed quis dolor.</p>\r\n', '<p>Doloremque cillum nu.</p>\r\n', 'Sed mollit labore di', 'Nulla nisi incidunt', '_self', 'Enim quisquam volupt', 'Necessitatibus et ul', 'Laudantium quis exc', '/kai/admin/uploads/1750251996_gallery_0_fruite-item-1.jpg', 5, '2025-06-18 20:06:36'),
(7, 'Et tempore anim ist', 3, 'Aut tempor qui ipsam', 989.00, 553.00, 44.00, 'menu_main,trang_chu,noi_bat', 1, 'in_stock', 'Eos voluptates eaque', '<p>Sit quam reiciendis .</p>\r\n', '<p>Voluptatum consequat.</p>\r\n', 'Vitae consequuntur e', 'Perspiciatis volupt', '_self', 'Nostrum saepe quia u', 'Nobis debitis vel de', 'Corrupti adipisci i', '/kai/admin/uploads/1750252050_gallery_0_featur-2.jpg', 84, '2025-06-18 20:07:30'),
(8, 'Possimus repellendu', 3, 'Commodi mollit provi', 587.00, 72.00, 23.00, 'menu_top,menu_main,trang_chu,lay_mo_ta,lay_noi_dung', 1, 'in_stock', 'Necessitatibus digni', '<p>Quam possimus, culpa.</p>\r\n', '<p>Autem provident, cup.</p>\r\n', 'Eaque qui voluptatem', 'Facilis nihil id vit', '_self', 'Quo fugit dolores d', 'Mollitia quia eaque', 'Totam animi ab poss', '/kai/admin/uploads/1750252153_gallery_0_featur-3.jpg', 5, '2025-06-18 20:09:13'),
(9, 'Est vitae illo magn', 3, 'Sit molestias irure', 807.00, 457.00, 57.00, 'trang_chu,an_tieu_de,lay_mo_ta,lay_noi_dung,noi_bat,duoi', 1, 'in_stock', 'Incididunt sint non', '<p>Consectetur duis nat.</p>\r\n', '<p>Eos voluptatem ut pa.</p>\r\n', 'Numquam consequat S', 'Alias consequatur o', '_self', 'Non earum dolor repr', 'Provident doloremqu', 'Ut cupiditate dolor', '/kai/admin/uploads/1750252192_gallery_0_featur-1.jpg', 60, '2025-06-18 20:09:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int NOT NULL,
  `category_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES
(1, 3),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(7, 3),
(8, 3),
(9, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Quản trị hệ thống'),
(2, 'user', 'Người dùng thông thường'),
(3, 'staff', 'Nhân viên quản lý nội dung');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`) VALUES
(1, 'website_status', '1'),
(2, 'favicon', '/kai/admin/uploads/1750167794_fahicon.png'),
(3, 'scroll_top', '1'),
(4, 'lock_copy', '1'),
(9, 'contact_image_6', '/kai/admin/uploads/1750167809_facebook.png'),
(22, 'fonts', '{\"body\":{\"family\":\"Times New Roman\",\"style\":\"normal\",\"size\":\"16\"},\"menu\":{\"family\":\"Times New Roman\",\"style\":\"normal\",\"size\":\"16\"},\"top\":{\"family\":\"Arial\",\"style\":\"normal\",\"size\":\"16\"},\"banner\":{\"family\":\"Arial\",\"style\":\"normal\",\"size\":\"16\"},\"title\":{\"family\":\"Arial\",\"style\":\"normal\",\"size\":\"16\"},\"footer\":{\"family\":\"Arial\",\"style\":\"normal\",\"size\":\"16\"},\"timeline\":{\"family\":\"Arial\",\"style\":\"normal\",\"size\":\"16\"}}'),
(31, 'colors', '{\"body\":{\"bg\":\"#ffffff\",\"text\":\"#000000\"},\"menu\":{\"bg\":\"#e9c9c9\",\"text\":\"#000000\"},\"top\":{\"bg\":\"#FFFFFF\",\"text\":\"#000000\"},\"banner\":{\"bg\":\"#FFFFFF\",\"text\":\"#000000\"},\"title\":{\"bg\":\"#FFFFFF\",\"text\":\"#000000\"},\"footer\":{\"bg\":\"#FFFFFF\",\"text\":\"#000000\"},\"timeline\":{\"bg\":\"#FFFFFF\",\"text\":\"#000000\"}}');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `slideshow`
--

CREATE TABLE `slideshow` (
  `id` int NOT NULL,
  `title_vi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int DEFAULT NULL,
  `desktop_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_target` enum('_self','_blank') COLLATE utf8mb4_unicode_ci DEFAULT '_self',
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `position` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `slideshow`
--

INSERT INTO `slideshow` (`id`, `title_vi`, `category_id`, `desktop_image`, `mobile_image`, `link_vi`, `link_target`, `width`, `height`, `is_active`, `position`, `created_at`) VALUES
(1, 'sl1', NULL, '/kai/admin/uploads/1750253828_desktop_s1-6248-hinh.png', '/kai/admin/uploads/1750253828_mobile_s1-6248-hinh.png', '', '_self', NULL, NULL, 1, 0, '2025-06-18 13:36:36'),
(2, 'sl2', NULL, '/kai/admin/uploads/1750254102_desktop_s2-5469-hinh.png', '/kai/admin/uploads/1750254102_mobile_s2-5469-hinh.png', '', '_self', NULL, NULL, 1, 0, '2025-06-18 13:41:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `userlog`
--

CREATE TABLE `userlog` (
  `id` int NOT NULL,
  `userId` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `userIp` varchar(45) NOT NULL,
  `login_time` datetime NOT NULL,
  `status` enum('success','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `userlog`
--

INSERT INTO `userlog` (`id`, `userId`, `username`, `userIp`, `login_time`, `status`) VALUES
(6, 1, 'admin', '::1', '2025-06-19 13:06:05', 'success'),
(8, 2, 'loi', '::1', '2025-06-19 13:06:43', 'success'),
(9, 1, 'admin', '::1', '2025-06-19 13:06:48', 'success'),
(10, 1, 'admin', '::1', '2025-06-19 13:20:41', 'success'),
(11, 2, 'loi', '::1', '2025-06-19 13:42:00', 'failed'),
(12, 2, 'loi', '::1', '2025-06-19 13:42:03', 'failed');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role_id` int DEFAULT '2',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role_id`, `created_at`, `phone`, `address`, `avatar`, `dob`, `is_active`) VALUES
(1, 'admin', 'admin', 'Thắng Rai', 'badaotulong123@gmail.com', 1, '2025-06-17 03:36:20', '0914476792', '59 Đường 30/4, Phường Tân Thành, Quận Tân Phú, Thành phố Hồ Chí Minh', '/kai/admin/uploads/1750219074_z4129947384514_d72a106cac6e6e6f85ffe9c60e3fa8cb.jpg', '2003-06-20', 1),
(2, 'loi', 'loi', 'Bá Lợi', 'baloi@gmail.com', 3, '2025-06-18 01:38:43', '0914476792', 'thôn 3 số nhà 161', '/kai/admin/uploads/1750210723_yk1.jpg', '2025-06-18', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_menu_permissions`
--

CREATE TABLE `user_menu_permissions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `menu_item_id` int NOT NULL,
  `can_view` tinyint(1) DEFAULT '1',
  `can_add` tinyint(1) DEFAULT '0',
  `can_edit` tinyint(1) DEFAULT '0',
  `can_delete` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `user_menu_permissions`
--

INSERT INTO `user_menu_permissions` (`id`, `user_id`, `menu_item_id`, `can_view`, `can_add`, `can_edit`, `can_delete`) VALUES
(889, 2, 12, 1, 0, 0, 0),
(890, 2, 22, 1, 1, 1, 1),
(891, 2, 23, 1, 1, 1, 1),
(892, 2, 24, 1, 1, 1, 1),
(893, 2, 1, 1, 0, 0, 0),
(894, 2, 11, 1, 0, 0, 0),
(895, 2, 20, 1, 1, 1, 0),
(896, 2, 21, 1, 0, 0, 0),
(897, 2, 13, 1, 0, 0, 0),
(940, 1, 1, 1, 1, 1, 1),
(941, 1, 4, 1, 1, 1, 1),
(942, 1, 5, 1, 1, 1, 1),
(943, 1, 9, 1, 1, 1, 1),
(944, 1, 11, 1, 1, 1, 1),
(945, 1, 12, 1, 1, 1, 1),
(946, 1, 13, 1, 1, 1, 1),
(947, 1, 14, 1, 1, 1, 1),
(948, 1, 15, 1, 1, 1, 1),
(949, 1, 16, 1, 1, 1, 1),
(950, 1, 17, 1, 1, 1, 1),
(951, 1, 18, 1, 1, 1, 1),
(952, 1, 19, 1, 1, 1, 1),
(953, 1, 20, 1, 1, 1, 1),
(954, 1, 21, 1, 1, 1, 1),
(955, 1, 22, 1, 1, 1, 1),
(956, 1, 23, 1, 1, 1, 1),
(957, 1, 24, 1, 1, 1, 1),
(958, 1, 25, 1, 1, 1, 1),
(959, 1, 26, 1, 1, 1, 1),
(960, 1, 27, 1, 1, 1, 1),
(961, 1, 28, 1, 1, 1, 1),
(962, 1, 29, 1, 1, 1, 1),
(963, 1, 30, 1, 1, 1, 1),
(964, 1, 31, 1, 1, 1, 1),
(965, 1, 32, 1, 1, 1, 1),
(966, 1, 33, 1, 1, 1, 1),
(967, 1, 34, 1, 1, 1, 1),
(968, 1, 35, 1, 1, 1, 1),
(969, 1, 36, 1, 1, 1, 1),
(970, 1, 37, 1, 1, 1, 1),
(971, 1, 38, 1, 1, 1, 1),
(972, 1, 39, 1, 1, 1, 1),
(973, 1, 40, 1, 1, 1, 1),
(974, 1, 41, 1, 1, 1, 1),
(975, 1, 42, 1, 1, 1, 1),
(976, 1, 43, 1, 1, 1, 1),
(977, 1, 44, 1, 1, 1, 1),
(978, 1, 45, 1, 1, 1, 1),
(979, 1, 46, 1, 1, 1, 1),
(980, 1, 47, 1, 1, 1, 1),
(981, 1, 48, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `visits`
--

CREATE TABLE `visits` (
  `id` int NOT NULL,
  `visit_date` datetime NOT NULL,
  `visit_count` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `visits`
--

INSERT INTO `visits` (`id`, `visit_date`, `visit_count`, `created_at`, `updated_at`) VALUES
(1, '2025-06-13 10:00:00', 150, '2025-06-19 05:07:27', '2025-06-19 05:07:27'),
(2, '2025-06-14 12:00:00', 200, '2025-06-19 05:07:27', '2025-06-19 05:07:27'),
(3, '2025-06-15 14:00:00', 180, '2025-06-19 05:07:27', '2025-06-19 05:07:27'),
(4, '2025-06-16 16:00:00', 220, '2025-06-19 05:07:27', '2025-06-19 05:07:27'),
(5, '2025-06-17 18:00:00', 190, '2025-06-19 05:07:27', '2025-06-19 05:07:27'),
(6, '2025-06-18 20:00:00', 210, '2025-06-19 05:07:27', '2025-06-19 05:07:27'),
(7, '2025-06-19 10:00:00', 230, '2025-06-19 05:07:27', '2025-06-19 05:07:27');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `column_settings`
--
ALTER TABLE `column_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `content_type` (`content_type`);

--
-- Chỉ mục cho bảng `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `info`
--
ALTER TABLE `info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Chỉ mục cho bảng `logos`
--
ALTER TABLE `logos`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Chỉ mục cho bảng `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Chỉ mục cho bảng `slideshow`
--
ALTER TABLE `slideshow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `userlog`
--
ALTER TABLE `userlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- Chỉ mục cho bảng `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Chỉ mục cho bảng `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `column_settings`
--
ALTER TABLE `column_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `info`
--
ALTER TABLE `info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `logos`
--
ALTER TABLE `logos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT cho bảng `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT cho bảng `slideshow`
--
ALTER TABLE `slideshow`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `userlog`
--
ALTER TABLE `userlog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=982;

--
-- AUTO_INCREMENT cho bảng `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `info`
--
ALTER TABLE `info`
  ADD CONSTRAINT `info_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `info_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ràng buộc cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ràng buộc cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `slideshow`
--
ALTER TABLE `slideshow`
  ADD CONSTRAINT `slideshow_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ràng buộc cho bảng `userlog`
--
ALTER TABLE `userlog`
  ADD CONSTRAINT `userlog_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Ràng buộc cho bảng `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  ADD CONSTRAINT `user_menu_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_menu_permissions_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
