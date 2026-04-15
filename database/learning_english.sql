-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 14, 2026 at 05:19 PM
-- Server version: 9.1.0
-- PHP Version: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `learning_english`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_warnings`
--

DROP TABLE IF EXISTS `academic_warnings`;
CREATE TABLE IF NOT EXISTS `academic_warnings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `schedule_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'absence_threshold',
  `trigger_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_class` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `schedule_id` int DEFAULT NULL COMMENT 'Gắn với buổi học cụ thể',
  `lesson_id` int DEFAULT NULL,
  `course_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `attached_file` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File đề đính kèm',
  `deadline` datetime DEFAULT NULL COMMENT 'Hạn chót nộp bài',
  `assignment_type` enum('post_class','pre_class','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post_class',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `course_id` (`course_id`),
  KEY `fk_assignments_schedule` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `schedule_id` int NOT NULL,
  `student_id` int NOT NULL,
  `status` enum('present','absent','late','excused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'absent',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_att` (`schedule_id`,`student_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_schedule` (`schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `schedule_id`, `student_id`, `status`, `note`, `created_at`) VALUES
(1, 336, 1, 'excused', 'Nghỉ học có phép', '2026-04-12 19:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Tiếng Anh Giao Tiếp', 'Các khóa học giúp cải thiện kỹ năng nghe nói và phản xạ thực tế.', '2026-03-20 00:16:16'),
(2, 'Luyện thi IELTS', 'Tổng hợp kiến thức và chiến thuật chinh phục kỳ thi IELTS từ con số 0.', '2026-03-20 00:16:16'),
(3, 'Luyện thi TOEIC', 'Luyện thi TOEIC 4 kỹ năng chuẩn format mới nhất của IIG.', '2026-03-20 00:16:16'),
(4, 'Ngữ pháp cơ bản', 'Lấy lại nền tảng ngữ pháp cho người mới bắt đầu hoặc hoàn toàn mất gốc.', '2026-03-20 00:16:16');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
CREATE TABLE IF NOT EXISTS `classes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `instructor_id` int DEFAULT NULL,
  `class_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_capacity` int NOT NULL DEFAULT '40',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `fk_classes_instructor` (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `course_id`, `instructor_id`, `class_name`, `max_capacity`, `start_date`, `end_date`, `created_at`) VALUES
(2, 4, 3, 'IELTS 01', 40, '2026-03-30', '2026-04-17', '2026-03-29 13:33:50'),
(4, 1, 22, 'Cơ bản 01', 40, '2026-03-30', '2026-04-24', '2026-03-30 16:48:09'),
(7, 1, 3, 'Giao tiếp Cơ bản - Sáng 246', 30, '2026-04-01', '2026-05-30', '2026-04-10 16:59:24'),
(8, 2, 3, 'English for Office - Tối 246', 25, '2026-04-01', '2026-05-30', '2026-04-10 16:59:24'),
(9, 3, 3, 'TOEIC Cấp tốc - Tối 357', 40, '2026-04-01', '2026-05-30', '2026-04-10 16:59:24'),
(10, 4, 3, 'IELTS Masterclass - Sáng 357', 20, '2026-04-01', '2026-05-30', '2026-04-10 16:59:24'),
(11, 5, 3, 'Ngữ pháp trọn bộ - Cuối tuần', 50, '2026-04-01', '2026-05-30', '2026-04-10 16:59:24');

-- --------------------------------------------------------

--
-- Table structure for table `class_details`
--

DROP TABLE IF EXISTS `class_details`;
CREATE TABLE IF NOT EXISTS `class_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `detail_name` varchar(255) NOT NULL,
  `max_students` int NOT NULL DEFAULT '20',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `class_details`
--

INSERT INTO `class_details` (`id`, `class_id`, `detail_name`, `max_students`, `created_at`) VALUES
(1, 2, 'IELTS 01 - Sáng - 2/4/6', 20, '2026-03-30 20:36:08'),
(2, 2, 'IELTS 01 - Tối - 2/4/6', 20, '2026-03-30 20:36:29'),
(3, 2, 'IELTS 01 - Tối - 3/5/7', 20, '2026-03-30 20:36:53'),
(4, 2, 'IELTS 01 - Sáng - 3/5/7', 20, '2026-03-30 20:37:06'),
(5, 4, 'Cơ bản 01 - Tối - 3/5/7', 20, '2026-03-30 20:44:24'),
(6, 2, 'IELTS 01 - Tối - 2/4/6', 20, '2026-03-30 20:44:45'),
(7, 4, 'Cơ bản 01 - Chiều - 3/5/7', 20, '2026-03-31 16:15:02'),
(8, 4, 'Cơ bản 01 - Sáng - 2/4/6', 20, '2026-03-31 16:15:14'),
(11, 7, 'Ca Sáng (08:00 - 09:30)', 30, '2026-04-10 16:59:24'),
(12, 8, 'Ca Tối (18:00 - 19:30)', 25, '2026-04-10 16:59:24'),
(13, 9, 'Ca Tối (19:45 - 21:15)', 40, '2026-04-10 16:59:24'),
(14, 10, 'Ca Sáng (10:00 - 11:30)', 20, '2026-04-10 16:59:24'),
(15, 11, 'Ca Chiều (14:00 - 16:00)', 50, '2026-04-10 16:59:24');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

DROP TABLE IF EXISTS `consultations`;
CREATE TABLE IF NOT EXISTS `consultations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','contacted','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `full_name`, `phone`, `email`, `note`, `status`, `created_at`) VALUES
(1, 'Hoàng Nhật Trường', '234234234234', 'zayluon@gmail.com', 'bvbcvbcvbcvbc', 'contacted', '2026-03-27 18:43:19'),
(2, 'Nguyễn Thị Mai', '0901112233', 'mai.nguyen@gmail.com', 'Mình muốn tư vấn khóa IELTS từ con số 0.', 'pending', '2026-03-20 01:30:00'),
(3, 'Trần Văn Hùng', '0912223344', 'hungtran88@yahoo.com', 'Trung tâm có khóa học giao tiếp buổi tối không?', 'pending', '2026-03-20 02:15:00'),
(4, 'Lê Ngọc Ánh', '0983334455', '', 'Tư vấn giúp em lộ trình học TOEIC 650+.', 'resolved', '2026-03-21 03:00:00'),
(5, 'Phạm Tuấn Kiệt', '0934445566', 'kietpham.it@gmail.com', 'Học phí khóa tiếng Anh doanh nghiệp là bao nhiêu?', 'pending', '2026-03-21 07:20:00'),
(6, 'Hoàng Thu Hương', '0975556677', '', 'Tôi muốn đăng ký cho con 7 tuổi học.', 'pending', '2026-03-22 01:00:00'),
(7, 'Đặng Minh Trí', '0966667788', 'tri.dang@outlook.com', 'Lịch học lớp giao tiếp thứ 7, CN.', 'resolved', '2026-03-22 08:45:00'),
(8, 'Vũ Thị Lan', '0907778899', 'lanvu.dev@gmail.com', 'Có lớp 1 kèm 1 ôn thi cấp tốc không?', 'pending', '2026-03-23 02:10:00'),
(9, 'Bùi Quốc Bảo', '0918889900', '', 'Mình cần tư vấn test đầu vào IELTS.', 'pending', '2026-03-23 04:30:00'),
(10, 'Ngô Thanh Vân', '0989990011', 'van.ngo@gmail.com', 'Cho mình hỏi về giáo viên bản xứ.', 'pending', '2026-03-24 06:20:00'),
(11, 'Dương Trọng Đại', '0930001122', '', 'Trung tâm ở đâu vậy ạ?', 'resolved', '2026-03-24 09:00:00'),
(12, 'Lý Tiểu Long', '0971112233', 'longly@gmail.com', 'Học phí đóng 1 lần hay trả góp được không?', 'pending', '2026-03-25 01:45:00'),
(13, 'Châu Tinh Trì', '0962223344', 'chautinhtri@yahoo.com', 'Tư vấn khóa phát âm chuẩn IPA.', 'resolved', '2026-03-25 03:30:00'),
(14, 'Đinh Ngọc Diệp', '0903334455', '', 'Mình bị mất gốc tiếng Anh lâu năm.', 'contacted', '2026-03-26 07:15:00'),
(15, 'Trương Vô Kỵ', '0914445566', 'voky.truong@gmail.com', 'Có hỗ trợ thi lại nếu không đạt target không?', 'contacted', '2026-03-26 08:50:00'),
(16, 'Lâm Tâm Như', '0985556677', 'tamnhu.lam@outlook.com', 'Gửi cho mình tài liệu khóa học tham khảo trước nhé.', 'contacted', '2026-03-27 02:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_replied` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `full_name`, `email`, `message`, `is_replied`, `created_at`) VALUES
(2, 'Nguyễn Văn Nam', 'nam.nguyen99@gmail.com', 'Cho mình hỏi trung tâm có khóa IELTS online không? Lịch học như thế nào?', 0, '2026-03-25 02:30:00'),
(3, 'Lê Thị Hoa', 'hoale_88@yahoo.com', 'Mình muốn đăng ký cho con học lớp giao tiếp tiếng Anh trẻ em. Trung tâm tư vấn giúp.', 1, '2026-03-25 07:15:00'),
(4, 'Trần Minh Tuấn', 'tuan.tran.work@outlook.com', 'Xin chào, công ty mình đang cần tìm đối tác dạy tiếng Anh doanh nghiệp cho khoảng 20 nhân viên.', 0, '2026-03-26 03:05:00'),
(5, 'Phạm Thu Hà', 'hapham2k@gmail.com', 'Em muốn test đầu vào thì có cần đặt lịch trước không ạ? Phí test là bao nhiêu?', 1, '2026-03-26 09:40:00'),
(6, 'Hoàng Trọng Phúc', 'phuc.hoang@icloud.com', 'Học phí khóa TOEIC 4 kỹ năng là bao nhiêu vậy trung tâm? Có được học thử không?', 1, '2026-03-27 01:20:00'),
(7, 'Đỗ Mỹ Linh', 'mylinh.do95@gmail.com', 'Mình bị mất gốc tiếng Anh hoàn toàn, học khóa nào thì phù hợp để giao tiếp cơ bản?', 1, '2026-03-27 04:50:00'),
(8, 'Vũ Hải Đăng', 'dangvu_pro@gmail.com', 'Trung tâm có chi nhánh ở khu vực quận 1 hoặc quận 3 không ạ?', 0, '2026-03-27 08:10:00'),
(9, 'Ngô Gia Bảo', 'giabao.ngo@yahoo.com.vn', 'Cho em xin lộ trình học chi tiết của khóa tiếng Anh giao tiếp dành cho người đi làm.', 0, '2026-03-28 01:00:00'),
(10, 'Bùi Tích Kim', 'kim.bui@gmail.com', 'Website mình bị lỗi phần đăng ký tài khoản, cứ báo lỗi mạng miết. Admin kiểm tra giúp nhé.', 1, '2026-03-28 02:45:00'),
(11, 'Lý Phương Châu', 'chau.lyphuong@gmail.com', 'Mình cần thủ tục bảo lưu khóa học do bận đi công tác đột xuất 2 tháng.', 0, '2026-03-28 03:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `path_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `path_id` (`path_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `category_id`, `path_id`, `title`, `description`, `image_url`, `level`, `fee`, `is_featured`, `created_at`) VALUES
(1, 1, NULL, 'Giao tiếp cho người mất gốc', 'Lấy lại nền tảng phát âm chuẩn IPA và tự tin giao tiếp cơ bản chỉ sau 30 ngày.', 'http://localhost/DoAnEnglish2026/backend/uploads/course-images/course_20260328_175531_32d15c83.webp', 'A1', 0.00, 0, '2026-03-20 00:16:29'),
(2, 1, NULL, 'Tiếng Anh Giao tiếp Văn phòng', 'Ứng dụng trong môi trường công sở: viết email chuẩn thương mại, họp hành và thuyết trình.', 'http://localhost/DoAnEnglish2026/backend/uploads/course-images/course_20260328_175338_59f65da3.webp', 'A2', 2000000.00, 1, '2026-03-20 00:16:29'),
(3, 3, NULL, 'Luyện thi TOEIC 650+ Cấp Tốc', 'Giải đề thực chiến, hướng dẫn mẹo tránh bẫy Part 5, 6, 7 cực kỳ hiệu quả tiết kiệm thời gian.', 'http://localhost/DoAnEnglish2026/backend/uploads/course-images/course_20260328_175300_bd344360.png', 'B1', 599000.00, 1, '2026-03-20 00:16:29'),
(4, 2, NULL, 'IELTS Masterclass 7.0 (Writing & Speaking)', 'Nâng band điểm chuyên sâu cho 2 kỹ năng \"khó nhằn\" nhất trong bài thi IELTS với cựu giám khảo.', 'http://localhost/DoAnEnglish2026/backend/uploads/course-images/course_20260328_175154_17c570d3.webp', 'B2', 1250000.00, 1, '2026-03-20 00:16:29'),
(5, 4, 8, '12 Thì Tiếng Anh Trọn Bộ', 'Học hiểu bản chất, tuyệt đối không học vẹt, ghi nhớ trọn đời cách dùng 12 thì trong tiếng Anh.', 'http://localhost/DoAnEnglish2026/backend/uploads/course-images/course_20260328_175521_70a58b1f.png', 'A1', 676500.00, 1, '2026-03-20 00:16:29');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `class_detail_id` int DEFAULT NULL,
  `status` enum('active','completed','dropped') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `enrollment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `fk_enrollments_class_detail` (`class_detail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `class_id`, `class_detail_id`, `status`, `enrollment_date`) VALUES
(1, 1, 2, 4, 'active', '2026-03-31 10:06:52'),
(2, 8, 2, 2, 'active', '2026-03-31 10:55:53');

-- --------------------------------------------------------

--
-- Table structure for table `learning_paths`
--

DROP TABLE IF EXISTS `learning_paths`;
CREATE TABLE IF NOT EXISTS `learning_paths` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `target_audience` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `learning_paths`
--

INSERT INTO `learning_paths` (`id`, `title`, `description`, `target_audience`, `created_at`) VALUES
(1, 'Lộ trình mất gốc 3 tháng', 'Lấy lại căn bản tiếng Anh', 'Người đi làm, sinh viên', '2026-03-27 20:16:24'),
(2, 'IELTS 5.0 - 6.5', 'Lộ trình IELTS trung cấp', 'Học sinh cấp 3, sinh viên', '2026-03-27 20:16:24'),
(3, 'TOEIC 500+ Cấp tốc', 'Luyện thi TOEIC trong 1 tháng', 'Sinh viên sắp ra trường', '2026-03-27 20:16:24'),
(4, 'Giao tiếp phản xạ', 'Tập trung 100% nghe nói', 'Người đi làm', '2026-03-27 20:16:24'),
(5, 'Tiếng Anh Doanh nghiệp', 'Viết email, họp hành, đàm phán', 'Nhân viên văn phòng', '2026-03-27 20:16:24'),
(6, 'IELTS 7.0+ Chuyên sâu', 'Luyện kỹ năng khó Speaking/Writing', 'Người cần đi du học', '2026-03-27 20:16:24'),
(7, 'Phát âm chuẩn IPA', 'Chuẩn hóa phát âm Mỹ', 'Mọi đối tượng', '2026-03-27 20:16:24'),
(8, 'Ngữ pháp toàn diện', 'Hệ thống toàn bộ ngữ pháp', 'Học sinh THCS/THPT', '2026-03-27 20:16:24'),
(9, 'Tiếng Anh Trẻ em - Starter', 'Làm quen tiếng Anh cho bé', 'Trẻ em 6-8 tuổi', '2026-03-27 20:16:24'),
(10, 'Tiếng Anh Trẻ em - Mover', 'Nâng cao từ vựng cho bé', 'Trẻ em 9-11 tuổi', '2026-03-27 20:16:24'),
(11, 'Luyện Nghe VOA/BBC', 'Tăng phản xạ nghe tin tức', 'Trình độ Intermediate', '2026-03-27 20:16:24'),
(12, 'Luyện Đọc Dịch', 'Dịch tài liệu chuyên ngành', 'Sinh viên IT, Kinh tế', '2026-03-27 20:16:24'),
(13, 'IELTS Reading Master', 'Tối đa điểm Reading', 'Sĩ tử IELTS', '2026-03-27 20:16:24'),
(14, 'IELTS Listening Master', 'Mẹo nghe bắt keyword', 'Sĩ tử IELTS', '2026-03-27 20:16:24'),
(15, 'Giao tiếp du lịch', 'Các tình huống ở sân bay, khách sạn', 'Người hay đi du lịch', '2026-03-27 20:16:24'),
(16, 'Tiếng Anh phỏng vấn', 'Trả lời phỏng vấn xin việc', 'Người tìm việc', '2026-03-27 20:16:24'),
(18, 'Từ vựng cốt lõi', '3000 từ vựng Oxford', 'Người mới bắt đầu', '2026-03-27 20:16:24'),
(19, 'Luyện thi THPT Quốc gia', 'Bám sát đề thi Bộ Giáo Dục', 'Học sinh lớp 12', '2026-03-27 20:16:24'),
(20, 'Tiếng Anh Y Khoa', 'Từ vựng ngành Y', 'Bác sĩ, sinh viên Y', '2026-03-27 20:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `class_id` int DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `student_id`, `class_id`, `reason`, `start_date`, `end_date`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'fjgggggggggg', '2026-04-16', '2026-04-16', 'approved', '', '2026-04-13 01:52:25', '2026-04-13 02:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_number` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `video_url`, `order_number`, `created_at`) VALUES
(1, 1, 'Bài 1: Bảng chữ cái và Chuẩn hóa Phát âm IPA', NULL, 1, '2026-04-10 17:13:28'),
(2, 1, 'Bài 2: Chào hỏi và Giới thiệu bản thân ấn tượng', NULL, 2, '2026-04-10 17:13:28'),
(3, 1, 'Bài 3: Số đếm, Ngày tháng và Cách nói thời gian', NULL, 3, '2026-04-10 17:13:28'),
(4, 1, 'Bài 4: Nói về Sở thích và Thói quen hàng ngày', NULL, 4, '2026-04-10 17:13:28'),
(5, 1, 'Bài 5: Từ vựng chủ đề Gia đình và Các mối quan hệ', NULL, 5, '2026-04-10 17:13:28'),
(6, 1, 'Bài 6: Giao tiếp nơi công sở: Nghề nghiệp và Nơi làm việc', NULL, 6, '2026-04-10 17:13:28'),
(7, 1, 'Bài 7: Chủ đề Ẩm thực: Đồ ăn và Thức uống', NULL, 7, '2026-04-10 17:13:28'),
(8, 1, 'Bài 8: Cách hỏi đường và Di chuyển bằng phương tiện công cộng', NULL, 8, '2026-04-10 17:13:28'),
(9, 1, 'Bài 9: Mua sắm: Hỏi giá và Kỹ năng mặc cả', NULL, 9, '2026-04-10 17:13:28'),
(10, 1, 'Bài 10: Mô tả Ngoại hình và Tính cách con người', NULL, 10, '2026-04-10 17:13:28'),
(11, 1, 'Bài 11: Biểu đạt Cảm xúc và Trạng thái cá nhân', NULL, 11, '2026-04-10 17:13:28'),
(12, 1, 'Bài 12: Lên kế hoạch cho các hoạt động giải trí cuối tuần', NULL, 12, '2026-04-10 17:13:28'),
(13, 1, 'Bài 13: Nói về Thời tiết và Các mùa trong năm', NULL, 13, '2026-04-10 17:13:28'),
(14, 1, 'Bài 14: Tiếng Anh Du lịch: Lên kế hoạch cho kỳ nghỉ', NULL, 14, '2026-04-10 17:13:28'),
(15, 1, 'Bài 15: Tại nhà hàng: Đặt bàn, Gọi món và Thanh toán', NULL, 15, '2026-04-10 17:13:28'),
(16, 1, 'Bài 16: Sức khỏe: Mô tả triệu chứng khi đi khám bệnh', NULL, 16, '2026-04-10 17:13:28'),
(17, 1, 'Bài 17: Giao tiếp tại Ngân hàng và Bưu điện', NULL, 17, '2026-04-10 17:13:28'),
(18, 1, 'Bài 18: Kỹ năng Giao tiếp qua điện thoại cơ bản', NULL, 18, '2026-04-10 17:13:28'),
(19, 1, 'Bài 19: Giải quyết các tình huống khẩn cấp', NULL, 19, '2026-04-10 17:13:28'),
(20, 1, 'Bài 20: Tổng ôn Phản xạ toàn khóa & Kiểm tra cuối kỳ', NULL, 20, '2026-04-10 17:13:28'),
(21, 2, 'Bài 1: Cấu trúc một Email thương mại chuyên nghiệp', NULL, 1, '2026-04-10 17:13:28'),
(22, 2, 'Bài 2: Viết email: Phản hồi và Yêu cầu thông tin', NULL, 2, '2026-04-10 17:13:28'),
(23, 2, 'Bài 3: Viết email: Báo cáo công việc và Phàn nàn', NULL, 3, '2026-04-10 17:13:28'),
(24, 2, 'Bài 4: Telephone English: Để lại và Nhận lời nhắn', NULL, 4, '2026-04-10 17:13:28'),
(25, 2, 'Bài 5: Telephone English: Xử lý sự cố qua điện thoại', NULL, 5, '2026-04-10 17:13:28'),
(26, 2, 'Bài 6: Chào đón khách hàng và Đối tác nước ngoài', NULL, 6, '2026-04-10 17:13:28'),
(27, 2, 'Bài 7: Giới thiệu Hồ sơ Công ty và Sản phẩm (Company Profile)', NULL, 7, '2026-04-10 17:13:28'),
(28, 2, 'Bài 8: Meetings: Bắt đầu và Điều phối cuộc họp', NULL, 8, '2026-04-10 17:13:28'),
(29, 2, 'Bài 9: Meetings: Đưa ra ý kiến và Phản biện lịch sự', NULL, 9, '2026-04-10 17:13:28'),
(30, 2, 'Bài 10: Meetings: Đạt được thỏa thuận và Chốt biên bản', NULL, 10, '2026-04-10 17:13:28'),
(31, 2, 'Bài 11: Presentations: Mở đầu bài thuyết trình ấn tượng', NULL, 11, '2026-04-10 17:13:28'),
(32, 2, 'Bài 12: Presentations: Mô tả Biểu đồ và Số liệu kinh doanh', NULL, 12, '2026-04-10 17:13:28'),
(33, 2, 'Bài 13: Presentations: Kỹ năng xử lý phần Hỏi Đáp (Q&A)', NULL, 13, '2026-04-10 17:13:28'),
(34, 2, 'Bài 14: Negotiations: Các nguyên tắc Đàm phán cơ bản', NULL, 14, '2026-04-10 17:13:28'),
(35, 2, 'Bài 15: Negotiations: Thương lượng giá cả và Hợp đồng', NULL, 15, '2026-04-10 17:13:28'),
(36, 2, 'Bài 16: Giao tiếp tại các sự kiện Networking (Small Talk)', NULL, 16, '2026-04-10 17:13:28'),
(37, 2, 'Bài 17: Kỹ năng trả lời Phỏng vấn tuyển dụng bằng Tiếng Anh', NULL, 17, '2026-04-10 17:13:28'),
(38, 2, 'Bài 18: Đánh giá hiệu suất công việc (Performance Review)', NULL, 18, '2026-04-10 17:13:28'),
(39, 2, 'Bài 19: Lên kế hoạch và Báo cáo Dự án (Project Planning)', NULL, 19, '2026-04-10 17:13:28'),
(40, 2, 'Bài 20: Thuyết trình Dự án cuối khóa (Final Project)', NULL, 20, '2026-04-10 17:13:28'),
(41, 3, 'Bài 1: Format đề thi TOEIC chuẩn IIG & Chiến thuật Part 1', NULL, 1, '2026-04-10 17:13:28'),
(42, 3, 'Bài 2: Listening Part 1: Tranh tả người & Tranh tả vật', NULL, 2, '2026-04-10 17:13:28'),
(43, 3, 'Bài 3: Listening Part 2: Xử lý nhanh câu hỏi Who/What/Where', NULL, 3, '2026-04-10 17:13:28'),
(44, 3, 'Bài 4: Listening Part 2: Xử lý câu hỏi When/Why/How', NULL, 4, '2026-04-10 17:13:28'),
(45, 3, 'Bài 5: Listening Part 2: Bẫy câu hỏi Yes/No & Câu hỏi Đuôi', NULL, 5, '2026-04-10 17:13:28'),
(46, 3, 'Bài 6: Reading Part 5: Nhận diện Từ loại (Danh, Động, Tính, Trạng)', NULL, 6, '2026-04-10 17:13:28'),
(47, 3, 'Bài 7: Reading Part 5: Tuyệt chiêu giải nhanh Thì của động từ', NULL, 7, '2026-04-10 17:13:28'),
(48, 3, 'Bài 8: Reading Part 5: Phân biệt Giới từ và Liên từ', NULL, 8, '2026-04-10 17:13:28'),
(49, 3, 'Bài 9: Listening Part 3: Bắt Keyword Hội thoại chốn công sở', NULL, 9, '2026-04-10 17:13:28'),
(50, 3, 'Bài 10: Listening Part 3: Hội thoại chủ đề Mua sắm & Du lịch', NULL, 10, '2026-04-10 17:13:28'),
(51, 3, 'Bài 11: Listening Part 3: Dạng bài kết hợp Biểu đồ & Lịch trình', NULL, 11, '2026-04-10 17:13:28'),
(52, 3, 'Bài 12: Reading Part 6: Kỹ năng điền câu và Tư duy ngữ cảnh', NULL, 12, '2026-04-10 17:13:28'),
(53, 3, 'Bài 13: Listening Part 4: Các dạng Thông báo (Announcements)', NULL, 13, '2026-04-10 17:13:28'),
(54, 3, 'Bài 14: Listening Part 4: Bản tin tức (News Broadcasts)', NULL, 14, '2026-04-10 17:13:28'),
(55, 3, 'Bài 15: Listening Part 4: Lời nhắn thoại (Recorded Messages)', NULL, 15, '2026-04-10 17:13:28'),
(56, 3, 'Bài 16: Reading Part 7: Dịch nhanh Đoạn văn đơn (Email & Thư tín)', NULL, 16, '2026-04-10 17:13:28'),
(57, 3, 'Bài 17: Reading Part 7: Đoạn văn đơn (Quảng cáo & Thông báo)', NULL, 17, '2026-04-10 17:13:28'),
(58, 3, 'Bài 18: Reading Part 7: Chiến thuật giải Đoạn văn kép (Double Passages)', NULL, 18, '2026-04-10 17:13:28'),
(59, 3, 'Bài 19: Reading Part 7: Kỹ năng Scan tìm ý Đoạn văn ba (Triple Passages)', NULL, 19, '2026-04-10 17:13:28'),
(60, 3, 'Bài 20: Giải đề thi thử TOEIC thực chiến (Full Mock Test)', NULL, 20, '2026-04-10 17:13:28'),
(61, 4, 'Bài 1: Speaking Part 1 - Cải thiện Fluency & Coherence', NULL, 1, '2026-04-10 17:13:28'),
(62, 4, 'Bài 2: Speaking Part 1 - Công thức mở rộng câu trả lời', NULL, 2, '2026-04-10 17:13:28'),
(63, 4, 'Bài 3: Writing Task 1 - Cách viết Overview & Phân tích Line Graph', NULL, 3, '2026-04-10 17:13:28'),
(64, 4, 'Bài 4: Writing Task 1 - Nhóm số liệu Bar Chart & Pie Chart', NULL, 4, '2026-04-10 17:13:28'),
(65, 4, 'Bài 5: Writing Task 1 - Xử lý Table & Multiple Charts', NULL, 5, '2026-04-10 17:13:28'),
(66, 4, 'Bài 6: Writing Task 1 - Từ vựng miêu tả Map & Process', NULL, 6, '2026-04-10 17:13:28'),
(67, 4, 'Bài 7: Speaking Part 2 - Lên dàn ý bài nói 2 phút trong 1 phút', NULL, 7, '2026-04-10 17:13:28'),
(68, 4, 'Bài 8: Speaking Part 2 - Ứng dụng Idioms & Advanced Vocabulary', NULL, 8, '2026-04-10 17:13:28'),
(69, 4, 'Bài 9: Speaking Part 2 - Xử lý chủ đề Describe a Person/Place', NULL, 9, '2026-04-10 17:13:28'),
(70, 4, 'Bài 10: Speaking Part 2 - Xử lý chủ đề Describe an Event/Object', NULL, 10, '2026-04-10 17:13:28'),
(71, 4, 'Bài 11: Writing Task 2 - Cấu trúc chuẩn Opinion Essay', NULL, 11, '2026-04-10 17:13:28'),
(72, 4, 'Bài 12: Writing Task 2 - Dàn bài Discussion Essay', NULL, 12, '2026-04-10 17:13:28'),
(73, 4, 'Bài 13: Writing Task 2 - Dạng bài Advantages & Disadvantages', NULL, 13, '2026-04-10 17:13:28'),
(74, 4, 'Bài 14: Writing Task 2 - Dạng bài Problem & Solution', NULL, 14, '2026-04-10 17:13:28'),
(75, 4, 'Bài 15: Writing Task 2 - Kỹ năng Brainstorming phát triển ý tưởng', NULL, 15, '2026-04-10 17:13:28'),
(76, 4, 'Bài 16: Writing Task 2 - Ăn điểm với Câu ghép và Câu phức', NULL, 16, '2026-04-10 17:13:28'),
(77, 4, 'Bài 17: Speaking Part 3 - Kỹ năng tranh luận và giải thích sâu', NULL, 17, '2026-04-10 17:13:28'),
(78, 4, 'Bài 18: Speaking Part 3 - Chủ đề Xã hội và Môi trường', NULL, 18, '2026-04-10 17:13:28'),
(79, 4, 'Bài 19: Speaking Part 3 - Chủ đề Công nghệ và Tương lai', NULL, 19, '2026-04-10 17:13:28'),
(80, 4, 'Bài 20: Final Mock Test & Chấm điểm 1-1 (Speaking & Writing)', NULL, 20, '2026-04-10 17:13:28'),
(81, 5, 'Bài 1: Bản chất của Hiện tại đơn (Present Simple)', NULL, 1, '2026-04-10 17:13:28'),
(82, 5, 'Bài 2: Hiện tại tiếp diễn (Present Continuous) & Động từ trạng thái', NULL, 2, '2026-04-10 17:13:28'),
(83, 5, 'Bài 3: Bài tập phân biệt Hiện tại đơn vs Hiện tại tiếp diễn', NULL, 3, '2026-04-10 17:13:28'),
(84, 5, 'Bài 4: Quá khứ đơn (Past Simple) & Quy tắc đuôi -ed', NULL, 4, '2026-04-10 17:13:28'),
(85, 5, 'Bài 5: Quá khứ tiếp diễn (Past Continuous) - Hành động cắt ngang', NULL, 5, '2026-04-10 17:13:28'),
(86, 5, 'Bài 6: Bài tập phân biệt Quá khứ đơn vs Quá khứ tiếp diễn', NULL, 6, '2026-04-10 17:13:28'),
(87, 5, 'Bài 7: Hiện tại hoàn thành (Present Perfect) - Kết quả và Trải nghiệm', NULL, 7, '2026-04-10 17:13:28'),
(88, 5, 'Bài 8: Tránh nhầm lẫn Hiện tại hoàn thành vs Quá khứ đơn', NULL, 8, '2026-04-10 17:13:28'),
(89, 5, 'Bài 9: Dấu hiệu nhận biết Hiện tại hoàn thành tiếp diễn', NULL, 9, '2026-04-10 17:13:28'),
(90, 5, 'Bài 10: Ôn tập chặng 1: Nhóm thì Hiện tại và Quá khứ', NULL, 10, '2026-04-10 17:13:28'),
(91, 5, 'Bài 11: Quá khứ hoàn thành (Past Perfect) - Trước một hành động khác', NULL, 11, '2026-04-10 17:13:28'),
(92, 5, 'Bài 12: Quá khứ hoàn thành tiếp diễn (Past Perfect Continuous)', NULL, 12, '2026-04-10 17:13:28'),
(93, 5, 'Bài 13: Tương lai đơn (Future Simple) & Tương lai gần (Be going to)', NULL, 13, '2026-04-10 17:13:28'),
(94, 5, 'Bài 14: Tương lai tiếp diễn (Future Continuous)', NULL, 14, '2026-04-10 17:13:28'),
(95, 5, 'Bài 15: Tương lai hoàn thành (Future Perfect)', NULL, 15, '2026-04-10 17:13:28'),
(96, 5, 'Bài 16: Tương lai hoàn thành tiếp diễn (Future Perfect Continuous)', NULL, 16, '2026-04-10 17:13:28'),
(97, 5, 'Bài 17: Nguyên tắc Sự phối hợp thì (Sequence of Tenses) - Phần 1', NULL, 17, '2026-04-10 17:13:28'),
(98, 5, 'Bài 18: Nguyên tắc Sự phối hợp thì (Sequence of Tenses) - Phần 2', NULL, 18, '2026-04-10 17:13:28'),
(99, 5, 'Bài 19: Tuyệt chiêu nhận diện thì qua Trạng từ chỉ thời gian', NULL, 19, '2026-04-10 17:13:28'),
(100, 5, 'Bài 20: Bài kiểm tra tổng hợp 12 Thì trong tiếng Anh', NULL, 20, '2026-04-10 17:13:28');

-- --------------------------------------------------------

--
-- Table structure for table `makeup_registrations`
--

DROP TABLE IF EXISTS `makeup_registrations`;
CREATE TABLE IF NOT EXISTS `makeup_registrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL COMMENT 'ID học viên đăng ký học bù',
  `target_schedule_id` int NOT NULL COMMENT 'ID của ca học (schedule) mà học viên muốn tham gia',
  `leave_request_id` int DEFAULT NULL COMMENT 'ID đơn xin nghỉ',
  `status` enum('registered','cancelled','attended','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'registered',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `target_schedule_id` (`target_schedule_id`),
  KEY `fk_makeup_leave_request` (`leave_request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `makeup_registrations`
--

INSERT INTO `makeup_registrations` (`id`, `student_id`, `target_schedule_id`, `leave_request_id`, `status`, `admin_note`, `created_at`) VALUES
(1, 1, 367, 1, 'rejected', '', '2026-04-12 19:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
CREATE TABLE IF NOT EXISTS `materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `schedule_id` int DEFAULT NULL COMMENT 'Gắn với buổi học cụ thể',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_type` enum('document','audio','video','link') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'document',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `fk_materials_schedule` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `question_type` enum('matching','multiple_choice','fill_blank','dictation','writing') COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `audio_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Gợi ý giải bài',
  `explanation` text COLLATE utf8mb4_unicode_ci COMMENT 'Giải thích chi tiết sau khi làm xong',
  `order_num` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_type`, `question_text`, `audio_url`, `hint`, `explanation`, `order_num`, `created_at`) VALUES
(1, 1, 'multiple_choice', 'Which word best describes speaking naturally without unnatural pauses?', NULL, 'Bắt đầu bằng chữ F', NULL, 1, '2026-04-12 17:44:07'),
(2, 1, 'multiple_choice', 'Choose the best synonym for \"important\" to get a higher score:', NULL, 'Từ này thường dùng trong văn phong học thuật', NULL, 2, '2026-04-12 17:44:07'),
(3, 1, 'multiple_choice', 'Which idiom means \"very happy\"?', NULL, 'Liên quan đến mặt trăng', NULL, 3, '2026-04-12 17:44:07'),
(4, 1, 'multiple_choice', 'Choose the correct preposition: \"I am really keen ___ reading books.\"', NULL, 'Đi với giới từ ON', NULL, 4, '2026-04-12 17:44:07'),
(5, 1, 'multiple_choice', 'I _____ living in this big city for 5 years.', NULL, 'Thì hiện tại hoàn thành tiếp diễn', NULL, 5, '2026-04-12 17:44:07'),
(6, 1, 'fill_blank', 'Fill in ONE word: I usually _____ out with my friends on weekends.', NULL, NULL, NULL, 6, '2026-04-12 17:44:07'),
(7, 1, 'fill_blank', 'Fill in ONE word: My hometown is famous _____ its traditional food.', NULL, NULL, NULL, 7, '2026-04-12 17:44:07'),
(8, 1, 'fill_blank', 'Fill in ONE word: To improve coherence, you should use _____ words like \"however\" or \"therefore\".', NULL, NULL, NULL, 8, '2026-04-12 17:44:07'),
(9, 2, 'writing', 'Write a short response (3-4 sentences) introducing your hometown, focusing on fluency markers.', NULL, 'Sử dụng \"Well, to be honest...\"', NULL, 1, '2026-04-12 17:44:07'),
(10, 2, 'writing', 'What do you usually do in your free time? Write 50-80 words.', NULL, 'Dùng các từ nối first of all, furthermore.', NULL, 2, '2026-04-12 17:44:07'),
(21, 5, 'multiple_choice', 'What should you write in the FIRST paragraph of Task 1?', NULL, NULL, NULL, 1, '2026-04-12 17:44:07'),
(22, 5, 'multiple_choice', 'Which word means \"to go up\"?', NULL, NULL, NULL, 2, '2026-04-12 17:44:07'),
(23, 5, 'multiple_choice', 'What is the synonym for \"fluctuate\"?', NULL, NULL, NULL, 3, '2026-04-12 17:44:07'),
(24, 5, 'multiple_choice', 'The number of visitors _____ a peak in 2010.', NULL, NULL, NULL, 4, '2026-04-12 17:44:07'),
(25, 5, 'multiple_choice', 'There was a _____ decline in car sales last year.', NULL, NULL, NULL, 5, '2026-04-12 17:44:07'),
(26, 5, 'fill_blank', 'Fill in ONE word: Overall, it is clear _____ both countries experienced an upward trend.', NULL, NULL, NULL, 6, '2026-04-12 17:44:07'),
(27, 5, 'fill_blank', 'Fill in ONE word: The figure _____ the USA remained stable over the period.', NULL, NULL, NULL, 7, '2026-04-12 17:44:07'),
(28, 5, 'fill_blank', 'Fill in ONE word: Sales increased _____ 20% to 50% in just two years.', NULL, NULL, NULL, 8, '2026-04-12 17:44:07'),
(29, 6, 'writing', 'Write an \"Overall\" paragraph for a line graph showing the number of tourists visiting Paris from 2010 to 2020.', NULL, NULL, NULL, 1, '2026-04-12 17:44:07'),
(30, 6, 'writing', 'Paraphrase this prompt: \"The chart below shows the amount of money spent on books in Germany, France, and Italy in 2015.\"', NULL, NULL, NULL, 2, '2026-04-12 17:44:07'),
(31, 7, 'multiple_choice', 'Listen to the speaker. What is his primary hobby?', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 'Chú ý từ khóa về giải trí', NULL, 1, '2026-04-12 18:22:44'),
(32, 7, 'dictation', 'Listen and type exactly what you hear.', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 'Câu này có 5 chữ', NULL, 2, '2026-04-12 18:22:44'),
(33, 7, 'multiple_choice', 'Where does the woman want to go?', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', NULL, NULL, 3, '2026-04-12 18:22:44'),
(34, 7, 'dictation', 'Listen and complete the sentence.', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', NULL, NULL, 4, '2026-04-12 18:22:44'),
(35, 7, 'multiple_choice', 'What time is the meeting?', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', NULL, NULL, 5, '2026-04-12 18:22:44'),
(36, 8, 'matching', 'Match the words with their synonyms.', NULL, NULL, NULL, 1, '2026-04-12 18:22:44'),
(37, 8, 'matching', 'Match the question with the logical response.', NULL, NULL, NULL, 2, '2026-04-12 18:22:44'),
(38, 8, 'matching', 'Match the word with its part of speech.', NULL, NULL, NULL, 3, '2026-04-12 18:22:44'),
(39, 8, 'matching', 'Match to form a correct collocation.', NULL, NULL, NULL, 4, '2026-04-12 18:22:44'),
(40, 8, 'matching', 'Match the IELTS criteria with its meaning.', NULL, NULL, NULL, 5, '2026-04-12 18:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

DROP TABLE IF EXISTS `question_options`;
CREATE TABLE IF NOT EXISTS `question_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung hiển thị (VD: Vế trái nối từ, hoặc câu trả lời A, B, C)',
  `match_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dùng cho dạng Matching: Vế phải nối từ',
  `is_correct` tinyint(1) DEFAULT '0' COMMENT '1 = Đúng, 0 = Sai',
  `order_num` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`id`, `question_id`, `option_text`, `match_text`, `is_correct`, `order_num`) VALUES
(1, 1, 'A. Hesitation', NULL, 0, 1),
(2, 1, 'B. Fluency', NULL, 1, 2),
(3, 1, 'C. Stuttering', NULL, 0, 3),
(4, 1, 'D. Repetition', NULL, 0, 4),
(5, 2, 'A. Trivial', NULL, 0, 1),
(6, 2, 'B. Crucial', NULL, 1, 2),
(7, 2, 'C. Minor', NULL, 0, 3),
(8, 2, 'D. Slight', NULL, 0, 4),
(9, 3, 'A. Under the weather', NULL, 0, 1),
(10, 3, 'B. See eye to eye', NULL, 0, 2),
(11, 3, 'C. Over the moon', NULL, 1, 3),
(12, 3, 'D. Piece of cake', NULL, 0, 4),
(13, 4, 'A. in', NULL, 0, 1),
(14, 4, 'B. on', NULL, 1, 2),
(15, 4, 'C. at', NULL, 0, 3),
(16, 4, 'D. with', NULL, 0, 4),
(17, 5, 'A. am', NULL, 0, 1),
(18, 5, 'B. was', NULL, 0, 2),
(19, 5, 'C. have been', NULL, 1, 3),
(20, 5, 'D. will be', NULL, 0, 4),
(21, 6, 'hang', NULL, 1, 0),
(22, 7, 'for', NULL, 1, 0),
(23, 8, 'linking', NULL, 1, 0),
(47, 21, 'A. Paraphrase the prompt', NULL, 1, 0),
(48, 21, 'B. Give your opinion', NULL, 0, 0),
(49, 21, 'C. Write a conclusion', NULL, 0, 0),
(50, 21, 'D. List all numbers', NULL, 0, 0),
(51, 22, 'A. Decrease', NULL, 0, 0),
(52, 22, 'B. Increase', NULL, 1, 0),
(53, 22, 'C. Plunge', NULL, 0, 0),
(54, 22, 'D. Fluctuate', NULL, 0, 0),
(55, 23, 'A. Remain stable', NULL, 0, 0),
(56, 23, 'B. Vary continuously', NULL, 1, 0),
(57, 23, 'C. Drop sharply', NULL, 0, 0),
(58, 23, 'D. Reach a peak', NULL, 0, 0),
(59, 24, 'A. took', NULL, 0, 0),
(60, 24, 'B. made', NULL, 0, 0),
(61, 24, 'C. reached', NULL, 1, 0),
(62, 24, 'D. did', NULL, 0, 0),
(63, 25, 'A. slight', NULL, 1, 0),
(64, 25, 'B. slightly', NULL, 0, 0),
(65, 25, 'C. slightness', NULL, 0, 0),
(66, 25, 'D. slighted', NULL, 0, 0),
(67, 26, 'that', NULL, 1, 0),
(68, 27, 'for', NULL, 1, 0),
(69, 28, 'from', NULL, 1, 0),
(70, 31, 'A. Playing football', NULL, 0, 1),
(71, 31, 'B. Collecting stamps', NULL, 1, 2),
(72, 31, 'C. Reading books', NULL, 0, 3),
(73, 31, 'D. Travelling', NULL, 0, 4),
(74, 32, 'I love learning new languages', NULL, 1, 0),
(75, 33, 'A. To the library', NULL, 1, 0),
(76, 33, 'B. To the park', NULL, 0, 0),
(77, 33, 'C. To the cinema', NULL, 0, 0),
(78, 33, 'D. To the office', NULL, 0, 0),
(79, 34, 'The weather is very nice today', NULL, 1, 0),
(80, 35, 'A. 8 AM', NULL, 0, 0),
(81, 35, 'B. 9 AM', NULL, 1, 0),
(82, 35, 'C. 10 AM', NULL, 0, 0),
(83, 35, 'D. 11 AM', NULL, 0, 0),
(84, 36, 'Beautiful', 'Stunning', 1, 1),
(85, 36, 'Happy', 'Elated', 1, 2),
(86, 36, 'Fast', 'Rapid', 1, 3),
(87, 37, 'How are you?', 'I am doing great!', 1, 1),
(88, 37, 'What is your name?', 'My name is John.', 1, 2),
(89, 37, 'Where are you from?', 'I am from Vietnam.', 1, 3),
(90, 38, 'Quickly', 'Adverb', 1, 1),
(91, 38, 'Happiness', 'Noun', 1, 2),
(92, 38, 'Beautiful', 'Adjective', 1, 3),
(93, 39, 'Take', 'a photo', 1, 1),
(94, 39, 'Make', 'a mistake', 1, 2),
(95, 39, 'Do', 'homework', 1, 3),
(96, 40, 'Lexical Resource', 'Vocabulary range', 1, 1),
(97, 40, 'Coherence', 'Logical flow', 1, 2),
(98, 40, 'Fluency', 'Speaking speed/smoothness', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('grammar','vocabulary','reading','listening','speaking','writing') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_points` int DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `lesson_id`, `title`, `category`, `total_points`, `description`, `created_at`) VALUES
(1, 61, 'Phần 1: Trắc nghiệm & Điền từ (Speaking Fluency)', 'speaking', 80, 'Kiểm tra từ vựng và ngữ pháp hỗ trợ độ trôi chảy trong Speaking Part 1.', '2026-04-12 17:44:07'),
(2, 61, 'Phần 2: Viết tự luận (Speaking Mở Rộng)', 'speaking', 20, 'Giáo viên sẽ chấm tay phần này.', '2026-04-12 17:44:07'),
(5, 63, 'Phần 1: Trắc nghiệm & Điền từ (Task 1 Vocabulary)', 'writing', 80, NULL, '2026-04-12 17:44:07'),
(6, 63, 'Phần 2: Viết tự luận (Task 1 Overview)', 'writing', 20, NULL, '2026-04-12 17:44:07'),
(7, 62, 'Phần 1: Luyện nghe và Chép chính tả', 'listening', 50, 'Nghe các đoạn hội thoại ngắn và trả lời hoặc chép lại chính xác.', '2026-04-12 18:22:44'),
(8, 62, 'Phần 2: Thử thách nối cặp từ vựng', 'vocabulary', 50, 'Nối từ vựng/câu hỏi ở vế trái với định nghĩa/đáp án phù hợp ở vế phải.', '2026-04-12 18:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_submissions`
--

DROP TABLE IF EXISTS `quiz_submissions`;
CREATE TABLE IF NOT EXISTS `quiz_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'completed',
  `answers_json` longtext COLLATE utf8mb4_unicode_ci,
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_submission` (`student_id`,`quiz_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_quiz` (`quiz_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_submissions`
--

INSERT INTO `quiz_submissions` (`id`, `student_id`, `quiz_id`, `score`, `status`, `answers_json`, `submitted_at`) VALUES
(24, 1, 1, 2.00, 'completed', '{\"1\":1,\"4\":15,\"5\":20,\"6\":[\"hang\"],\"7\":[\"for\"]}', '2026-04-13 01:06:16'),
(31, 1, 2, 0.00, 'pending_grading', '{\"9\":\"\\u00e1da\",\"10\":\"dsds\"}', '2026-04-13 01:11:59'),
(32, 1, 7, 1.00, 'completed', '{\"32\":\"I love learning new languages\"}', '2026-04-13 01:46:29'),
(33, 1, 8, 1.00, 'completed', '{\"36\":{\"84\":\"Stunning\",\"85\":\"Elated\",\"86\":\"Rapid\"}}', '2026-04-13 01:49:25');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `class_detail_id` int DEFAULT NULL,
  `teacher_id` int DEFAULT NULL COMMENT 'Giảng viên dạy thực tế ca này',
  `lesson_id` int DEFAULT NULL,
  `study_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `teaching_type` enum('offline','online') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline' COMMENT 'Hình thức học',
  `room_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('scheduled','completed','canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled' COMMENT 'Trạng thái ca học',
  `attendance_checked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đã điểm danh chưa (0=Chưa, 1=Rồi)',
  `note` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú buổi học',
  PRIMARY KEY (`id`),
  KEY `fk_schedules_class` (`class_id`),
  KEY `fk_schedules_teacher` (`teacher_id`),
  KEY `fk_schedules_class_detail` (`class_detail_id`),
  KEY `fk_schedules_lesson` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=371 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `class_id`, `class_detail_id`, `teacher_id`, `lesson_id`, `study_date`, `start_time`, `end_time`, `teaching_type`, `room_info`, `status`, `attendance_checked`, `note`) VALUES
(331, 2, 4, 5, NULL, '2026-04-04', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(332, 2, 4, NULL, NULL, '2026-04-07', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(333, 2, 4, NULL, NULL, '2026-04-09', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(334, 2, 4, NULL, NULL, '2026-04-11', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(335, 2, 4, 3, NULL, '2026-04-14', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(336, 2, 4, NULL, NULL, '2026-04-16', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(337, 4, 5, 23, NULL, '2026-03-31', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(338, 4, 5, 23, NULL, '2026-04-02', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(339, 4, 5, 23, NULL, '2026-04-04', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(340, 4, 5, 23, NULL, '2026-04-07', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(341, 4, 5, 23, NULL, '2026-04-09', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(342, 4, 5, 23, NULL, '2026-04-11', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(343, 4, 5, 23, NULL, '2026-04-14', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(344, 4, 5, 23, NULL, '2026-04-16', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(345, 4, 5, 23, NULL, '2026-04-18', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(346, 4, 5, 23, NULL, '2026-04-21', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(347, 4, 5, 23, NULL, '2026-04-23', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(348, 7, 11, 3, NULL, '2026-04-06', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(349, 8, 12, 3, NULL, '2026-04-06', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(350, 10, 14, 3, NULL, '2026-04-07', '10:00:00', '11:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(351, 9, 13, 3, NULL, '2026-04-07', '19:45:00', '21:15:00', 'offline', NULL, 'scheduled', 0, NULL),
(352, 7, 11, 3, NULL, '2026-04-08', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(353, 8, 12, 3, NULL, '2026-04-08', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(354, 10, 14, 3, NULL, '2026-04-09', '10:00:00', '11:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(355, 9, 13, 3, NULL, '2026-04-09', '19:45:00', '21:15:00', 'offline', NULL, 'scheduled', 0, NULL),
(356, 7, 11, 3, NULL, '2026-04-10', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(357, 8, 12, 3, NULL, '2026-04-10', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(358, 11, 15, 3, NULL, '2026-04-11', '14:00:00', '16:00:00', 'offline', NULL, 'scheduled', 0, NULL),
(359, 9, 13, 3, NULL, '2026-04-11', '19:45:00', '21:15:00', 'offline', NULL, 'scheduled', 0, NULL),
(360, 11, 15, 3, NULL, '2026-04-12', '14:00:00', '16:00:00', 'offline', NULL, 'scheduled', 0, NULL),
(361, 7, 11, 3, NULL, '2026-04-13', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(362, 8, 12, 3, NULL, '2026-04-13', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(363, 10, 14, 3, NULL, '2026-04-14', '10:00:00', '11:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(364, 9, 13, 3, NULL, '2026-04-14', '19:45:00', '21:15:00', 'offline', NULL, 'scheduled', 0, NULL),
(365, 7, 11, 3, NULL, '2026-04-15', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(366, 8, 12, 3, NULL, '2026-04-15', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(367, 10, 14, 3, NULL, '2026-04-16', '10:00:00', '11:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(368, 9, 13, 3, NULL, '2026-04-16', '19:45:00', '21:15:00', 'offline', NULL, 'scheduled', 0, NULL),
(369, 7, 11, 3, NULL, '2026-04-17', '08:00:00', '09:30:00', 'offline', NULL, 'scheduled', 0, NULL),
(370, 8, 12, 3, NULL, '2026-04-17', '18:00:00', '19:30:00', 'offline', NULL, 'scheduled', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

DROP TABLE IF EXISTS `student_answers`;
CREATE TABLE IF NOT EXISTS `student_answers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `selected_option_id` int DEFAULT NULL COMMENT 'Nếu học sinh làm bài trắc nghiệm',
  `answer_text` text COLLATE utf8mb4_unicode_ci COMMENT 'Nếu học sinh gõ chữ (Writing / Fill in the blank)',
  `is_correct` tinyint(1) DEFAULT '0',
  `points_earned` decimal(5,2) DEFAULT '0.00',
  `teacher_feedback` json DEFAULT NULL COMMENT 'Lưu định dạng JSON để bóc tách lỗi Grammar, Vocab hiển thị bôi màu',
  `graded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`),
  KEY `selected_option_id` (`selected_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_attempts`
--

DROP TABLE IF EXISTS `student_attempts`;
CREATE TABLE IF NOT EXISTS `student_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `student_id` int NOT NULL,
  `attempt_number` int DEFAULT '1' COMMENT 'Lần làm thứ mấy',
  `total_score` decimal(5,2) DEFAULT '0.00',
  `status` enum('in_progress','completed','graded') COLLATE utf8mb4_unicode_ci DEFAULT 'in_progress',
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_vocabulary_progress`
--

DROP TABLE IF EXISTS `student_vocabulary_progress`;
CREATE TABLE IF NOT EXISTS `student_vocabulary_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `vocab_id` int NOT NULL,
  `status` enum('learning','reviewing','mastered') COLLATE utf8mb4_unicode_ci DEFAULT 'learning',
  `correct_count` int DEFAULT '0',
  `incorrect_count` int DEFAULT '0',
  `last_reviewed_at` timestamp NULL DEFAULT NULL,
  `next_review_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_vocab` (`student_id`,`vocab_id`),
  KEY `vocab_id` (`vocab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `submission_content` text COLLATE utf8mb4_unicode_ci,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_assignment` (`student_id`,`assignment_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `class_id` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('student','instructor','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `status` enum('active','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `status`, `created_at`) VALUES
(1, 'Hoàng Nhật Trường', 'zayluon@gmail.com', '$2y$12$wjwQEwGMt3mgSqBOA/30TuYCFxZYou/pwmJESUqD.gCCMFuzu.ywG', '0364132169', 'student', 'active', '2026-03-19 07:25:48'),
(2, 'Admin', 'admin@gmail.com', '$2y$12$Ui88Q4q/TIF36OhZ8MnDn.1l6HQoTcQzAsftYDjnr7JMCBG3nuDnO', NULL, 'admin', 'active', '2026-03-19 08:57:52'),
(3, 'Teacher', 'teacher@gmail.com', '$2y$12$1dqlsZa3ZQv8C5ZDIf4xzuiscBPIzB245la3bcCxzvGL5CwxWF9vK', NULL, 'instructor', 'active', '2026-03-19 09:04:21'),
(4, 'Cá', 'a@gmail.com', '$2y$12$FmPtsSDGR7rIZN4VA2hgAe5xepf5TAW6GhSUAQTM5KZ5OKFIhlNQG', NULL, 'student', 'active', '2026-03-20 00:04:36'),
(5, 'GV John Smith', 'gv.john@gmail.com', '123456', '0911222555', 'instructor', 'active', '2026-03-27 20:16:24'),
(6, 'GV Lan Hương', 'gv.lanhuong@gmail.com', '123456', '0911222666', 'instructor', 'active', '2026-03-27 20:16:24'),
(7, 'Học viên 01', 'hv1@gmail.com', '123456', '0988111001', 'student', 'active', '2026-03-27 20:16:24'),
(8, 'Học viên 02', 'hv2@gmail.com', '123456', '0988111002', 'student', 'active', '2026-03-27 20:16:24'),
(9, 'Học viên 03', 'hv3@gmail.com', '123456', '0988111003', 'student', 'active', '2026-03-27 20:16:24'),
(10, 'Học viên 04', 'hv4@gmail.com', '123456', '0988111004', 'student', 'active', '2026-03-27 20:16:24'),
(11, 'Học viên 05', 'hv5@gmail.com', '123456', '0988111005', 'student', 'active', '2026-03-27 20:16:24'),
(12, 'Học viên 06', 'hv6@gmail.com', '123456', '0988111006', 'student', 'active', '2026-03-27 20:16:24'),
(13, 'Học viên 07', 'hv7@gmail.com', '123456', '0988111007', 'student', 'active', '2026-03-27 20:16:24'),
(14, 'Học viên 08', 'hv8@gmail.com', '123456', '0988111008', 'student', 'active', '2026-03-27 20:16:24'),
(15, 'Học viên 09', 'hv9@gmail.com', '123456', '0988111009', 'student', 'active', '2026-03-27 20:16:24'),
(16, 'Học viên 10', 'hv10@gmail.com', '123456', '0988111010', 'student', 'active', '2026-03-27 20:16:24'),
(17, 'Học viên 11', 'hv11@gmail.com', '123456', '0988111011', 'student', 'active', '2026-03-27 20:16:24'),
(18, 'Học viên 12', 'hv12@gmail.com', '123456', '0988111012', 'student', 'active', '2026-03-27 20:16:24'),
(19, 'Học viên 13', 'hv13@gmail.com', '123456', '0988111013', 'student', 'active', '2026-03-27 20:16:24'),
(20, 'Học viên 14', 'hv14@gmail.com', '123456', '0988111014', 'student', 'active', '2026-03-27 20:16:24'),
(22, 'GV Mai Phương', 'gv.maiphuong@gmail.com', '123456', '0911222333', 'instructor', 'active', '2026-03-27 20:16:24'),
(23, 'GV Tuấn Anh', 'gv.tuananh@gmail.com', '123456', '0911222444', 'instructor', 'active', '2026-03-27 20:16:24'),
(45, 'Test User', 'test@gmail.com', '$2y$12$QLPoRQ3BevTMQe2o5FUxk.lJGMYEb6q30i.WPsrhq/09bQ83v7H4y', NULL, 'student', 'active', '2026-04-07 21:07:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

DROP TABLE IF EXISTS `user_progress`;
CREATE TABLE IF NOT EXISTS `user_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `is_flashcard_completed` tinyint(1) DEFAULT '0',
  `quiz_score` decimal(5,2) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vocabularies`
--

DROP TABLE IF EXISTS `vocabularies`;
CREATE TABLE IF NOT EXISTS `vocabularies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_id` int NOT NULL,
  `word` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `part_of_speech` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meaning` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pronunciation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `example_sentence` text COLLATE utf8mb4_unicode_ci,
  `audio_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_contents`
--

DROP TABLE IF EXISTS `website_contents`;
CREATE TABLE IF NOT EXISTS `website_contents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_type` enum('text','image','html') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `content_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `website_contents`
--

INSERT INTO `website_contents` (`id`, `section_key`, `content_type`, `content_value`, `updated_at`) VALUES
(2, 'home_banner_image_url', 'image', 'https://img.freepik.com/premium-psd/study-social-media-instagram-post-template-design_1287219-540.jpg?w=900', '2026-03-28 19:43:55'),
(3, 'home_intro_image_url', 'image', 'https://img.freepik.com/premium-vector/english-learning-institute-social-media-post-design-template-any-educational-institute_610715-99.jpg', '2026-03-28 20:02:39'),
(4, 'about_story_image_url', 'image', 'https://simpleenglish.com.vn/wp-content/uploads/2025/02/SIMPLE_BANNER-WEB-1423x620_1902_2-1-scaled.jpg', '2026-03-28 20:30:16');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignments_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_classes_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `class_details`
--
ALTER TABLE `class_details`
  ADD CONSTRAINT `class_details_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`path_id`) REFERENCES `learning_paths` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrollments_class_detail` FOREIGN KEY (`class_detail_id`) REFERENCES `class_details` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `makeup_registrations`
--
ALTER TABLE `makeup_registrations`
  ADD CONSTRAINT `fk_makeup_leave_request` FOREIGN KEY (`leave_request_id`) REFERENCES `leave_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_makeup_schedule` FOREIGN KEY (`target_schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_makeup_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `fk_materials_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedules_class_detail` FOREIGN KEY (`class_detail_id`) REFERENCES `class_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedules_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_schedules_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `student_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_3` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_attempts`
--
ALTER TABLE `student_attempts`
  ADD CONSTRAINT `student_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_vocabulary_progress`
--
ALTER TABLE `student_vocabulary_progress`
  ADD CONSTRAINT `student_vocabulary_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_vocabulary_progress_ibfk_2` FOREIGN KEY (`vocab_id`) REFERENCES `vocabularies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vocabularies`
--
ALTER TABLE `vocabularies`
  ADD CONSTRAINT `vocabularies_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
