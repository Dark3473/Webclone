-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 22, 2025 lúc 01:07 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `edubase`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `description`, `teacher_id`, `date`) VALUES
(1, 'CSS Fundamentals', 'Learn how to style websites using CSS', NULL, '2025-04-11'),
(2, 'HTML Essentials', 'Leran how to make a website', NULL, '2025-04-19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_management`
--

CREATE TABLE `course_management` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `Trạng thái học` enum('active','completed','dropped') NOT NULL,
  `certificate_issued` tinyint(1) NOT NULL DEFAULT 0,
  `certificate_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `course_management`
--

INSERT INTO `course_management` (`id`, `user_id`, `course_id`, `enrollment_date`, `Trạng thái học`, `certificate_issued`, `certificate_date`) VALUES
(1, 1, 1, '2025-04-17', 'active', 0, NULL),
(2, 2, 1, '2025-04-18', 'active', 0, NULL),
(3, 2, 2, '2025-04-19', 'active', 0, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `các quyền`
--

CREATE TABLE `các quyền` (
  `quyen_id` int(11) NOT NULL,
  `quyền tạo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cho phep tao',
  `quyền sửa` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cho phep sua',
  `quyền cập nhật` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cho phep cap nhat',
  `quyền xóa` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cho phep xoa',
  `quyền học` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cho phep tiep can khoa hoc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `câu hỏi khóa học`
--

CREATE TABLE `câu hỏi khóa học` (
  `Cau hoi khoa hoc_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `answer_A` text NOT NULL,
  `answer_B` text NOT NULL,
  `answer_C` text NOT NULL,
  `answer_D` text NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `câu hỏi module`
--

CREATE TABLE `câu hỏi module` (
  `Cau hoi_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `answer_A` text NOT NULL,
  `answer_B` text NOT NULL,
  `answer_C` text NOT NULL,
  `answer_D` text NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `final_test`
--

CREATE TABLE `final_test` (
  `user_id` int(11) NOT NULL,
  `score` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lesson_management`
--

CREATE TABLE `lesson_management` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `trạng thái học` enum('chưa bắt đầu','đang học','đã hoàn thành') NOT NULL,
  `last_accessed` date NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lesson_management`
--

INSERT INTO `lesson_management` (`id`, `page_id`, `trạng thái học`, `last_accessed`, `user_id`) VALUES
(1, 1, 'đã hoàn thành', '2025-04-22', 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `modules`
--

CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `module_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `modules`
--

INSERT INTO `modules` (`module_id`, `course_id`, `module_name`, `date`, `module_order`) VALUES
(0, 1, 'Intro to the CSS Essentials Course', '2025-01-02', 1),
(1, 1, 'CSS Basics', '2025-01-03', 2),
(2, 1, 'Properties and Values', '2025-01-04', 3),
(3, 1, 'The Box Model', '2025-01-05', 4),
(4, 1, 'Floats and Media Queries', '2025-01-06', 5),
(5, 1, 'Flexbox and CSS Grid', '2025-01-07', 6),
(6, 2, 'Getting Started with HTML', '2025-04-19', 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `pages`
--

CREATE TABLE `pages` (
  `page_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `date` date NOT NULL,
  `page_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `pages`
--

INSERT INTO `pages` (`page_id`, `topic_id`, `content`, `date`, `page_order`) VALUES
(1, 1, 'Welcome to Module 1 of our CSS Essentials course!\n\nIn this module, we\'ll introduce you to CSS and explain its basic syntax. By the end of this module, you\'ll have a solid grasp of how CSS can enhance web pages, making them visually appealing. It\'s an important skill for web designers.\n\nIn this first section, we\'ll answer what CSS is, explore why learning CSS is valuable, and provide a brief overview of its history.', '2025-04-17', 0),
(2, 2, 'đây là nội dung của topic 2 ', '2025-04-19', 1),
(3, 3, 'nội dung topic 3', '2025-04-18', 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phan quyen`
--

CREATE TABLE `phan quyen` (
  `phan quyen_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quyen_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `test_module`
--

CREATE TABLE `test_module` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `score` float NOT NULL,
  `attempt_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `thongbao_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao_khoa_hoc`
--

CREATE TABLE `thong_bao_khoa_hoc` (
  `thongbao KH_id` int(11) NOT NULL,
  `	course_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao_user`
--

CREATE TABLE `thong_bao_user` (
  `thongbao_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `topics`
--

CREATE TABLE `topics` (
  `topic_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `topic_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `topics`
--

INSERT INTO `topics` (`topic_id`, `module_id`, `topic_name`, `date`, `topic_order`) VALUES
(1, 1, 'Introduction to CSS', '2025-01-02', 0),
(2, 1, 'Including CSS in a Web Page', '2025-01-02', 0),
(3, 1, 'Creating and Organizing External Stylesheets', '2025-01-02', 0),
(4, 1, 'CSS Files and Directories', '2025-01-02', 0),
(5, 1, 'CSS Syntax', '2025-01-02', 0),
(6, 1, 'CSS Comments', '2025-01-02', 0),
(7, 1, 'Moduls Test', '2025-04-18', 0),
(8, 6, 'Introduction to HTML', '2025-04-19', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `date`) VALUES
(1, 'lmao', 'nguoikila2003@gmail.com', '$2y$10$OM/MCsscbx5B.TS5102JLuGB87YUEjLvxqCLV3Ygimf09/fKpYkZK', '2025-04-14'),
(2, 'lmao3', 'abc@gmail.com', '123', '0000-00-00');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Chỉ mục cho bảng `course_management`
--
ALTER TABLE `course_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Chỉ mục cho bảng `các quyền`
--
ALTER TABLE `các quyền`
  ADD PRIMARY KEY (`quyen_id`);

--
-- Chỉ mục cho bảng `câu hỏi khóa học`
--
ALTER TABLE `câu hỏi khóa học`
  ADD PRIMARY KEY (`Cau hoi khoa hoc_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Chỉ mục cho bảng `câu hỏi module`
--
ALTER TABLE `câu hỏi module`
  ADD PRIMARY KEY (`Cau hoi_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Chỉ mục cho bảng `final_test`
--
ALTER TABLE `final_test`
  ADD PRIMARY KEY (`user_id`);

--
-- Chỉ mục cho bảng `lesson_management`
--
ALTER TABLE `lesson_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `fk_user_lesson` (`user_id`);

--
-- Chỉ mục cho bảng `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`module_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Chỉ mục cho bảng `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`),
  ADD KEY `module_id` (`topic_id`);

--
-- Chỉ mục cho bảng `phan quyen`
--
ALTER TABLE `phan quyen`
  ADD PRIMARY KEY (`phan quyen_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `phanquyen_id` (`quyen_id`);

--
-- Chỉ mục cho bảng `test_module`
--
ALTER TABLE `test_module`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tm_user` (`user_id`),
  ADD KEY `fk_test_module_module` (`module_id`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`thongbao_id`);

--
-- Chỉ mục cho bảng `thong_bao_khoa_hoc`
--
ALTER TABLE `thong_bao_khoa_hoc`
  ADD PRIMARY KEY (`thongbao KH_id`,`	course_id`),
  ADD KEY `fk_thong_bao_kh_khoahoc` (`	course_id`);

--
-- Chỉ mục cho bảng `thong_bao_user`
--
ALTER TABLE `thong_bao_user`
  ADD PRIMARY KEY (`thongbao_id`,`user_id`),
  ADD KEY `fk_thong_bao_user_user` (`user_id`);

--
-- Chỉ mục cho bảng `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `course_management`
--
ALTER TABLE `course_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `các quyền`
--
ALTER TABLE `các quyền`
  MODIFY `quyen_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `câu hỏi khóa học`
--
ALTER TABLE `câu hỏi khóa học`
  MODIFY `Cau hoi khoa hoc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `câu hỏi module`
--
ALTER TABLE `câu hỏi module`
  MODIFY `Cau hoi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lesson_management`
--
ALTER TABLE `lesson_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `modules`
--
ALTER TABLE `modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `pages`
--
ALTER TABLE `pages`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `phan quyen`
--
ALTER TABLE `phan quyen`
  MODIFY `phan quyen_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `test_module`
--
ALTER TABLE `test_module`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `thongbao_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `topics`
--
ALTER TABLE `topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `course_management`
--
ALTER TABLE `course_management`
  ADD CONSTRAINT `fk_cm_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `câu hỏi khóa học`
--
ALTER TABLE `câu hỏi khóa học`
  ADD CONSTRAINT `câu hỏi khóa học_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `câu hỏi module`
--
ALTER TABLE `câu hỏi module`
  ADD CONSTRAINT `câu hỏi module_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `final_test`
--
ALTER TABLE `final_test`
  ADD CONSTRAINT `fk_final_test_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lesson_management`
--
ALTER TABLE `lesson_management`
  ADD CONSTRAINT `fk_lm_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_lesson` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`topic_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `phan quyen`
--
ALTER TABLE `phan quyen`
  ADD CONSTRAINT `phan quyen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `phan quyen_ibfk_2` FOREIGN KEY (`quyen_id`) REFERENCES `các quyền` (`quyen_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `test_module`
--
ALTER TABLE `test_module`
  ADD CONSTRAINT `fk_test_module_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`),
  ADD CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thong_bao_khoa_hoc`
--
ALTER TABLE `thong_bao_khoa_hoc`
  ADD CONSTRAINT `fk_thong_bao_kh_khoahoc` FOREIGN KEY (`	course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thong_bao_kh_thongbao` FOREIGN KEY (`thongbao KH_id`) REFERENCES `thong_bao` (`thongbao_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thong_bao_user`
--
ALTER TABLE `thong_bao_user`
  ADD CONSTRAINT `fk_thong_bao_user_thongbao` FOREIGN KEY (`thongbao_id`) REFERENCES `thong_bao` (`thongbao_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thong_bao_user_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
