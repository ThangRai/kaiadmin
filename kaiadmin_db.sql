-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th6 21, 2025 lúc 05:39 AM
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
(1, 'Trang chủ', 0, 2, '', 1, 'Trang chủ', '<p>&lt;style&gt; .danhmucconhome-item .image { border-style: solid; width: 100%; overflow: inherit; }</p>\r\n', '<p>&lt;style&gt; .danhmucconhome-item .image { border-style: solid; width: 100%; overflow: inherit; } .danhmucconhome-item .image img { width: 100%; height: 100%; border-radius: unset; opacity: .8; margin: unset; } &lt;/style&gt;</p>\r\n', 'trang-ch', '', '_self', 'Trang chủ', '// Chuyển hướng bằng JS nếu header thất bại\r\n    echo \'<script>window.location.href=\"?page=quantri\";</script>\';\r\n    exit;', 'Trang chủ', '/kai/admin/uploads/1750233866_avatar_info1.jpg', NULL, NULL, NULL, 0, '2025-06-18 15:03:51'),
(2, 'Giới thiệu', 0, 3, '', 1, '', '<p>+ Nhiệm vụ của Web Số sẽ c&agrave;i đặt v&agrave; tối ưu quảng c&aacute;o Google cho qu&yacute; kh&aacute;ch trong qu&aacute; tr&igrave;nh hoạt động của quảng c&aacute;o.</p>\r\n', '<p>+ Thay đổi mục ti&ecirc;u quảng c&aacute;o v&agrave; từ kh&oacute;a cho ph&ugrave; hợp với dịch vụ của qu&yacute; kh&aacute;ch. Chi ph&iacute; web Số sẽ nhận 15% dựa v&agrave;o số tiền kh&aacute;ch h&agrave;ng nạp v&agrave;o t&agrave;i khoản quảng c&aacute;o. VD: Qu&yacute; kh&aacute;ch nạp 5tr v&agrave;o tk quảng c&aacute;o, th&igrave; cần chuyển khoản 5.750.000 v&agrave; Web Số sẽ nhận 750.000 ph&iacute; quản l&yacute; quảng c&aacute;o v&agrave; chi ph&iacute; nạp tiền v&agrave;o Google. % Chi ph&iacute; c&oacute; thể thay đổi t&ugrave;y v&agrave;o kh&aacute;ch h&agrave;ng chạy ng&acirc;n s&aacute;ch nhiều hay &iacute;t.</p>\r\n', 'gioi-thieu', '', '_self', 'Giới thiệu', '+ Nhiệm vụ của Web Số sẽ cài đặt và tối ưu quảng cáo Google cho quý khách trong quá trình hoạt động của quảng cáo.', 'Giới thiệu', '/kai/admin/uploads/1750254428_avatar_1915ff9b-380b-4117-b3f9-a58ce21dc6ad.png', NULL, NULL, NULL, 1, '2025-06-18 15:32:00'),
(3, 'Sản phẩm', 0, 4, '', 1, '', '', '', 'san-pham', '', '_self', 'Sản phẩm', 'Trái cây tươi ngon, nhập mỗi ngày, đa dạng từ táo, nho, cam đến sầu riêng. Cam kết chất lượng, an toàn vệ sinh, giao hàng nhanh tại nhà.', '', NULL, NULL, NULL, NULL, 2, '2025-06-18 15:33:54'),
(6, 'Dịch vụ', 0, 5, '', 1, 'Dịch vụ', '<p>Iure et consectetur .</p>\r\n', '<p>Qui qui totam sunt f.</p>\r\n', 'dịch-vụ', '', '_self', 'Sint ipsum maiores', 'Labore aut accusanti', 'Sed aliquid voluptat', NULL, NULL, NULL, NULL, 3, '2025-06-21 08:28:21'),
(7, 'Nội dung', 0, 6, '', 1, 'Nội dung', '<p>Incidunt, similique .</p>\r\n', '<p>Ut eu nihil non vita.</p>\r\n', 'nội-dung', '', '_self', 'Quos id quo necessit', 'Odit non et eum in a', 'Esse fugiat obcaec', '/kai/admin/uploads/1750470669_avatar_hinh-9.jpg', NULL, NULL, NULL, 5, '2025-06-21 08:51:09'),
(8, 'Dự án', 0, 7, '', 1, 'Dự án', '<p>Culpa ut elit, nostr.</p>\r\n', '<p>Deserunt ut fuga. Vo.</p>\r\n', 'dự-án', '', '_self', 'Aspernatur officia q', 'Soluta qui exercitat', 'Eum voluptate nisi e', '/kai/admin/uploads/1750471003_avatar_-file9464-568.jpg', NULL, NULL, NULL, 5, '2025-06-21 08:56:43'),
(9, 'Đối tác', 0, 14, '', 1, 'Đối tác', '<p>Velit doloremque imp.</p>\r\n', '<p>Dolor dolor animi, v.</p>\r\n', 'đối-tác', '', '_self', 'In anim quia volupta', 'Sed magni voluptatib', 'At enim neque quis n', '/kai/admin/uploads/1750473227_avatar_-file1303-415.jpg', NULL, NULL, NULL, 7, '2025-06-21 09:33:47'),
(10, 'Ý kiến khách hàng', 0, 18, '', 1, 'Ý kiến khách hàng', '<p>Sunt sint consequatu.</p>\r\n', '<p>Magni adipisci molli.</p>\r\n', 'ý-kiến-khách-hàng', '', '_self', 'Ý kiến khách hàng', 'Lorem est tenetur si', 'Unde similique exerc', '/kai/admin/uploads/1750477562_avatar_khach-hang-1.jpg', NULL, NULL, NULL, 9, '2025-06-21 10:46:02'),
(11, 'Aspernatur esse vel', 10, 18, '', 1, 'Consequat Aut et mo', '<p>Sint non ut mollit d.</p>\r\n', '<p>Et quibusdam sunt de.</p>\r\n', 'Ea ea ut aute beatae', 'Quas temporibus debi', '_blank', 'Vero molestiae iste', 'Est voluptas est fa', 'Ipsam ab dolore sint', NULL, NULL, NULL, NULL, 72, '2025-06-21 10:54:18'),
(12, 'Video', 0, 9, '', 1, 'Video', '<p>Magnam voluptates au.</p>\r\n', '<p>Dicta voluptatem ven.</p>\r\n', 'video', '', '_self', 'Quas corrupti illo', 'A odio optio eius i', 'Sint inventore labor', NULL, NULL, NULL, NULL, 10, '2025-06-21 10:59:57');

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
(1, 'products', 1, 2, 2, 3, 3, '2025-06-19 01:16:52', '2025-06-21 04:13:23'),
(2, 'services', 3, 2, 2, 3, 6, '2025-06-19 01:16:52', '2025-06-21 04:12:52'),
(3, 'blog', 2, 3, 2, 3, 6, '2025-06-19 01:16:52', '2025-06-21 03:33:40'),
(4, 'projects', 2, 2, 2, 3, 6, '2025-06-19 01:16:52', '2025-06-19 01:16:52'),
(19, 'partners', 2, 2, 2, 3, 6, '2025-06-21 03:25:03', '2025-06-21 03:25:03'),
(20, 'gallery', 2, 2, 2, 3, 6, '2025-06-21 03:28:48', '2025-06-21 03:28:48'),
(23, 'video', 1, 2, 3, 4, 4, '2025-06-21 04:12:38', '2025-06-21 04:12:38'),
(24, 'customer_feedback', 2, 2, 3, 4, 4, '2025-06-21 04:12:38', '2025-06-21 04:13:03');

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
-- Cấu trúc bảng cho bảng `content`
--

CREATE TABLE `content` (
  `id` int NOT NULL,
  `title_vi` varchar(255) NOT NULL,
  `parent_id` int DEFAULT '0',
  `module_id` int DEFAULT '7',
  `display_position` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `h1_content` varchar(255) DEFAULT NULL,
  `description_vi` text,
  `content_vi` text,
  `slug_vi` varchar(255) DEFAULT NULL,
  `link_vi` varchar(255) DEFAULT NULL,
  `link_target` varchar(50) DEFAULT '_self',
  `seo_title_vi` varchar(255) DEFAULT NULL,
  `seo_description_vi` text,
  `seo_keywords_vi` text,
  `gallery_images` text,
  `position` int DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `content`
--

INSERT INTO `content` (`id`, `title_vi`, `parent_id`, `module_id`, `display_position`, `is_active`, `h1_content`, `description_vi`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `seo_title_vi`, `seo_description_vi`, `seo_keywords_vi`, `gallery_images`, `position`, `created_at`, `updated_at`) VALUES
(1, 'Officiis aliquip dol', 0, 6, '', 1, 'Nihil omnis voluptat', '<p>Inventore eum volupt.</p>\r\n', '<p>A eveniet, in anim l.</p>\r\n', 'officiis-aliquip-dol', '', '_self', 'Molestiae et deserun', 'Eum aut odit labore', 'Lorem accusamus ad e', '/kai/admin/uploads/1750470878_gallery_0_nghi-phan-0.jpg', 1, '2025-06-21 08:54:38', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer_feedback`
--

CREATE TABLE `customer_feedback` (
  `id` int NOT NULL COMMENT 'Mã định danh duy nhất cho ý kiến khách hàng',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên khách hàng',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn hình ảnh',
  `module_id` int NOT NULL COMMENT 'ID module liên kết (tham chiếu id trong categories)',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả ý kiến khách hàng',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung ý kiến khách hàng',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Trạng thái hiển thị (1: hiện, 0: ẩn)',
  `created_at` datetime NOT NULL COMMENT 'Thời gian tạo',
  `updated_at` datetime DEFAULT NULL COMMENT 'Thời gian cập nhật cuối'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu trữ ý kiến khách hàng';

--
-- Đang đổ dữ liệu cho bảng `customer_feedback`
--

INSERT INTO `customer_feedback` (`id`, `name`, `image`, `module_id`, `description`, `content`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn A', 'uploads/feedback/1750477330_hinh-9.jpg', 10, 'Sản phẩm rất tốt, dịch vụ tuyệt vời!', 'Sản phẩm rất tốt, dịch vụ tuyệt vời!', 1, '2025-06-21 10:36:31', '2025-06-21 10:42:10'),
(2, 'Trần Thị B', 'uploads/feedback/1750477362_khach-hang-4.jpg', 10, 'Tôi rất hài lòng với chất lượng.', 'Tôi rất hài lòng với chất lượng.', 1, '2025-06-21 10:36:31', '2025-06-21 10:42:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `gallery`
--

CREATE TABLE `gallery` (
  `id` int NOT NULL,
  `title_vi` varchar(255) NOT NULL,
  `module_id` int DEFAULT '9',
  `is_active` tinyint(1) DEFAULT '1',
  `gallery_images` text,
  `position` int DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `gallery`
--

INSERT INTO `gallery` (`id`, `title_vi`, `module_id`, `is_active`, `gallery_images`, `position`, `created_at`, `updated_at`) VALUES
(1, 'hình ảnh', 9, 1, '/kai/admin/uploads/1750471486_gallery_0_featur-3.jpg,/kai/admin/uploads/1750471486_gallery_1_featur-1.jpg,/kai/admin/uploads/1750471486_gallery_2_featur-2.jpg,/kai/admin/uploads/1750471486_gallery_3_fruite-item-1.jpg', 1, '2025-06-21 09:04:46', NULL),
(2, 'ds', 9, 1, '/kai/admin/uploads/1750471787_gallery_0_phuong-quyen-tran-0.jpg,/kai/admin/uploads/1750471787_gallery_1_nghi-phan-0.jpg,/kai/admin/uploads/1750471787_gallery_2_bao-bao-0.jpg,/kai/admin/uploads/1750471787_gallery_3_bao-an-nguyen-ngoc-0.jpg,/kai/admin/uploads/1750471787_gallery_4_-file9355-504.jpg,/kai/admin/uploads/1750471787_gallery_5_-file1303-415.jpg,/kai/admin/uploads/1750471787_gallery_6_-file1646-931.jpg,/kai/admin/uploads/1750471787_gallery_7_-file2211-561.jpg', 0, '2025-06-21 09:09:47', NULL);

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
(2, 'Về chúng tôi', 2, 3, '', 1, 'Về chúng tôi', '<h3>m&ocirc; tả</h3>\r\n', '<h3>✅ Mẹo th&ecirc;m:</h3>\r\n\r\n<p>Nếu bạn muốn hiện t&ecirc;n nằm <strong>tr&ecirc;n g&oacute;c ảnh như watermark</strong>, c&oacute; thể d&ugrave;ng CSS để chồng chữ l&ecirc;n ảnh &ndash; m&igrave;nh cũng c&oacute; thể gi&uacute;p nếu bạn muốn theo kiểu đ&oacute;.</p>\r\n\r\n<p>Bạn c&oacute; muốn chữ nằm <strong>tr&ecirc;n ảnh, g&oacute;c tr&ecirc;n tr&aacute;i</strong> kh&ocirc;ng? Hay chỉ cần kiểu văn bản ph&iacute;a tr&ecirc;n như tr&ecirc;n l&agrave; đủ?</p>\r\n', 've-chung', '', '_self', 'Về chúng tôi', 'ALTER TABLE info ADD COLUMN parent_id INT DEFAULT 0 AFTER title_vi,\r\nADD FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;', 'Về chúng tôi', NULL, 0, '2025-06-18 14:31:11');

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
-- Cấu trúc bảng cho bảng `maps`
--

CREATE TABLE `maps` (
  `id` int NOT NULL COMMENT 'Mã định danh duy nhất cho bản đồ',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên bản đồ',
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Địa chỉ chi tiết',
  `province_code` int NOT NULL COMMENT 'Mã tỉnh/thành phố (từ API)',
  `zoom` int DEFAULT '15' COMMENT 'Mức zoom bản đồ',
  `coordinates` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tọa độ (lat,lng)',
  `embed_code` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã nhúng iframe Google Maps',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Trạng thái hiển thị (1: hiện, 0: ẩn)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu trữ thông tin bản đồ';

--
-- Đang đổ dữ liệu cho bảng `maps`
--

INSERT INTO `maps` (`id`, `title`, `address`, `province_code`, `zoom`, `coordinates`, `embed_code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Thắng Rai', 'Thôn 3, Krông Bông, Đắk Lắk 64407', 66, 15, '12.5272579,108.3053496', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3894.832426986425!2d108.305349584132!3d12.527257892892967!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317193b603af7f3b%3A0x71c85315931a80b0!2zVGjhuq9uZyBSYWk!5e0!3m2!1svi!2s!4v1750479553562!5m2!1svi!2s\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 1, '2025-06-21 11:21:06', '2025-06-21 12:07:35');

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
(26, 'Danh sách menu', '', 'categories.php', '', 25, 30, 0, '2025-06-18 06:11:42', 1, NULL),
(27, 'Giới thiệu', 'fas fa-info-circle', '', '', NULL, 7, 0, '2025-06-18 06:14:56', 1, NULL),
(28, 'Danh sách bài viết giới thiệu', '', 'info.php', '', 27, 0, 0, '2025-06-18 06:15:36', 1, NULL),
(29, 'Sản Phẩm', 'fas fa-th-large', '', '', NULL, 7, 0, '2025-06-18 06:16:57', 1, NULL),
(30, 'Danh sách sản phẩm', '', 'product.php', '', 29, 30, 0, '2025-06-18 06:18:00', 1, NULL),
(31, 'Bình luận', '', 'binhluan.php', '', 29, 32, 0, '2025-06-18 06:18:19', 1, NULL),
(32, 'Cấu hình khuyến mãi', '', 'khuyenmai.php', '', 29, 31, 0, '2025-06-18 06:18:37', 1, NULL),
(33, 'Dịch vụ', 'fas fa-file', '', '', NULL, 9, 0, '2025-06-18 06:19:17', 1, NULL),
(34, 'Danh sách bài viết Dịch vụ', '', 'service.php', '', 33, 0, 0, '2025-06-18 06:19:40', 1, NULL),
(35, 'Kích thước hình Dịch vụ', '', 'sizeimgservice.php', '', 33, 0, 0, '2025-06-18 06:20:07', 0, NULL),
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
(46, 'Kích thước hình bài viết giới thiệu', '', 'sizeimggioithieu.php', '', 27, 30, 0, '2025-06-18 06:32:14', 0, NULL),
(47, 'Ngôn ngữ', '', 'ngonngu.php', '', 11, 15, 0, '2025-06-18 06:33:32', 1, NULL),
(48, 'Module', '', 'danhmuc_type.php', '', 25, 33, 0, '2025-06-18 07:00:13', 1, NULL),
(49, 'Nội dung - Blog', 'fas fa-book', '', '', NULL, 8, 0, '2025-06-21 01:46:24', 1, NULL),
(50, 'Danh sách Nội dung - Blog', '', 'content.php', '', 49, 0, 0, '2025-06-21 01:47:02', 1, NULL),
(51, 'Dự án', 'fas fa-clipboard-list', '', '', NULL, 9, 0, '2025-06-21 01:47:41', 1, NULL),
(52, 'Danh sách dự án', '', 'project.php', '', 51, 0, 0, '2025-06-21 01:48:02', 1, NULL),
(53, 'Video', 'fas fa-video-slash', 'video.php', '', NULL, 13, 0, '2025-06-21 02:15:53', 1, NULL);

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
-- Cấu trúc bảng cho bảng `partner`
--

CREATE TABLE `partner` (
  `id` int NOT NULL COMMENT 'Mã định danh duy nhất cho đối tác',
  `title_vi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên đối tác bằng tiếng Việt',
  `parent_id` int DEFAULT '0' COMMENT 'Mã danh mục cha (0 nếu không có danh mục cha)',
  `module_id` int NOT NULL DEFAULT '8' COMMENT 'Mã module (8 cho module Đối tác)',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Trạng thái hiển thị (1: Hiện, 0: Ẩn)',
  `h1_content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nội dung thẻ H1',
  `content_vi` text COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung chi tiết bằng tiếng Việt',
  `slug_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn thân thiện tiếng Việt',
  `link_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Liên kết tiếng Việt',
  `link_target` enum('_self','_blank') COLLATE utf8mb4_unicode_ci DEFAULT '_self' COMMENT 'Phương thức mở liên kết (_self: Trang hiện tại, _blank: Trang mới)',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn tới hình ảnh của đối tác',
  `width` int DEFAULT NULL COMMENT 'Độ rộng hình ảnh (pixel)',
  `height` int DEFAULT NULL COMMENT 'Chiều cao hình ảnh (pixel)',
  `position` int DEFAULT '0' COMMENT 'Thứ tự sắp xếp',
  `created_at` datetime NOT NULL COMMENT 'Thời gian tạo',
  `updated_at` datetime DEFAULT NULL COMMENT 'Thời gian cập nhật cuối'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu trữ thông tin đối tác';

--
-- Đang đổ dữ liệu cho bảng `partner`
--

INSERT INTO `partner` (`id`, `title_vi`, `parent_id`, `module_id`, `is_active`, `h1_content`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `image`, `width`, `height`, `position`, `created_at`, `updated_at`) VALUES
(1, 'Ea aspernatur conseq', 0, 14, 1, 'Neque alias pariatur', '<p>Inventore esse, do r.</p>\r\n', 'ea-aspernatur-conseq', '', '_blank', '/kai/admin/uploads/1750475935_partner.png', 100, 88, 1, '2025-06-21 10:18:55', NULL);

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
-- Cấu trúc bảng cho bảng `project`
--

CREATE TABLE `project` (
  `id` int NOT NULL,
  `title_vi` varchar(255) NOT NULL,
  `parent_id` int DEFAULT '0',
  `module_id` int DEFAULT '8',
  `display_position` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `h1_content` varchar(255) DEFAULT NULL,
  `description_vi` text,
  `content_vi` text,
  `slug_vi` varchar(255) DEFAULT NULL,
  `link_vi` varchar(255) DEFAULT NULL,
  `link_target` varchar(50) DEFAULT '_self',
  `seo_title_vi` varchar(255) DEFAULT NULL,
  `seo_description_vi` text,
  `seo_keywords_vi` text,
  `gallery_images` text,
  `position` int DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `project`
--

INSERT INTO `project` (`id`, `title_vi`, `parent_id`, `module_id`, `display_position`, `is_active`, `h1_content`, `description_vi`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `seo_title_vi`, `seo_description_vi`, `seo_keywords_vi`, `gallery_images`, `position`, `created_at`, `updated_at`) VALUES
(1, 'Harum quia in fugiat', 8, 7, '', 0, 'Nostrud ipsum aliqu', '<p>Duis ducimus, velit .</p>\r\n', '<p>Aperiam id inventore.</p>\r\n', 'harum-quia-in-fugiat', '', '_self', 'Culpa officiis vero', 'Ea consequatur porro', 'Excepturi ipsa reic', '/kai/admin/uploads/1750471239_gallery_0_nha-trang-1-1.jpg', 61, '2025-06-21 09:00:39', NULL);

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
-- Cấu trúc bảng cho bảng `service`
--

CREATE TABLE `service` (
  `id` int NOT NULL,
  `title_vi` varchar(255) NOT NULL,
  `parent_id` int DEFAULT '0',
  `module_id` int DEFAULT '3',
  `display_position` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `h1_content` varchar(255) DEFAULT NULL,
  `description_vi` text,
  `content_vi` text,
  `slug_vi` varchar(255) DEFAULT NULL,
  `link_vi` varchar(255) DEFAULT NULL,
  `link_target` varchar(50) DEFAULT '_self',
  `seo_title_vi` varchar(255) DEFAULT NULL,
  `seo_description_vi` text,
  `seo_keywords_vi` text,
  `gallery_images` text,
  `position` int DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `service`
--

INSERT INTO `service` (`id`, `title_vi`, `parent_id`, `module_id`, `display_position`, `is_active`, `h1_content`, `description_vi`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `seo_title_vi`, `seo_description_vi`, `seo_keywords_vi`, `gallery_images`, `position`, `created_at`, `updated_at`) VALUES
(1, 'Architecto anim eius', 6, 5, '', 1, 'Excepteur velit ut', '<p>Et earum exercitatio.</p>\r\n', '<p>Quia sit duis aut ci.</p>\r\n', 'architecto-anim-eius', '', '_self', 'Ullamco enim minus d', 'Tempore ipsam ullam', 'Optio molestiae tem', '/kai/admin/uploads/1750469870_gallery_0_f6c74a62-9ba7-4fe2-b4c2-5c073a044f40.png', 1, '2025-06-21 08:30:41', NULL);

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
(12, 2, 'loi', '::1', '2025-06-19 13:42:03', 'failed'),
(13, 1, 'admin', '::1', '2025-06-21 08:05:53', 'success'),
(14, 1, 'admin', '::1', '2025-06-21 09:08:58', 'success'),
(15, 1, 'admin', '::1', '2025-06-21 09:23:57', 'success'),
(16, 1, 'admin', '::1', '2025-06-21 09:24:14', 'success'),
(17, 1, 'admin', '::1', '2025-06-21 12:04:03', 'success'),
(18, 2, 'loi', '::1', '2025-06-21 12:33:45', 'success'),
(19, 1, 'admin', '::1', '2025-06-21 12:34:44', 'success');

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
  `is_active` tinyint(1) DEFAULT '1',
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `address_detail` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role_id`, `created_at`, `phone`, `address`, `avatar`, `dob`, `is_active`, `province`, `district`, `ward`, `address_detail`) VALUES
(1, 'admin', 'admin', 'Thắng Rai', 'badaotulong123@gmail.com', 1, '2025-06-17 03:36:20', '0914476792', '161 Thôn 3, Xã Khuê Ngọc Điền, Huyện Krông Bông, Tỉnh Đắk Lắk', '/kai/admin/uploads/1750219074_z4129947384514_d72a106cac6e6e6f85ffe9c60e3fa8cb.jpg', '2003-06-20', 1, 'Tỉnh Đắk Lắk', 'Huyện Krông Bông', 'Xã Khuê Ngọc Điền', '161 Thôn 3'),
(2, 'loi', 'loi', 'Bá Lợi', 'baloi@gmail.com', 3, '2025-06-18 01:38:43', '0914476792', 'thôn 3 số nhà 161', '/kai/admin/uploads/1750210723_yk1.jpg', '2025-06-18', 1, 'Tỉnh Lạng Sơn', 'Huyện Tràng Định', 'Xã Trung Thành', '21B Trần Khắc Trân,');

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
(1026, 1, 1, 1, 1, 1, 1),
(1027, 1, 4, 1, 1, 1, 1),
(1028, 1, 5, 1, 1, 1, 1),
(1029, 1, 9, 1, 1, 1, 1),
(1030, 1, 11, 1, 1, 1, 1),
(1031, 1, 12, 1, 1, 1, 1),
(1032, 1, 13, 1, 1, 1, 1),
(1033, 1, 14, 1, 1, 1, 1),
(1034, 1, 15, 1, 1, 1, 1),
(1035, 1, 16, 1, 1, 1, 1),
(1036, 1, 17, 1, 1, 1, 1),
(1037, 1, 18, 1, 1, 1, 1),
(1038, 1, 19, 1, 1, 1, 1),
(1039, 1, 20, 1, 1, 1, 1),
(1040, 1, 21, 1, 1, 1, 1),
(1041, 1, 22, 1, 1, 1, 1),
(1042, 1, 23, 1, 1, 1, 1),
(1043, 1, 24, 1, 1, 1, 1),
(1044, 1, 25, 1, 1, 1, 1),
(1045, 1, 26, 1, 1, 1, 1),
(1046, 1, 27, 1, 1, 1, 1),
(1047, 1, 28, 1, 1, 1, 1),
(1048, 1, 29, 1, 1, 1, 1),
(1049, 1, 30, 1, 1, 1, 1),
(1050, 1, 31, 1, 1, 1, 1),
(1051, 1, 32, 1, 1, 1, 1),
(1052, 1, 33, 1, 1, 1, 1),
(1053, 1, 34, 1, 1, 1, 1),
(1054, 1, 36, 1, 1, 1, 1),
(1055, 1, 37, 1, 1, 1, 1),
(1056, 1, 38, 1, 1, 1, 1),
(1057, 1, 39, 1, 1, 1, 1),
(1058, 1, 40, 1, 1, 1, 1),
(1059, 1, 41, 1, 1, 1, 1),
(1060, 1, 42, 1, 1, 1, 1),
(1061, 1, 43, 1, 1, 1, 1),
(1062, 1, 44, 1, 1, 1, 1),
(1063, 1, 45, 1, 1, 1, 1),
(1064, 1, 47, 1, 1, 1, 1),
(1065, 1, 48, 1, 1, 1, 1),
(1066, 1, 49, 1, 1, 1, 1),
(1067, 1, 50, 1, 1, 1, 1),
(1068, 1, 51, 1, 1, 1, 1),
(1069, 1, 52, 1, 1, 1, 1),
(1070, 1, 53, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `video`
--

CREATE TABLE `video` (
  `id` int NOT NULL COMMENT 'Mã định danh duy nhất cho video',
  `title_vi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề tiếng Việt',
  `parent_id` int DEFAULT '0' COMMENT 'ID danh mục cha',
  `module_id` int NOT NULL DEFAULT '9' COMMENT 'ID module (9 cho video)',
  `display_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vị trí hiển thị, phân tách bằng dấu phẩy',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Trạng thái hiển thị (1: hiện, 0: ẩn)',
  `h1_content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nội dung thẻ H1',
  `description_vi` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả tiếng Việt',
  `content_vi` text COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung tiếng Việt',
  `slug_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn tiếng Việt',
  `link_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Link YouTube',
  `link_target` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '_self' COMMENT 'Phương thức mở liên kết (_self, _blank)',
  `seo_title_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tiêu đề SEO tiếng Việt',
  `seo_description_vi` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả SEO tiếng Việt',
  `seo_keywords_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Từ khóa SEO tiếng Việt',
  `position` int DEFAULT '0' COMMENT 'Thứ tự sắp xếp',
  `created_at` datetime NOT NULL COMMENT 'Thời gian tạo',
  `updated_at` datetime DEFAULT NULL COMMENT 'Thời gian cập nhật'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu trữ video';

--
-- Đang đổ dữ liệu cho bảng `video`
--

INSERT INTO `video` (`id`, `title_vi`, `parent_id`, `module_id`, `display_position`, `is_active`, `h1_content`, `description_vi`, `content_vi`, `slug_vi`, `link_vi`, `link_target`, `seo_title_vi`, `seo_description_vi`, `seo_keywords_vi`, `position`, `created_at`, `updated_at`) VALUES
(1, 'Quam reprehenderit n', 12, 9, '', 1, 'Ipsum tempora sint', '<p>Deserunt quia ex ex .</p>\r\n', '<p>Laboriosam, eu proid.</p>\r\n', 'Ipsum delectus tem', 'https://www.youtube.com/watch?v=1ui7lF_hz6U', '_blank', 'Aliquam in voluptate', 'Aut omnis cum corpor', 'Aspernatur et repell', 1, '2025-06-21 11:01:38', '2025-06-21 11:01:38');

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
-- Chỉ mục cho bảng `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `customer_feedback`
--
ALTER TABLE `customer_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `gallery`
--
ALTER TABLE `gallery`
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
-- Chỉ mục cho bảng `maps`
--
ALTER TABLE `maps`
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
-- Chỉ mục cho bảng `partner`
--
ALTER TABLE `partner`
  ADD PRIMARY KEY (`id`);

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
-- Chỉ mục cho bảng `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id`);

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
-- Chỉ mục cho bảng `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `column_settings`
--
ALTER TABLE `column_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `content`
--
ALTER TABLE `content`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `customer_feedback`
--
ALTER TABLE `customer_feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Mã định danh duy nhất cho ý kiến khách hàng', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT cho bảng `maps`
--
ALTER TABLE `maps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Mã định danh duy nhất cho bản đồ', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

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
-- AUTO_INCREMENT cho bảng `partner`
--
ALTER TABLE `partner`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Mã định danh duy nhất cho đối tác', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `project`
--
ALTER TABLE `project`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `service`
--
ALTER TABLE `service`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1071;

--
-- AUTO_INCREMENT cho bảng `video`
--
ALTER TABLE `video`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Mã định danh duy nhất cho video', AUTO_INCREMENT=2;

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
