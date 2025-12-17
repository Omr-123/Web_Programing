-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 17, 2025 at 08:04 PM
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
-- Database: `lerno`
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `instructor_id` int UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `level` enum('beginner','intermediate','advanced') COLLATE utf8mb4_unicode_ci DEFAULT 'beginner',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `instructor_id` (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `instructor_id`, `title`, `description`, `thumbnail_url`, `language`, `level`, `price`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Mastering JavaScript', 'An in-depth course on modern JavaScript development.', 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d', 'en', 'intermediate', 49.99, 1, '2025-12-10 08:00:00', NULL),
(2, 2, 'HTML & CSS for Beginners', 'Learn the basics of web design with HTML and CSS.', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c', 'en', 'beginner', 29.99, 1, '2025-12-11 09:30:00', NULL),
(3, 2, 'Full-Stack Web Development', 'Become a full-stack web developer with this comprehensive course.', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085', 'en', 'advanced', 99.99, 1, '2025-12-12 12:15:00', NULL),
(4, 2, 'React.js Essentials', 'Get started with React.js and build dynamic web applications.', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c', 'en', 'intermediate', 59.99, 1, '2025-12-13 07:45:00', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`) VALUES
(10, 5, 4, '2025-12-17 22:03:08');

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
  `order` int UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `video_url`, `content`, `duration_seconds`, `order`, `created_at`) VALUES
(1, 4, 'React js Essentials', 'https://www.youtube.com/playlist?list=PL0Zuz27SZ-6PrE9srvEn8nbhOOyxnWXfp', 'Using React js to Be Advanced.', 900, 1, '2025-12-14 17:05:00'),
(2, 4, 'Components and Props', 'https://www.youtube.com/playlist?list=PLZPZq0r_RZOMQArzyI32mVndGBZ3D99XQ', 'Understanding Components and Props in React js.', 1200, 2, '2025-12-14 17:10:00'),
(3, 4, 'State and Lifecycle', 'https://www.youtube.com/playlist?list=PLC3y8-rFHvwgg3vaYJgHGnModB54rxOk3', 'Managing State and Lifecycle Methods in React js.', 1500, 3, '2025-12-14 17:15:00'),
(4, 4, 'Handling Events', 'https://www.youtube.com/playlist?list=PLEiEAq2VkUUKMie-cEUnkHRxiFKDZJGqO', 'Event Handling in React js Applications.', 1100, 4, '2025-12-14 17:20:00'),
(5, 4, 'React Router Basics', 'https://www.youtube.com/playlist?list=PL4cUxeGkcC9gZD-Tvwfod2gaISzfRiP9d', 'Implementing Routing with React Router.', 1300, 5, '2025-12-14 17:25:00'),
(6, 3, 'Full-Stack Project Setup', 'https://www.youtube.com/playlist?list=PLEiEAq2VkUULCC3eEATL4zzuapTjmo1Z_', 'Setting up a full-stack web development project.', 1800, 1, '2025-12-14 18:00:00'),
(7, 3, 'Backend Development with Node.js', 'https://www.youtube.com/watch?v=f2EqECiTBL8', 'Building the backend using Node.js and Express.', 2400, 2, '2025-12-14 18:30:00'),
(8, 3, 'Frontend Development with React', 'https://www.youtube.com/watch?v=RVFAyFWO4go', 'Creating the frontend with React.js.', 2100, 3, '2025-12-14 19:00:00'),
(9, 3, 'Database Integration', 'https://www.youtube.com/watch?v=fPuLnzSjPLE', 'Integrating a database into the full-stack application.', 1500, 4, '2025-12-14 19:30:00'),
(10, 3, 'Deployment and Hosting', 'https://www.youtube.com/playlist?list=PL0Zuz27SZ-6PrE9srvEn8nbhOOyxnWXfp', 'Deploying and hosting the full-stack application.', 1200, 5, '2025-12-14 20:00:00'),
(11, 2, 'HTML Basics', 'https://www.youtube.com/watch?v=zJSY8tbf_ys', 'Introduction to HTML and its basic structure.', 800, 1, '2025-12-14 16:00:00'),
(12, 2, 'CSS Fundamentals', 'https://www.youtube.com/watch?v=zJSY8tbf_ys', 'Learning the fundamentals of CSS for styling web pages.', 1000, 2, '2025-12-14 16:20:00'),
(13, 2, 'Responsive Design', 'https://www.youtube.com/watch?v=zJSY8tbf_ys', 'Creating responsive web designs using CSS media queries.', 1100, 3, '2025-12-14 16:40:00'),
(14, 2, 'Flexbox and Grid', 'https://www.youtube.com/watch?v=zJSY8tbf_ys', 'Using Flexbox and CSS Grid for layout design.', 1200, 4, '2025-12-14 17:00:00'),
(15, 1, 'JavaScript Fundamentals', 'https://www.youtube.com/watch?v=zJSY8tbf_ys', 'Understanding the fundamentals of JavaScript programming.', 1300, 1, '2025-12-14 15:00:00'),
(16, 1, 'DOM Manipulation', 'https://www.youtube.com/watch?v=zJSY8tbf_ys', 'Manipulating the Document Object Model (DOM) with JavaScript.', 1400, 2, '2025-12-14 15:25:00'),
(17, 1, 'ES6 Features', 'https://www.youtube.com/watch?v=RVFAyFWO4go', 'Exploring new features introduced in ECMAScript 6 (ES6).', 1500, 3, '2025-12-14 15:50:00'),
(18, 1, 'Asynchronous JavaScript', 'https://www.youtube.com/watch?v=RVFAyFWO4go', 'Working with asynchronous programming in JavaScript using callbacks, promises, and async/await.', 1600, 4, '2025-12-14 16:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `lessons_progress`
--

DROP TABLE IF EXISTS `lessons_progress`;
CREATE TABLE IF NOT EXISTS `lessons_progress` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `lesson_id` int UNSIGNED NOT NULL,
  `completed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_lesson` (`user_id`,`lesson_id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons_progress`
--

INSERT INTO `lessons_progress` (`id`, `user_id`, `lesson_id`, `completed_at`) VALUES
(8, 5, 7, '2025-12-17 21:07:33'),
(9, 6, 7, '2025-12-17 21:21:53');

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
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` tinyint UNSIGNED NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `email`, `password`, `avatar`, `role_id`, `joined_at`) VALUES
(2, 'Ali', 'Nasser', 'ali@gmail.com', '$2y$10$6dNwDTGF.PtLBlAB0B3au.9/5naT5QGduVL4F20HF4dC.H6b0UjtW', 'https://plus.unsplash.com/premium_photo-1689568126014-06fea9d5d341?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8cHJvZmlsZXxlbnwwfHwwfHx8MA%3D%3D', 2, '2025-12-14 19:02:41'),
(4, 'seif', 'Emad', 'Seif@gamil.com', '$2y$10$6dNwDTGF.PtLBlAB0B3au.9/5naT5QGduVL4F20HF4dC.H6b0UjtW', '', 1, '2025-12-17 20:28:31'),
(5, 'Omar', 'Emad', 'Omar@gmail.com', '$2y$10$jTDhObCXuWuvKGAKyCjk5O.ft7fAaadHeWTM0ELvh3LRWfrQUFinG', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRyTTDr-b3Q1sQsRRcmzjq0PHdImZfTpyq5KBTB1YuUAhirr1OpeFVhd_Ll2nw7qzI2iSM-0Pwu6YxDNINKTpWGPYJr1tIBkEkKUIjXhLXDoQ&s=10', 3, '2025-12-17 21:01:51'),
(6, 'Omar', 'Emad', 'Omar1@gmail.com', '$2y$10$YuQCkiWsSZR2GZb5O.nPn.rJ7JKwT61ftWwJQ5dZ/oNWcpcgmhoeW', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcROHxYft1f_Ln_y_scKnh8-g5rLMmce7JKyPQ&s', 1, '2025-12-17 21:20:18');

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
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- Dump completed on 2025-12-17 20:04:21