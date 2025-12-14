-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 14, 2025 at 08:48 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lerno2`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL,
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_course` (`user_id`,`course_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `instructor_id` int UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `level` enum('beginner','intermediate','advanced') COLLATE utf8mb4_unicode_ci DEFAULT 'beginner',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_published` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `instructor_id` (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `instructor_id`, `title`, `slug`, `description`, `thumbnail_url`, `language`, `level`, `price`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 1, 'Full-Stack Web Development', 'full-stack-web-dev', 'Learn HTML, CSS, JS, PHP, and MySQL by building projects.', 'https://images.unsplash.com/photo-1517433456452-f9633a875f6f', 'en', 'beginner', 0.00, 1, '2025-12-14 17:58:41', NULL),
(2, 1, 'Advanced PHP Patterns', 'advanced-php-patterns', 'Master advanced PHP design patterns and best practices.', 'https://images.unsplash.com/photo-1518779578993-ec3579fee39f', 'en', 'advanced', 0.00, 1, '2025-12-14 17:58:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_assets`
--

DROP TABLE IF EXISTS `course_assets`;
CREATE TABLE IF NOT EXISTS `course_assets` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` int UNSIGNED NOT NULL,
  `kind` enum('image','resource') COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int UNSIGNED DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_assets`
--

INSERT INTO `course_assets` (`id`, `course_id`, `kind`, `url`, `title`, `position`, `created_at`) VALUES
(1, 1, 'image', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c', 'Coding Setup', 1, '2025-12-14 17:58:41'),
(2, 1, 'image', 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4', 'HTML & CSS', 2, '2025-12-14 17:58:41'),
(3, 2, 'image', 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d', 'PHP Patterns', 1, '2025-12-14 17:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL,
  `enrolled_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_course` (`user_id`,`course_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`) VALUES
(1, 2, 1, '2025-12-14 17:58:41'),
(2, 2, 2, '2025-12-14 17:58:41'),
(3, 4, 1, '2025-12-14 18:13:51'),
(4, 4, 2, '2025-12-14 18:24:11'),
(5, 5, 1, '2025-12-14 18:28:15'),
(6, 6, 1, '2025-12-14 18:35:08'),
(7, 6, 2, '2025-12-14 18:36:20'),
(9, 7, 1, '2025-12-14 20:04:11'),
(10, 7, 2, '2025-12-14 20:04:11'),
(12, 11, 1, '2025-12-14 21:53:50'),
(13, 8, 1, '2025-12-14 22:31:26'),
(14, 10, 1, '2025-12-14 22:40:39');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` int UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `duration_seconds` int UNSIGNED DEFAULT '0',
  `position` int UNSIGNED NOT NULL,
  `is_preview` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `video_url`, `content`, `duration_seconds`, `position`, `is_preview`, `created_at`) VALUES
(1, 1, 'Intro to Web Dev', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Overview and setup', 600, 1, 1, '2025-12-14 17:58:41'),
(2, 1, 'HTML Basics', 'https://www.youtube.com/embed/pQN-pnXPaVg', 'Learn HTML structure', 900, 2, 0, '2025-12-14 17:58:41'),
(3, 1, 'CSS Fundamentals', 'https://www.youtube.com/embed/yfoY53QXEnI', 'Styling web pages', 1200, 3, 0, '2025-12-14 17:58:41'),
(4, 2, 'SOLID in PHP', 'https://www.youtube.com/embed/TMuno5RZNeE', 'Applying SOLID principles', 1100, 1, 1, '2025-12-14 17:58:41'),
(5, 2, 'Dependency Injection', 'https://www.youtube.com/embed/2lZ-LwB1nBM', 'DI containers and patterns', 1000, 2, 0, '2025-12-14 17:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

DROP TABLE IF EXISTS `lesson_progress`;
CREATE TABLE IF NOT EXISTS `lesson_progress` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `lesson_id` int UNSIGNED NOT NULL,
  `status` enum('not_started','in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'not_started',
  `last_watched_second` int UNSIGNED DEFAULT '0',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_lesson` (`user_id`,`lesson_id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `user_id`, `lesson_id`, `status`, `last_watched_second`, `updated_at`) VALUES
(1, 4, 1, 'completed', 0, '2025-12-14 18:19:25'),
(2, 4, 2, 'completed', 0, '2025-12-14 18:20:03'),
(3, 4, 3, 'completed', 0, '2025-12-14 18:20:41'),
(4, 5, 1, 'completed', 0, '2025-12-14 18:28:22'),
(5, 5, 2, 'completed', 0, '2025-12-14 18:28:26'),
(6, 6, 1, 'completed', 0, '2025-12-14 18:35:31'),
(7, 11, 1, 'completed', 0, '2025-12-14 21:53:55');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` tinyint UNSIGNED NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(3, 'admin'),
(2, 'instructor'),
(1, 'student');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `fname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` tinyint UNSIGNED NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `email`, `password`, `role_id`, `joined_at`, `profile_image_url`, `bio`) VALUES
(1, 'Seif', 'Emad', 'seifemad@hotmail.com', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJK', 2, '2025-12-14 17:58:41', NULL, NULL),
(2, 'John', 'Doe', 'john@example.com', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJK', 1, '2025-12-14 17:58:41', NULL, NULL),
(3, 'Admin', 'User', 'admin@lerno.com', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJK', 3, '2025-12-14 17:58:41', NULL, NULL),
(4, 'ss', 'mm', 'seif@hotmail.com', '$2y$10$LflURi7Cx4XCIMZk0tUIBe/FMrITgHIS2FInxRtlm9or2J3.nWDrC', 1, '2025-12-14 18:10:24', NULL, NULL),
(5, 'mm', 'll', 'lany@hotmail.com', '$2y$10$1ZcY5BBjZh13bK3kfWzeQ.wTpZPwaGInj27AEMY0dVwBt0rPNT38G', 2, '2025-12-14 18:14:59', NULL, NULL),
(6, 'eman', 'hassan', 'eman@hotmail.com', '$2y$10$SL298xmmn8Lmp6l9jXvtKO/sus8CpBJv5gm/fpINIqlJA3TRDl1Um', 1, '2025-12-14 18:34:21', NULL, NULL),
(7, 'ss', 'mm', 'mm@hotmail.com', '$2y$10$BL1siL67Bt5O8AkQltbfuOR0SgbE5vdT85F3fxhvIcv/uFxWs5Hse', 3, '2025-12-14 18:36:58', NULL, NULL),
(8, 'mhm', 'asa', 'mhm@hotmail.com', '$2y$10$eRj2COwXpAKuOKm2o3toDuwZbWrRTBqk0iVvBtxyWc0aTJev7uvci', 2, '2025-12-14 21:10:13', NULL, NULL),
(9, 'ss', ';;', 'kmk@hotmail.com', '$2y$10$ss/5OVdLHJYZ/VxQwrKGY.s2mhgAneiy78B/69gNKNDJFTkcY6bQm', 2, '2025-12-14 21:30:56', NULL, NULL),
(10, 'gg', 'hh', 'hh@hotmail.com', '$2y$10$tzUxp7SVJKSIYI8L.7XUz.KTrbiTFnlDCujW/P.sz6bFuKyYxY.Ma', 2, '2025-12-14 21:52:32', 'https://img.freepik.com/free-photo/portrait-handsome-smiling-stylish-hipster-lambersexual-model-sexy-man-dressed-tshirt-jeans-fashion-male-isolated-blue-wall-studio_158538-26731.jpg?semt=ais_hybrid&w=740&q=80', 'hi'),
(11, 'uu', 'kk', 'kk@hotmail.com', '$2y$10$kCdBtM62OzPd.h6ZZ3weU.kGoCjXKNyLUALpFk7FWUawr77JAvaJ2', 2, '2025-12-14 21:53:20', 'https://img.freepik.com/free-photo/portrait-handsome-smiling-stylish-hipster-lambersexual-model-sexy-man-dressed-tshirt-jeans-fashion-male-isolated-blue-wall-studio_158538-26731.jpg?semt=ais_hybrid&w=740&q=80', NULL),
(12, 'uu', 'ud', 'uu@hotmail.com', '$2y$10$J5nYj9MsWOH9LG6cQBTho.zA9/WNEu2NoaZsuYE5VCW8OznIUPPru', 2, '2025-12-14 22:26:48', 'https://img.freepik.com/free-photo/portrait-handsome-smiling-stylish-hipster-lambersexual-model-sexy-man-dressed-tshirt-jeans-fashion-male-isolated-blue-wall-studio_158538-26731.jpg?semt=ais_hybrid&w=740&q=80', 'web developer');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_assets`
--
ALTER TABLE `course_assets`
  ADD CONSTRAINT `fk_assets_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enroll_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
