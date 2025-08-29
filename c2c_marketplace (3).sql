-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th8 25, 2025 lúc 05:50 AM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `c2c_marketplace`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(36, 2, 10, 1, '2025-05-23 06:40:27'),
(40, 10, 10, 1, '2025-07-30 07:56:08'),
(41, 10, 11, 1, '2025-07-30 08:01:03'),
(42, 10, 9, 3, '2025-07-30 08:02:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Điện tử', '2025-05-20 09:52:40', '2025-05-21 06:19:26'),
(2, 'Thời trang', '2025-05-20 09:52:40', '2025-05-20 09:52:40'),
(3, 'Đồ gia dụng', '2025-05-20 09:52:40', '2025-05-20 09:52:40'),
(4, 'Sách', '2025-05-20 09:52:40', '2025-05-20 09:52:40'),
(5, 'Khác', '2025-05-20 09:52:40', '2025-05-20 09:52:40'),
(6, 'Hieu truong', '2025-05-20 10:01:33', '2025-05-20 10:01:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chats`
--

CREATE TABLE `chats` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `product_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `chats`
--

INSERT INTO `chats` (`id`, `sender_id`, `receiver_id`, `product_id`, `message`, `created_at`) VALUES
(1, 2, 1, 10, 'zxc', '2025-07-05 16:01:57'),
(2, 2, 1, 10, 'zxc', '2025-07-05 16:02:05'),
(3, 2, 1, 10, 'ho', '2025-07-05 16:02:36'),
(4, 1, 2, 10, 'kk', '2025-07-05 16:15:53'),
(5, 2, 1, 10, '?', '2025-07-05 16:16:18'),
(6, 2, 1, 10, 'z', '2025-07-05 16:18:27'),
(7, 2, 1, 10, '?', '2025-07-05 16:19:57'),
(8, 1, 2, 10, 'zxc', '2025-07-05 16:22:04'),
(9, 2, 1, 10, 'thật k', '2025-07-05 16:26:12'),
(10, 2, 1, 10, 'thật mà', '2025-07-05 16:26:22'),
(11, 2, 1, 10, 'kk?', '2025-07-05 16:26:34'),
(12, 1, 2, 10, 'z', '2025-07-05 16:28:22'),
(13, 1, 2, 10, 'zz', '2025-07-05 16:28:30'),
(14, 1, 2, 10, 'zxcxzc', '2025-07-05 16:28:33'),
(15, 2, 1, 10, 'zxc', '2025-07-05 16:28:35'),
(16, 2, 1, 10, 'zxc', '2025-07-05 16:28:37'),
(17, 2, 1, 10, 'z', '2025-07-05 16:28:42'),
(18, 2, 1, 10, 'z', '2025-07-05 16:28:43'),
(19, 2, 1, 10, 'z', '2025-07-05 16:28:44'),
(20, 2, 1, 10, 'z', '2025-07-05 16:28:44'),
(21, 2, 1, 10, 'z', '2025-07-05 16:28:45'),
(22, 2, 1, 10, 'zxc', '2025-07-05 16:28:46'),
(23, 2, 1, 10, 'zxc', '2025-07-05 16:28:46'),
(24, 2, 1, 10, 'kkk', '2025-07-05 16:31:48'),
(25, 1, 2, 10, 'oke', '2025-07-05 16:38:02'),
(26, 2, 1, 10, 'z', '2025-07-05 16:41:17'),
(27, 2, 1, 10, 'hi', '2025-07-05 16:43:49'),
(28, 1, 2, 10, 'oke', '2025-07-05 16:43:52'),
(29, 1, 2, 10, 'k', '2025-07-05 16:44:06'),
(30, 2, 1, 10, 'k', '2025-07-05 16:44:08'),
(31, 1, 2, 7, 'hi', '2025-07-29 07:11:04'),
(32, 1, 2, 7, '?', '2025-07-29 07:11:21'),
(33, 1, 2, 7, 'kk', '2025-07-29 07:11:43'),
(34, 1, 2, 7, 'um', '2025-07-29 07:13:34'),
(35, 1, 2, 7, 'z', '2025-07-29 07:13:48'),
(36, 1, 2, 7, 'z', '2025-07-29 07:13:50'),
(37, 1, 2, 7, 'hi', '2025-07-29 07:13:55'),
(38, 1, 2, 7, '??', '2025-07-29 07:14:39'),
(39, 2, 1, 7, 'ok', '2025-07-29 07:14:49'),
(40, 2, 1, 7, 'ok', '2025-07-29 07:14:51'),
(41, 2, 1, 7, 'p', '2025-07-29 07:15:08'),
(42, 1, 2, 7, 'k', '2025-07-29 07:18:08'),
(43, 1, 2, 7, 'k', '2025-07-29 07:18:10'),
(44, 2, 1, 7, 'z', '2025-07-29 07:18:16'),
(45, 1, 2, 7, '?', '2025-07-29 07:19:47'),
(46, 1, 2, 7, '?', '2025-07-29 07:19:49'),
(47, 2, 1, 7, 'zxc', '2025-07-29 07:22:37'),
(48, 2, 1, 7, 'hi', '2025-07-29 07:23:09'),
(49, 2, 1, 7, 'hi', '2025-07-29 07:23:11'),
(50, 2, 1, 7, 'kk', '2025-07-29 07:23:19'),
(51, 2, 1, 7, 'kk', '2025-07-29 07:23:22'),
(52, 2, 1, 7, 'e', '2025-07-29 07:23:30'),
(53, 1, 2, 7, '?', '2025-07-29 07:26:33'),
(54, 1, 2, 7, 'zxc', '2025-07-29 07:26:45'),
(55, 1, 2, 7, 'kk', '2025-07-29 07:31:03'),
(56, 2, 1, 7, '?', '2025-07-29 07:31:15'),
(57, 2, 1, 7, 'ê m', '2025-07-29 07:31:24'),
(58, 1, 2, 7, 't nghe nè', '2025-07-29 07:31:33'),
(59, 2, 1, 7, 'không có gì', '2025-07-29 07:31:40'),
(60, 1, 2, 7, 'phải k', '2025-07-29 07:31:52'),
(61, 2, 1, 7, 'k', '2025-07-29 07:34:56'),
(62, 1, 2, 7, '?', '2025-07-29 07:35:11'),
(63, 1, 2, 7, 'heh', '2025-07-29 07:35:19'),
(64, 1, 2, 7, '?', '2025-07-29 08:12:15'),
(65, 1, 2, 7, 'zxc', '2025-07-29 08:12:57'),
(66, 1, 2, 7, 'zxc', '2025-07-29 08:13:43'),
(67, 1, 2, 7, '>', '2025-07-29 08:13:53'),
(68, 1, 2, 7, 'zxc', '2025-07-29 08:14:11'),
(69, 2, 1, 7, '.', '2025-07-29 08:14:50'),
(70, 2, 1, 7, 'ê', '2025-07-29 08:15:33'),
(71, 1, 1, 7, 'l', '2025-07-29 08:16:02'),
(72, 10, 1, 10, '?', '2025-07-29 08:34:21'),
(73, 10, 1, 10, 'zxc', '2025-07-29 08:36:05'),
(74, 10, 1, 10, '?', '2025-07-29 08:37:01'),
(75, 10, 1, 10, 'kk', '2025-07-29 08:42:03'),
(76, 10, 1, 10, 'zxc', '2025-07-29 08:43:33'),
(77, 10, 1, 10, 'zxc', '2025-07-29 08:45:31'),
(78, 1, 10, 10, 'zxc', '2025-07-29 08:46:00'),
(79, 10, 1, 10, '?', '2025-07-29 10:21:55'),
(80, 10, 1, 10, ':D', '2025-07-29 10:22:07'),
(81, 10, 1, 10, 'zxc', '2025-07-29 10:22:28'),
(82, 10, 1, 10, 'hả', '2025-07-29 10:23:18'),
(83, 10, 1, 10, 'hả', '2025-07-29 10:23:23'),
(84, 10, 1, 10, 'zxc', '2025-07-29 10:24:06'),
(85, 10, 1, 10, 'zxc', '2025-07-29 10:26:35'),
(86, 1, 10, 10, 'zxc', '2025-07-29 10:34:14'),
(87, 10, 1, 10, 'thật không má ?', '2025-07-29 10:35:02'),
(88, 10, 1, 10, '?', '2025-07-29 10:40:29'),
(89, 10, 1, 10, 'câm', '2025-07-29 10:42:10'),
(90, 10, 1, 10, '>', '2025-07-29 10:44:11'),
(91, 1, 10, 10, 'kkk', '2025-07-29 10:48:47'),
(92, 10, 1, 10, 'zxc', '2025-07-29 10:48:57'),
(93, 10, 1, 10, 'ê nha má', '2025-07-29 10:53:50'),
(94, 1, 10, 10, 'haha', '2025-07-29 10:54:05'),
(95, 10, 1, 10, '?', '2025-07-29 10:54:15'),
(96, 10, 1, 10, 'cv', '2025-07-29 10:55:16'),
(97, 10, 1, 10, 'zxc', '2025-07-29 10:55:51'),
(98, 10, 1, 10, 'zxc', '2025-07-29 11:00:57'),
(99, 1, 10, 10, 'kk', '2025-07-29 11:01:07'),
(100, 10, 1, 10, 'zxc', '2025-07-29 11:01:18'),
(101, 10, 1, 10, 'hi', '2025-07-29 11:02:09'),
(102, 10, 1, 10, 'kkk', '2025-07-29 11:02:13'),
(103, 10, 1, 10, 'kakakkaa', '2025-07-29 11:05:09'),
(104, 10, 1, 10, 'zxc', '2025-07-29 11:13:53'),
(105, 10, 1, 10, '11', '2025-07-29 11:14:32'),
(106, 10, 1, 10, 'zxc', '2025-07-29 11:16:05'),
(107, 10, 1, 10, '?', '2025-07-29 11:16:53'),
(108, 10, 1, 10, 'zxc', '2025-07-29 11:26:54'),
(109, 10, 1, 10, 'ZX', '2025-07-30 07:24:42'),
(110, 10, 1, 10, 'ê m', '2025-07-30 07:24:47'),
(111, 10, 1, 10, 'hi', '2025-07-30 07:27:46'),
(112, 10, 1, 10, 'zxc', '2025-07-30 07:35:45'),
(113, 10, 1, 10, 'kk', '2025-07-30 07:38:03'),
(114, 1, 10, 10, '?', '2025-07-30 07:41:11'),
(115, 10, 1, 10, 'kk', '2025-07-30 07:41:33'),
(116, 10, 1, 10, 'hi', '2025-07-30 07:43:29'),
(117, 10, 1, 10, 'zxc', '2025-07-30 07:43:50'),
(118, 10, 1, 10, 'lk', '2025-07-30 07:46:36'),
(119, 10, 1, 10, 'zxc', '2025-07-30 07:48:49'),
(120, 10, 1, 10, 'hi', '2025-07-30 07:53:39'),
(121, 10, 1, 10, 'zxc', '2025-07-30 07:55:09'),
(122, 1, 10, 10, 'zxc', '2025-07-30 08:11:24'),
(123, 1, 14, 13, 'hi', '2025-08-04 00:48:57'),
(124, 14, 1, 13, 'xin chào', '2025-08-04 00:50:46'),
(125, 14, 1, 13, '?', '2025-08-04 01:10:28'),
(126, 14, 1, 13, '?', '2025-08-04 01:10:30'),
(127, 14, 1, 13, '?', '2025-08-04 01:10:30'),
(128, 14, 1, 13, '?', '2025-08-04 01:10:30'),
(129, 14, 1, 13, '?', '2025-08-04 01:10:32'),
(130, 14, 1, 13, '?', '2025-08-04 01:10:32'),
(131, 14, 1, 13, 'k', '2025-08-04 01:10:38'),
(132, 14, 1, 13, 'k', '2025-08-04 01:10:40'),
(133, 14, 1, 13, 'k', '2025-08-04 01:10:40'),
(134, 14, 1, 13, 'cc', '2025-08-04 01:14:07'),
(135, 14, 1, 13, 'cc', '2025-08-04 01:14:09'),
(136, 14, 1, 13, 'cc', '2025-08-04 01:14:09'),
(137, 14, 1, 13, '123', '2025-08-04 01:14:18'),
(138, 14, 1, 13, '123', '2025-08-04 01:14:20'),
(139, 14, 1, 13, '123', '2025-08-04 01:14:20'),
(140, 14, 1, 13, '1', '2025-08-04 01:15:16'),
(141, 14, 1, 13, '1', '2025-08-04 01:15:18'),
(142, 14, 1, 13, '1', '2025-08-04 01:15:18'),
(143, 1, 14, 13, '2', '2025-08-04 01:16:08'),
(144, 1, 14, 13, '3', '2025-08-04 01:16:14'),
(145, 1, 14, 13, '4', '2025-08-04 01:16:17'),
(146, 1, 14, 13, '5', '2025-08-04 01:16:27'),
(147, 1, 14, 13, '6', '2025-08-04 01:16:37'),
(148, 1, 14, 13, 'z', '2025-08-04 01:16:56'),
(149, 1, 14, 13, 'zxzxcx', '2025-08-04 01:17:15'),
(150, 14, 1, 13, '1', '2025-08-04 01:17:47'),
(151, 14, 1, 13, '1', '2025-08-04 01:17:49'),
(152, 14, 1, 13, '1', '2025-08-04 01:17:49'),
(153, 14, 1, 13, ',k', '2025-08-04 01:33:04'),
(154, 14, 1, 13, '?', '2025-08-04 01:33:09'),
(155, 1, 14, 13, 'hi', '2025-08-04 01:33:18'),
(156, 1, 14, 13, 'hi', '2025-08-04 01:33:21'),
(157, 1, 14, 13, '21', '2025-08-04 01:35:38'),
(158, 1, 14, 13, '1', '2025-08-04 01:35:46'),
(159, 1, 14, 13, '3', '2025-08-04 01:35:55'),
(160, 14, 1, 13, '1', '2025-08-04 01:42:43'),
(161, 14, 1, 13, '1', '2025-08-04 01:42:45'),
(162, 1, 14, 13, 'kk', '2025-08-04 01:44:29'),
(163, 14, 1, 13, 'phải k', '2025-08-04 01:45:01'),
(164, 14, 1, 13, 'phải k', '2025-08-04 01:45:04'),
(165, 1, 16, 14, 'hi', '2025-08-04 05:47:27'),
(166, 16, 1, 14, 'hi nhe', '2025-08-04 05:47:54'),
(167, 16, 1, 14, 'hi nhe', '2025-08-04 05:47:56'),
(168, 16, 1, 14, 'hi nhe', '2025-08-04 05:47:56'),
(169, 16, 1, 14, 'hi nhe', '2025-08-04 05:47:58'),
(170, 16, 1, 14, 'chao', '2025-08-04 05:48:18'),
(171, 16, 1, 14, 'chao', '2025-08-04 05:48:21'),
(172, 16, 1, 14, 'chao', '2025-08-04 05:48:21'),
(173, 16, 1, 14, 'chao', '2025-08-04 05:48:23'),
(174, 1, 16, 14, 'chao ban', '2025-08-04 05:48:26'),
(175, 1, 16, 14, 'co gi khong', '2025-08-04 05:48:34'),
(176, 1, 16, 14, 'ok', '2025-08-04 05:49:05'),
(177, 16, 1, 14, 'ok', '2025-08-04 05:49:08'),
(178, 16, 1, 14, 'ok', '2025-08-04 05:49:10'),
(179, 1, 16, 14, 'không có gì', '2025-08-04 05:49:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `updated_at`) VALUES
(1, 'ád ád', 'hieucb204@gmail.com', 'Mở lại dịch vụ free hosting đang tạm khóa', 'Mở lại dịch vụ free hosting đang tạm khóa', '2025-05-21 06:30:54', '2025-05-21 06:30:54'),
(2, 'ád ád', 'hieucb204@gmail.com', 'ád ád', 'ád ád', '2025-05-21 07:22:39', '2025-05-21 07:22:39'),
(3, 'hieucb204@gmail.com', 'hieucb204@gmail.com', 'hieucb204@gmail.com', 'hieucb204@gmail.com', '2025-05-21 09:14:25', '2025-05-21 09:14:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `favorites`
--

CREATE TABLE `favorites` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(6, 4, 5, '2025-05-20 14:18:43'),
(14, 10, 11, '2025-07-30 07:56:30'),
(15, 1, 12, '2025-08-04 05:52:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `order_id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, NULL, 2, 'message', 'Thông báo mới', 'zxc', '/chat?product_id=7&seller_id=1', 0, '2025-07-29 15:13:45'),
(2, NULL, 2, 'message', 'Thông báo mới', '>', '/chat?product_id=7&seller_id=1', 0, '2025-07-29 15:13:55'),
(3, NULL, 2, 'message', 'Thông báo mới', 'zxc', '/chat?product_id=7&seller_id=1', 0, '2025-07-29 15:14:13'),
(4, NULL, 1, 'message', 'Thông báo mới', '.', '/chat?product_id=7&seller_id=2', 1, '2025-07-29 15:14:53'),
(5, NULL, 1, 'message', 'Thông báo mới', 'ê', '/chat?product_id=7&seller_id=2', 1, '2025-07-29 15:15:35'),
(6, NULL, 1, 'message', 'Thông báo mới', 'l', '/chat?product_id=7&seller_id=1', 1, '2025-07-29 15:16:04'),
(7, NULL, 10, 'auth', 'Chào mừng bạn', 'Chào mừng bạn đến với Chợ C2C, tes123@gmail.com!', '/profile', 1, '2025-07-29 15:26:28'),
(8, NULL, 10, 'cart', 'Thêm vào giỏ hàng', 'Sản phẩm #11 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-29 15:34:06'),
(9, NULL, 1, 'message', 'Thông báo mới', '?', '/chat?product_id=10&seller_id=10', 1, '2025-07-29 15:34:23'),
(10, NULL, 1, 'message', 'Thông báo mới', 'zxc', '/chat?product_id=10&seller_id=10', 1, '2025-07-29 15:36:07'),
(11, NULL, 1, 'message', 'Thông báo mới', '?', '/chat?product_id=10&seller_id=10', 1, '2025-07-29 15:37:03'),
(12, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', '?', '/chat?product_id=10&seller_id=10', 1, '2025-07-29 15:37:05'),
(13, NULL, 1, 'favorite', 'Thêm vào yêu thích', 'Sản phẩm #7 đã được thêm vào danh sách yêu thích!', '/favorites', 1, '2025-07-29 15:39:34'),
(14, NULL, 1, 'message', 'Thông báo mới', 'kk', '/chat?10/10', 1, '2025-07-29 15:42:05'),
(15, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'kk', '/chat?product_id=10&seller_id=10', 1, '2025-07-29 15:42:07'),
(16, NULL, 1, 'message', 'Thông báo mới', 'zxc', '/chat/10/10', 1, '2025-07-29 15:43:35'),
(17, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'zxc', '/chat/10/10', 1, '2025-07-29 15:43:37'),
(18, NULL, 1, 'message', 'Thông báo mới', 'zxc', '/chat/10/10', 1, '2025-07-29 15:45:33'),
(19, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'zxc', '/chat/10/10', 1, '2025-07-29 15:45:35'),
(20, NULL, 10, 'message', 'Thông báo mới', 'zxc', '/chat/10/1', 1, '2025-07-29 15:46:02'),
(21, NULL, 10, 'message', 'Tin nhắn mới từ Người dùng', 'zxc', '/chat/10/1', 1, '2025-07-29 15:46:04'),
(22, NULL, 1, 'message', 'Thông báo mới', '?', '/chat/10/10', 1, '2025-07-29 17:21:57'),
(23, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', '?', '/chat/10/10', 1, '2025-07-29 17:21:59'),
(24, NULL, 1, 'message', 'Thông báo mới', ':D', '/chat/10/10', 1, '2025-07-29 17:22:09'),
(25, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', ':D', '/chat/10/10', 1, '2025-07-29 17:22:11'),
(26, NULL, 1, 'message', 'Thông báo mới', 'zxc', '/chat/10/10', 1, '2025-07-29 17:22:31'),
(27, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'zxc', '/chat/10/10', 1, '2025-07-29 17:22:33'),
(28, NULL, 1, 'message', 'Thông báo mới', 'hả', '/chat/10/10', 1, '2025-07-29 17:23:20'),
(29, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'hả', '/chat/10/10', 1, '2025-07-29 17:23:22'),
(30, NULL, 1, 'message', 'Thông báo mới', 'hả', '/chat/10/10', 1, '2025-07-29 17:23:25'),
(31, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'hả', '/chat/10/10', 1, '2025-07-29 17:23:27'),
(32, NULL, 1, 'message', 'Thông báo mới', 'zxc', '/chat/10/10', 1, '2025-07-29 17:24:08'),
(33, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'zxc', '/chat/10/10', 1, '2025-07-29 17:24:10'),
(34, NULL, 1, 'message', 'Thông báo mới', 'zxc', '/chat/10/10', 1, '2025-07-29 17:26:37'),
(35, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'zxc', '/chat/10/10', 1, '2025-07-29 17:26:39'),
(36, NULL, 10, 'message', 'Thông báo mới', 'zxc', '/chat/10/1', 1, '2025-07-29 17:34:16'),
(37, NULL, 10, 'message', 'Tin nhắn mới từ Người dùng', 'zxc', '/chat/10/1', 1, '2025-07-29 17:34:18'),
(38, NULL, 1, 'message', 'Thông báo mới', 'thật không má ?', '/chat/10/10', 1, '2025-07-29 17:35:04'),
(39, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', 'thật không má ?', '/chat/10/10', 1, '2025-07-29 17:35:06'),
(40, NULL, 1, 'message', 'Thông báo mới', '?', '/chat/10/10', 1, '2025-07-29 17:40:31'),
(41, NULL, 1, 'message', 'Tin nhắn mới từ Người dùng', '?', '/chat/10/10', 1, '2025-07-29 17:40:33'),
(42, NULL, 10, 'favorite', 'Thêm vào yêu thích', 'Sản phẩm #7 đã được thêm vào danh sách yêu thích!', '/favorites', 1, '2025-07-30 14:32:20'),
(43, NULL, 10, 'cart', 'Thêm vào giỏ hàng', 'Sản phẩm #7 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-30 14:55:32'),
(44, NULL, 10, 'cart', 'Thêm vào giỏ hàng', 'Sản phẩm #10 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-30 14:56:10'),
(45, NULL, 10, 'favorite', 'Thêm vào yêu thích', 'Sản phẩm #11 đã được thêm vào danh sách yêu thích!', '/favorites', 1, '2025-07-30 14:56:32'),
(46, NULL, 10, 'cart', 'Thêm vào giỏ hàng', 'Sản phẩm #11 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-30 15:01:05'),
(47, NULL, 10, 'cart', 'Thêm vào giỏ hàng', 'Sản phẩm  đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-30 15:02:44'),
(48, NULL, 10, 'cart', 'Thêm vào giỏ hàng', 'Sản phẩm  đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-30 15:02:46'),
(49, NULL, 10, 'cart', 'Thêm vào giỏ hàng', 'Sản phẩm Sản phẩm đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-30 15:03:34'),
(50, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'zxc1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-07-30 15:07:39'),
(51, NULL, 14, 'product', 'Sản phẩm mới', 'Sản phẩm \"test\" đã được đăng thành công!', '/store/14', 1, '2025-08-04 07:12:21'),
(52, NULL, 14, 'product', 'Sản phẩm mới', 'Sản phẩm \"test\" đã được đăng thành công!', '/store/14', 1, '2025-08-04 07:12:23'),
(53, NULL, 14, 'product', 'Chỉnh sửa sản phẩm', 'Sản phẩm \"test1\" đã được cập nhật!', '/store/14', 1, '2025-08-04 07:17:35'),
(54, NULL, 14, 'product', 'Chỉnh sửa sản phẩm', 'Sản phẩm \"test1\" đã được cập nhật!', '/store/14', 1, '2025-08-04 07:17:37'),
(55, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 07:50:46'),
(56, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 07:50:48'),
(57, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 08:10:28'),
(58, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 08:10:30'),
(59, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 08:10:38'),
(60, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 08:14:07'),
(61, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 08:14:18'),
(62, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 08:15:16'),
(63, NULL, 1, 'message', 'Tin nhắn mới', 'Bạn nhận được tin nhắn từ doitac@gmail.com', '/partners/message/13/14', 1, '2025-08-04 08:17:47'),
(64, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'test1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-08-04 08:49:17'),
(65, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #116 đã được đặt thành công!', '/order/confirmation/116', 1, '2025-08-04 08:49:43'),
(66, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #116 từ người mua!', '/profile/orders/116', 1, '2025-08-04 08:49:45'),
(67, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #116 đã được cập nhật trạng thái: Đơn hàng đang được xử lý.', '/order/confirmation/116', 1, '2025-08-04 09:25:43'),
(68, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #116 đã được cập nhật trạng thái: Đơn hàng đang được xử lý.', '/order/confirmation/116', 1, '2025-08-04 09:25:46'),
(69, NULL, 15, 'auth', 'Chào mừng đối tác', 'Chào mừng bạn đến với Chợ C2C, doitac1@gmail.com! Vui lòng mua gói nâng cấp để trở thành đối tác chính thức.', '/upgrade', 1, '2025-08-04 12:25:36'),
(70, NULL, 16, 'auth', 'Chào mừng đối tác', 'Chào mừng bạn đến với Chợ C2C, doitac2@gmail.com! Vui lòng mua gói nâng cấp để trở thành đối tác chính thức.', '/upgrade', 1, '2025-08-04 12:43:48'),
(71, NULL, 16, 'auth', 'Nâng cấp thành công', 'Bạn đã trở thành đối tác chính thức!', '/partners', 1, '2025-08-04 12:45:53'),
(72, NULL, 16, 'product', 'Sản phẩm mới', 'Sản phẩm \"testdiu\" đã được đăng thành công!', '/store/16', 1, '2025-08-04 12:46:24'),
(73, NULL, 16, 'product', 'Sản phẩm mới', 'Sản phẩm \"testdiu\" đã được đăng thành công!', '/store/16', 1, '2025-08-04 12:46:26'),
(74, NULL, 16, 'product', 'Chỉnh sửa sản phẩm', 'Sản phẩm \"testdiu1\" đã được cập nhật!', '/store/16', 1, '2025-08-04 12:46:32'),
(75, NULL, 16, 'product', 'Chỉnh sửa sản phẩm', 'Sản phẩm \"testdiu1\" đã được cập nhật!', '/store/16', 1, '2025-08-04 12:46:35'),
(76, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'testdiu1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-08-04 12:50:19'),
(77, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #117 đã được đặt thành công!', '/order/confirmation/117', 1, '2025-08-04 12:50:31'),
(78, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #117 đã được đặt thành công!', '/order/confirmation/117', 1, '2025-08-04 12:50:33'),
(79, NULL, 16, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #117 từ người mua!', '/partners/orders/117', 0, '2025-08-04 12:50:33'),
(80, NULL, 16, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #117 từ người mua!', '/partners/orders/117', 0, '2025-08-04 12:50:35'),
(81, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #117 đã được cập nhật trạng thái: Đơn hàng đã được giao thành công.', '/order/confirmation/117', 1, '2025-08-04 12:51:21'),
(82, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #117 đã được cập nhật trạng thái: Đơn hàng đã được giao thành công.', '/order/confirmation/117', 1, '2025-08-04 12:51:23'),
(83, NULL, 1, 'favorite', 'Thêm vào yêu thích', 'Sản phẩm #12 đã được thêm vào danh sách yêu thích!', '/favorites', 1, '2025-08-04 12:52:49'),
(84, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #118 đã được đặt thành công!', '/order/confirmation/118', 1, '2025-08-05 15:09:34'),
(85, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #118 đã được đặt thành công!', '/order/confirmation/118', 1, '2025-08-05 15:09:37'),
(86, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #118 từ người mua!', '/partners/orders/118', 1, '2025-08-05 15:09:37'),
(87, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #118 từ người mua!', '/partners/orders/118', 1, '2025-08-05 15:09:39'),
(88, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #118 đã được cập nhật trạng thái: Đơn hàng đã được giao thành công.', '/order/confirmation/118', 1, '2025-08-05 15:10:10'),
(89, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #118 đã được cập nhật trạng thái: Đơn hàng đã được giao thành công.', '/order/confirmation/118', 1, '2025-08-05 15:10:12'),
(90, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'test1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-08-05 15:33:59'),
(91, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #120 đã được đặt thành công!', '/order/confirmation/120', 1, '2025-08-05 15:37:32'),
(92, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #120 đã được đặt thành công!', '/order/confirmation/120', 1, '2025-08-05 15:37:34'),
(93, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #120 từ người mua!', '/partners/orders/120', 1, '2025-08-05 15:37:34'),
(94, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #120 từ người mua!', '/partners/orders/120', 1, '2025-08-05 15:37:36'),
(95, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #120 đã được cập nhật trạng thái: Đơn hàng đã được giao thành công.', '/order/confirmation/120', 1, '2025-08-05 15:37:57'),
(96, NULL, 1, 'order', 'Cập nhật đơn hàng', 'Đơn hàng #120 đã được cập nhật trạng thái: Đơn hàng đã được giao thành công.', '/order/confirmation/120', 1, '2025-08-05 15:37:59'),
(97, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'test1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-08-05 16:02:27'),
(98, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #121 đã được đặt thành công!', '/order/confirmation/121', 1, '2025-08-05 16:02:37'),
(99, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #121 đã được đặt thành công!', '/order/confirmation/121', 1, '2025-08-05 16:02:39'),
(100, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #121 từ người mua!', '/partners/orders/121', 1, '2025-08-05 16:02:39'),
(101, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #121 từ người mua!', '/partners/orders/121', 1, '2025-08-05 16:02:41'),
(102, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'test1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-08-05 16:20:03'),
(103, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'test1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-08-05 16:20:55'),
(104, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #123 đã được đặt thành công!', '/order/confirmation/123', 1, '2025-08-05 16:21:04'),
(105, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #123 đã được đặt thành công!', '/order/confirmation/123', 1, '2025-08-05 16:21:06'),
(106, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #123 từ người mua!', '/partners/orders/123', 1, '2025-08-05 16:21:06'),
(107, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #123 từ người mua!', '/partners/orders/123', 1, '2025-08-05 16:21:08'),
(108, NULL, 1, 'cart', 'Thêm vào giỏ hàng', 'test1 đã được thêm vào giỏ hàng!', '/cart', 1, '2025-08-05 17:25:28'),
(109, NULL, 1, 'order', 'Đặt hàng thành công', 'Đơn hàng #124 đã được đặt thành công!', '/order/confirmation/124', 1, '2025-08-05 17:25:35'),
(110, NULL, 14, 'order', 'Đơn hàng mới', 'Bạn có đơn hàng mới #124 từ người mua!', '/partners/orders/124', 1, '2025-08-05 17:25:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `seller_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','pending_payment','confirmed') DEFAULT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `carrier` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `total_price`, `status`, `tracking_number`, `carrier`, `created_at`, `updated_at`) VALUES
(105, 1, 2, 7, 2, 4400.00, 'pending', NULL, NULL, '2025-05-24 04:10:59', '2025-05-24 04:10:59'),
(106, 1, 2, 7, 1, 2200.00, 'pending', NULL, NULL, '2025-05-24 04:26:23', '2025-05-24 04:26:23'),
(107, 1, 2, 7, 1, 2200.00, 'pending', NULL, NULL, '2025-05-24 04:27:14', '2025-05-24 04:27:14'),
(108, 1, 2, 7, 1, 2200.00, 'pending', NULL, NULL, '2025-05-24 04:28:23', '2025-05-24 04:28:23'),
(109, 1, 2, 7, 1, 2200.00, 'pending', NULL, NULL, '2025-05-24 04:36:58', '2025-05-24 04:36:58'),
(110, 1, 2, 7, 1, 2200.00, 'pending', NULL, NULL, '2025-05-24 04:41:48', '2025-05-24 04:41:48'),
(111, 1, 2, 7, 1, 2200.00, 'pending', NULL, NULL, '2025-05-24 04:42:08', '2025-05-24 04:42:08'),
(112, 1, 2, 7, 1, 2200.00, 'confirmed', NULL, NULL, '2025-05-24 04:43:49', '2025-05-24 04:52:50'),
(113, 1, 2, 7, 1, 2200.00, 'pending_payment', NULL, NULL, '2025-05-24 04:53:57', '2025-05-24 04:53:58'),
(114, 1, 2, 7, 1, 2200.00, 'confirmed', NULL, NULL, '2025-05-24 05:01:16', '2025-05-24 05:07:38'),
(115, 1, 2, 7, 1, 2200.00, 'confirmed', NULL, NULL, '2025-05-24 05:12:07', '2025-07-30 12:39:02'),
(116, 1, 14, 13, 1, 22000.00, 'cancelled', NULL, NULL, '2025-08-04 01:49:40', '2025-08-05 07:18:45'),
(117, 1, 16, 14, 1, 22000.00, 'delivered', '', '', '2025-08-04 05:50:31', '2025-08-04 05:51:21'),
(118, 1, 14, 13, 2, 44000.00, 'delivered', '', '', '2025-08-05 08:09:34', '2025-08-05 08:10:10'),
(120, 1, 14, 13, 1, 22000.00, 'delivered', '', '', '2025-08-05 08:37:32', '2025-08-05 08:37:57'),
(121, 1, 14, 13, 1, 22000.00, 'pending', NULL, NULL, '2025-08-05 09:02:37', '2025-08-05 09:02:37'),
(122, 1, 14, 13, 1, 22000.00, 'pending', NULL, NULL, '2025-08-05 09:20:20', '2025-08-05 09:20:20'),
(123, 1, 14, 13, 1, 22000.00, 'pending', NULL, NULL, '2025-08-05 09:21:04', '2025-08-05 09:21:04'),
(124, 1, 14, 13, 1, 22000.00, 'pending', NULL, NULL, '2025-08-05 10:25:35', '2025-08-05 10:25:35');

--
-- Bẫy `orders`
--
DELIMITER $$
CREATE TRIGGER `update_potential_score_after_order` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    UPDATE products p
    JOIN (
        SELECT product_id, SUM(quantity) as sales
        FROM orders
        WHERE status = 'delivered' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY product_id
    ) o ON p.id = o.product_id
    LEFT JOIN (
        SELECT product_id, AVG(rating) as avg_rating
        FROM seller_ratings sr
        JOIN orders o ON sr.seller_id = o.seller_id
        WHERE o.status = 'delivered' AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY product_id
    ) sr ON p.id = sr.product_id
    SET p.potential_score = (COALESCE(o.sales, 0) * 0.6 + COALESCE(sr.avg_rating, 0) * 40)
    WHERE p.id = NEW.product_id AND NEW.status = 'delivered';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_detail`
--

CREATE TABLE `order_detail` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `state` varchar(100) NOT NULL,
  `town_city` varchar(100) NOT NULL,
  `house_no` varchar(255) NOT NULL,
  `road_name` varchar(255) NOT NULL,
  `landmark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `order_detail`
--

INSERT INTO `order_detail` (`id`, `order_id`, `fullname`, `phone`, `pincode`, `state`, `town_city`, `house_no`, `road_name`, `landmark`) VALUES
(102, 105, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(103, 106, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(104, 107, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(105, 108, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(106, 109, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(107, 110, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(108, 111, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', '0941518881', '0941518881'),
(109, 112, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', '0941518881', '0941518881'),
(110, 113, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(111, 114, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(112, 115, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(113, 116, 'hieudep', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'zxc'),
(114, 117, 'Hiếu Trương', '0941518881', '123', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(115, 118, 'Hiếu Trương', '0941518881', '123', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(116, 120, 'ád ád', '0941518881', '123', 'ád ád', 'da nang', 'ád ád', 'ád ád', 'ád ád'),
(117, 121, 'hieudep', '0941518881', '50217', 'hieudep', 'hieudep', 'hieudep', 'hieudep', 'hieudep'),
(118, 122, 'Hiếu Trương', '0941518881', '50217', 'Đà Nẵng', 'da nang', 'xc1231', 'Hiếu Trương', 'Hiếu Trương'),
(119, 123, 'ád ád', '0941518881', '123', 'ád ád', 'da nang', 'ád ád', 'ád ád', 'ád ád'),
(120, 124, 'ád ád', '0941518881', '123', 'ád ád', 'da nang', 'ád ád', 'ád ád', 'ád ád');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment`
--

CREATE TABLE `payment` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `payment_method` enum('cod','payos') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `payment`
--

INSERT INTO `payment` (`id`, `order_id`, `payment_method`, `transaction_id`, `amount`, `status`, `created_at`) VALUES
(99, 105, 'cod', NULL, 4400.00, 'pending', '2025-05-24 04:10:59'),
(100, 106, 'payos', NULL, 2200.00, 'pending', '2025-05-24 04:26:23'),
(101, 107, 'payos', NULL, 2200.00, 'pending', '2025-05-24 04:27:14'),
(102, 108, 'payos', NULL, 2200.00, 'pending', '2025-05-24 04:28:23'),
(103, 109, 'payos', NULL, 2200.00, 'pending', '2025-05-24 04:36:58'),
(104, 110, 'payos', NULL, 2200.00, 'pending', '2025-05-24 04:41:48'),
(105, 111, 'payos', NULL, 2200.00, 'pending', '2025-05-24 04:42:08'),
(106, 112, 'payos', NULL, 2200.00, 'completed', '2025-05-24 04:43:49'),
(107, 113, 'payos', NULL, 2200.00, 'pending', '2025-05-24 04:53:57'),
(108, 114, 'payos', NULL, 2200.00, 'completed', '2025-05-24 05:01:16'),
(109, 115, 'payos', '69a35d76f4f445b2955d7fcbae129906', 2200.00, 'completed', '2025-05-24 05:12:07'),
(110, 116, 'cod', NULL, 22000.00, 'pending', '2025-08-04 01:49:41'),
(111, 117, 'cod', NULL, 22000.00, 'pending', '2025-08-04 05:50:31'),
(112, 118, 'cod', NULL, 44000.00, 'pending', '2025-08-05 08:09:34'),
(113, 120, 'cod', NULL, 22000.00, 'pending', '2025-08-05 08:37:32'),
(114, 121, 'cod', NULL, 22000.00, 'pending', '2025-08-05 09:02:37'),
(115, 122, 'cod', NULL, 22000.00, 'pending', '2025-08-05 09:20:20'),
(116, 123, 'cod', NULL, 22000.00, 'pending', '2025-08-05 09:21:04'),
(117, 124, 'cod', NULL, 22000.00, 'pending', '2025-08-05 10:25:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `views` int DEFAULT '0',
  `potential_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `category_id` int NOT NULL,
  `seller_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `user_id`, `title`, `description`, `price`, `status`, `created_at`, `image`, `is_featured`, `views`, `potential_score`, `category_id`, `seller_id`) VALUES
(5, 1, 'Sản phẩm mẫu 1', 'Mô tả sản phẩm mẫu 1', 1000000.00, 'approved', '2025-05-18 09:24:04', 'product-2-1.jpg', 1, 215, 0.00, 1, 1),
(6, 1, 'Sản phẩm mẫu 2', 'Mô tả sản phẩm mẫu 2', 2000000.00, 'approved', '2025-05-18 09:24:04', 'product-2-1.jpg', 0, 211, 0.00, 2, 1),
(7, 2, 'zxc1', 'zxc', 2000.00, 'approved', '2025-05-18 10:44:37', 'product-1-1.jpg', 0, 33, 0.00, 1, 2),
(9, 1, 'xcxzc', 'xcxzc1', 2000.00, 'approved', '2025-05-19 05:10:27', 'product-1-3.jpg  ', 0, 4, 0.00, 4, 1),
(10, 1, 'Sản phẩm 1', 'Tiêu đề ', 31000.00, 'approved', '2025-05-20 09:06:45', '682c462599607_certificate-TC-2341.png', 0, 47, 0.00, 5, 1),
(11, 1, 'zxc', 'zxc', 2000.00, 'approved', '2025-05-20 10:03:47', '682c53833842d_127.0.0.1_8000_admin_categories_add.png', 0, 39, 0.00, 6, 1),
(12, 5, 'spmoi1', 'spmoi', 31000.00, 'approved', '2025-05-21 09:09:11', '682d9837cc036_product-16.jpg', 0, 35, 0.00, 1, 5),
(13, 14, 'test1', 'test', 20000.00, 'approved', '2025-08-04 00:12:20', '688ffae4d33e7_主12.jpg', 0, 49, 7.00, 1, 14),
(14, 16, 'testdiu1', 'testdiu', 20000.00, 'approved', '2025-08-04 05:46:24', '689049308b18b_主12.jpg', 0, 5, 0.00, 1, 16);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reports`
--

CREATE TABLE `reports` (
  `id` int NOT NULL,
  `reported_user_id` int NOT NULL,
  `reason` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `reports`
--

INSERT INTO `reports` (`id`, `reported_user_id`, `reason`, `created_at`) VALUES
(1, 2, 'no no ', '2025-05-20 06:04:21'),
(4, 1, 'zxc', '2025-05-20 14:23:37'),
(5, 2, 'xzc', '2025-05-21 07:19:54'),
(6, 5, 'sp k tot', '2025-05-21 09:13:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `seller_ratings`
--

CREATE TABLE `seller_ratings` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `seller_id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text,
  `reply` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Đang đổ dữ liệu cho bảng `seller_ratings`
--

INSERT INTO `seller_ratings` (`id`, `order_id`, `seller_id`, `buyer_id`, `rating`, `comment`, `reply`, `created_at`, `updated_at`) VALUES
(1, 0, 1, 2, 5, 'Người bán giao hàng nhanh, hỗ trợ tốt!', NULL, '2025-05-18 11:19:36', '2025-08-04 05:23:31'),
(2, 0, 5, 1, 5, 'zxc', NULL, '2025-08-04 03:16:56', '2025-08-04 05:23:31'),
(3, 0, 14, 1, 5, 'rất tốt', NULL, '2025-08-04 03:52:40', '2025-08-04 05:23:31'),
(4, 0, 14, 1, 5, 'zxc', NULL, '2025-08-04 04:47:25', '2025-08-04 05:23:31'),
(5, 0, 14, 1, 5, 'zxc', NULL, '2025-08-04 04:47:33', '2025-08-04 05:23:31'),
(6, 0, 14, 1, 5, 'zxc', NULL, '2025-08-04 04:47:54', '2025-08-04 05:23:31'),
(7, 0, 14, 1, 5, '123', NULL, '2025-08-04 04:48:47', '2025-08-04 05:23:31'),
(8, 0, 14, 1, 5, 'kakak', 'zxc', '2025-08-04 04:55:52', '2025-08-04 05:23:44'),
(9, 0, 16, 1, 3, 'zxc', NULL, '2025-08-04 05:47:09', '2025-08-04 05:47:09'),
(10, 0, 16, 1, 5, 'xzc', 'zcx', '2025-08-04 05:47:16', '2025-08-04 05:49:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','completed','failed','pending_payment','cancelled') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `transaction_id` varchar(100) DEFAULT NULL,
  `order_code` bigint DEFAULT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `amount`, `payment_method`, `status`, `created_at`, `transaction_id`, `order_code`, `user_id`) VALUES
(18, 105, 4400.00, 'cod', 'pending', '2025-05-24 04:10:59', NULL, NULL, NULL),
(19, 112, 2200.00, 'payos', 'pending', '2025-05-24 04:43:52', 'bd4d4bf9f9e7431e8913aca85a5d6bbf', 112, NULL),
(20, 112, 2200.00, 'payos', 'completed', '2025-05-24 04:52:50', NULL, 112, NULL),
(21, 113, 2200.00, 'payos', 'pending', '2025-05-24 04:53:58', '581590b539854805bc853073bb675541', 113, NULL),
(22, 114, 2200.00, 'payos', 'pending', '2025-05-24 05:01:18', 'bc2594cd2f4144cc9633a03cf42daf34', 114, NULL),
(23, 114, 2200.00, 'payos', 'completed', '2025-05-24 05:07:38', NULL, 114, NULL),
(28, NULL, 20000.00, 'payos', 'completed', '2025-07-30 12:37:40', 'a2ef5da3ca664955a429b5239e8222f7', 8790590013, 13),
(30, NULL, 20000.00, 'payos', 'completed', '2025-07-30 12:38:34', '69a35d76f4f445b2955d7fcbae129906', 8791130013, 13),
(31, NULL, 2000.00, 'payos', 'pending', '2025-08-03 06:52:44', '5d67b18abdda495982d8e115a15c1605', 2039610014, 14),
(32, NULL, 2000.00, 'payos', 'completed', '2025-08-03 06:53:59', NULL, 2039610014, 14),
(33, NULL, 2000.00, 'payos', 'pending', '2025-08-03 07:01:10', '248ffac7e45f4264a79be7986e6e57dd', 2044680014, 14),
(34, NULL, 2000.00, 'payos', 'pending', '2025-08-03 07:01:12', '7d6336b3427546b48c293f53a01e0c1b', 2044700014, 14),
(35, NULL, 2000.00, 'payos', 'completed', '2025-08-03 07:02:20', NULL, 2044700014, 14),
(36, NULL, 2000.00, 'payos', 'pending', '2025-08-03 07:04:22', '7045bd7fcfb8474c9da17371c9c8cba7', 2046610014, 14),
(37, NULL, 2000.00, 'payos', 'completed', '2025-08-03 07:06:31', NULL, 2046610014, 14),
(38, 116, 22000.00, 'cod', 'pending', '2025-08-04 01:49:41', NULL, NULL, NULL),
(39, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:25:47', '69390bdf4dff49658957e0fc42b6fa0f', 2851470015, 15),
(40, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:26:05', 'c7cbbf42229f46a88090dd062a2b49e1', 2851650015, 15),
(41, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:26:32', '76667b498a4c4b148083a78f7140dd6f', 2851920015, 15),
(42, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:26:40', 'a19512c8c7e3428f8be43bb30f7625f0', 2851990015, 15),
(43, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:27:01', '9b225506ab5e4406aaef3f7accdade59', 2852210015, 15),
(44, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:27:21', '6fe6c93ecc5542d6961822653604550b', 2852400015, 15),
(45, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:27:40', '027ce89acc624949952b980350595134', 2852590015, 15),
(46, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:29:46', 'f5b6450597654fd397a401ccf3ad2786', 2853850015, 15),
(47, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:39:31', 'b319b441e7e144da9658def2842765ea', 2859700015, 15),
(48, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:40:51', '5f02f308476b4412886e2bcb76df9b7f', 2860500015, 15),
(49, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:42:24', 'd1e34fbd6aca468ea5c91db14990755e', 2861440015, 15),
(50, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:42:51', '9122e97b85494fdab6f9577f8fcae50b', 2861700015, 15),
(51, NULL, 2000.00, 'payos', 'pending', '2025-08-04 05:43:55', '0bf9e8c422054626a4b0f50340c2b3b5', 2862340016, 16),
(52, NULL, 2000.00, 'payos', 'completed', '2025-08-04 05:45:51', NULL, 2862340016, 16),
(53, 117, 22000.00, 'cod', 'pending', '2025-08-04 05:50:31', NULL, NULL, NULL),
(54, 118, 44000.00, 'cod', 'pending', '2025-08-05 08:09:34', NULL, NULL, NULL),
(55, 120, 22000.00, 'cod', 'pending', '2025-08-05 08:37:32', NULL, NULL, NULL),
(56, 121, 22000.00, 'cod', 'pending', '2025-08-05 09:02:37', NULL, NULL, NULL),
(57, 122, 22000.00, 'cod', 'pending', '2025-08-05 09:20:20', NULL, NULL, NULL),
(58, 123, 22000.00, 'cod', 'pending', '2025-08-05 09:21:04', NULL, NULL, NULL),
(59, 124, 22000.00, 'cod', 'pending', '2025-08-05 10:25:35', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `userrating`
--

CREATE TABLE `userrating` (
  `id` int NOT NULL,
  `rater_id` int NOT NULL,
  `rated_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `userrating`
--

INSERT INTO `userrating` (`id`, `rater_id`, `rated_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 5, 4, 'zxc', '2025-08-04 10:18:07'),
(2, 1, 5, 5, 'um um', '2025-08-04 10:43:52'),
(3, 1, 5, 5, '1', '2025-08-04 10:51:29'),
(4, 1, 5, 3, 'zxc', '2025-08-04 12:54:25'),
(5, 1, 2, 5, 'z', '2025-08-04 12:55:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','partners') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `average_rating` decimal(3,2) DEFAULT '0.00',
  `rating_count` int DEFAULT '0',
  `is_active` int NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `is_partner_paid` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `average_rating`, `rating_count`, `is_active`, `reset_token`, `reset_token_expires`, `google_id`, `is_partner_paid`) VALUES
(1, 'admin@gmail.com', 'admin@gmail.com', '$2y$10$DVwCgVI5PoXckikH4rMmUewgoPMkxh1Cx6NMKORK1rKdU0TrDwTqO', 'admin', '2025-05-18 09:23:52', 0.00, 0, 0, NULL, NULL, NULL, 0),
(2, 'test@gmail.com', 'test@gmail.com', '$2y$10$V/hWgIL5VpZIcnpz6bR1HuM2v2IE9I/BjEo5i6Utmhnn9N2kKc1O2', 'user', '2025-05-18 10:37:24', 0.00, 0, 0, NULL, NULL, NULL, 0),
(4, 'tes11t@gmail.com', 'tes11t@gmail.com', '$2y$10$HJiUXaTUQgaJnYBW4GcxUu272CvrsTTWi23lpUx8Ezs1unZ6Dj4Ue', 'user', '2025-05-20 10:12:19', 0.00, 0, 1, NULL, NULL, NULL, 0),
(5, 'hieucv204@gmail.com', 'hieucv204@gmail.com', '$2y$10$8MMBML6Jx8Cgz7NXRFO78uS4NZyYBiNPRu1obaSmI60dUTSJDnD1.', 'user', '2025-05-21 06:09:30', 0.00, 0, 0, NULL, NULL, '107437331923282843239', 0),
(9, 'hieucv2004@gmail.com', 'hieucv2004@gmail.com', '$2y$10$o0vjEY27wWaE6ag4xQ/tj.8cwfZYVhpS9ceBlCEuFnKaaCvDfOfTS', 'user', '2025-05-21 08:46:46', 0.00, 0, 0, '6e7a7bf117b638bdf8a7b62bb0a7bee83cf2a4bfd0e5257121e4571ae3f2a637', '2025-05-21 16:53:59', '110396125575346962995', 0),
(10, 'tes123@gmail.com', 'tes123@gmail.com', '$2y$10$qOsBEtCKLyl3b7F1kLhOceDPIk26TRzhV0isHtgtxCjJB2oX0ET1a', 'user', '2025-07-29 08:26:26', 0.00, 0, 0, NULL, NULL, NULL, 0),
(11, 'hieucv2zxc04@gmail.com', 'hieucv2zxc04@gmail.com', '$2y$10$GVOeuw3YIhpKWngJfWOx1.Sr0JFV8VnkTj4I/G6qFM/JtZhGE0nxG', 'partners', '2025-07-30 12:23:01', 0.00, 0, 0, NULL, NULL, NULL, 0),
(13, 'zxcx@gmail.com', 'zxcx@gmail.com', '$2y$10$sW1YvluJQJK4/agmLanuUus8yFX6ekj5B4BgFoBFCEQ2fGHY9HV.m', 'partners', '2025-07-30 12:27:52', 0.00, 0, 0, NULL, NULL, NULL, 0),
(14, 'doitac@gmail.com', 'doitac@gmail.com', '$2y$10$xuIHuTun3U88nq/iwrt6Q.WO4OHK/vpZ.VPjKAEXVF/T9smR.BZNm', 'partners', '2025-08-03 06:51:10', 0.00, 0, 0, NULL, NULL, NULL, 1),
(15, 'doitac1@gmail.com', 'doitac1@gmail.com', '$2y$10$greP07ZPTHpUiSsY/qMI.Ov4qcBs75braYPguD2NkHpMX0H2vOSvG', 'partners', '2025-08-04 05:25:34', 0.00, 0, 0, NULL, NULL, NULL, 0),
(16, 'doitac2@gmail.com', 'doitac2@gmail.com', '$2y$10$lm72M6xNSZYS5Lro5Mt4XO9DG/yLJMMcG2qYlX.e4AHokyYa2h07S', 'partners', '2025-08-04 05:43:46', 0.00, 0, 0, NULL, NULL, NULL, 1);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_chats_product_id` (`product_id`),
  ADD KEY `idx_chats_sender_receiver` (`sender_id`,`receiver_id`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `products_ibfk_2` (`category_id`),
  ADD KEY `products_ibfk_3` (`seller_id`);

--
-- Chỉ mục cho bảng `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_user_id` (`reported_user_id`);

--
-- Chỉ mục cho bảng `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transactions_user_id` (`user_id`),
  ADD KEY `transactions_ibfk_1` (`order_id`);

--
-- Chỉ mục cho bảng `userrating`
--
ALTER TABLE `userrating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rater_id` (`rater_id`),
  ADD KEY `rated_id` (`rated_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT cho bảng `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `seller_ratings`
--
ALTER TABLE `seller_ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT cho bảng `userrating`
--
ALTER TABLE `userrating`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ràng buộc cho bảng `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ràng buộc cho bảng `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ràng buộc cho bảng `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ràng buộc cho bảng `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`);

--
-- Ràng buộc cho bảng `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD CONSTRAINT `seller_ratings_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `seller_ratings_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`);

--
-- Ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `userrating`
--
ALTER TABLE `userrating`
  ADD CONSTRAINT `userrating_ibfk_1` FOREIGN KEY (`rater_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `userrating_ibfk_2` FOREIGN KEY (`rated_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
