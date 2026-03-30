-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 29, 2026 at 11:56 AM
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
  `lesson_id` int DEFAULT NULL,
  `course_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `fk_classes_instructor` (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `course_id`, `instructor_id`, `class_name`, `start_date`, `end_date`, `created_at`) VALUES
(1, 4, 5, 'IELTS 01', '2026-03-29', '2026-06-23', '2026-03-29 11:49:47');

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
  `status` enum('active','completed','dropped') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `enrollment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
CREATE TABLE IF NOT EXISTS `materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `question_type` enum('multiple_choice','fill_blank','listening') COLLATE utf8mb4_unicode_ci DEFAULT 'multiple_choice',
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `audio_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_a` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_b` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_c` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_d` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correct_option` enum('A','B','C','D') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `teacher_id` int DEFAULT NULL COMMENT 'Giảng viên dạy thực tế ca này',
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
  KEY `fk_schedules_teacher` (`teacher_id`)
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
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(23, 'GV Tuấn Anh', 'gv.tuananh@gmail.com', '123456', '0911222444', 'instructor', 'active', '2026-03-27 20:16:24');

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
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_classes_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_schedules_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
