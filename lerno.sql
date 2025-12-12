-- phpMyAdmin SQL Dump
-- Database: `lerno`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE users (
  userId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  fname VARCHAR(50) NOT NULL,
  lname VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role INT NOT NULL DEFAULT 0,
  joinedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Table structure for table `courses`
--
CREATE TABLE courses (
  courseId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255),
  price FLOAT NOT NULL,
  level VARCHAR(50) NOT NULL,
  lang VARCHAR(50) NOT NULL,
  thumb VARCHAR(255),
  createdAt DATE NOT NULL,
  instructorId INT,
  FOREIGN KEY (instructorId) REFERENCES users(userId) ON DELETE SET NULL
);

--
-- Table structure for table `lessons`
--
CREATE TABLE lessons (
  lessonId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  title VARCHAR(100) NOT NULL,
  video VARCHAR(255),
  content VARCHAR(255),
  duration INT NOT NULL,
  courseId INT NOT NULL,
  FOREIGN KEY (courseId) REFERENCES courses(courseId) ON DELETE CASCADE
);

--
-- Table structure for table `enrollments`
--
CREATE TABLE enrollments (
  enrollmentId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  userId INT,
  courseId INT,
  enrollmentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  progress FLOAT DEFAULT 0,
  FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
  FOREIGN KEY (courseId) REFERENCES courses(courseId) ON DELETE CASCADE
);

--
-- Table structure for table `payments`
--
CREATE TABLE payments (
  paymentId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  userId INT,
  courseId INT,
  amount FLOAT NOT NULL,
  paymentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paymentMethod VARCHAR(50) NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
  FOREIGN KEY (courseId) REFERENCES courses(courseId) ON DELETE CASCADE
);

--
-- Table structure for table `reviews`
--
CREATE TABLE reviews (
  reviewId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  userId INT,
  courseId INT,
  rating INT CHECK (rating >= 1 AND rating <= 5),
  comment VARCHAR(50),
  reviewDate DATE NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
  FOREIGN KEY (courseId) REFERENCES courses(courseId) ON DELETE CASCADE
);

--
-- Table structure for table `admin`
--
CREATE TABLE admin (
  adminId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  firstName VARCHAR(50) NOT NULL,
  lastName VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  passwordHash VARCHAR(255) NOT NULL,
  instructorId INT,
  userId INT,
  courseId INT,
  FOREIGN KEY (courseId) REFERENCES courses(courseId) ON DELETE SET NULL,
  FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE SET NULL
  -- Note: Constraint for InstructorID removed because table `Instructor` does not exist in this dump.
);

--
-- Table structure for table `cart`
--
CREATE TABLE cart (
  cartId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  userId INT,
  courseId INT,
  addedAt DATE NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
  FOREIGN KEY (courseId) REFERENCES courses(courseId) ON DELETE CASCADE
);

--
-- Table structure for table `cartItems`
--
-- Renamed from `Cart-Item` because hyphens are invalid in standard table names
CREATE TABLE cartItems (
  cartItemId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  cartId INT,
  courseId INT,
  quantity INT NOT NULL DEFAULT 1,
  FOREIGN KEY (cartId) REFERENCES cart(cartId) ON DELETE CASCADE,
  FOREIGN KEY (courseId) REFERENCES courses(courseId) ON DELETE CASCADE
);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
COMMIT;

-- --------------------------------------------------------
-- Data Dump: Inserting Fake Data
-- --------------------------------------------------------

-- 1. Users (Roles: 0=Admin/Guest, 1=Student, 2=Instructor for context)
INSERT INTO users (fname, lname, email, password, role, joinedAt) VALUES
('Jane', 'Smith', 'jane.smith@lerno.com', 'hashedpass1', 2, '2023-10-05 10:00:00'),
('Mo', 'Nour', 'mo.nour@student.com', 'hashedpass2', 1, '2025-11-20 14:30:00'),
('Joe', 'Hany', 'joe.hany@student.com', 'hashedpass3', 1, '2025-11-22 09:15:00'),
('Fady', 'Cross', 'fady.cross@uni.edu', 'hashedpass4', 2, '2023-01-15 08:00:00'),
('Gerouge', 'Hany', 'gerouge.hany@student.com', 'hashedpass5', 1, '2025-11-25 16:45:00');

-- 2. Courses (Linked to Instructors: Jane (1) and Dr. Ann (4))
INSERT INTO courses (name, description, price, level, lang, thumb, createdAt, instructorId) VALUES
('Intro to C++', 'Learn the basics of programming using C++.', 49.99, 'Beginner', 'English', 'cpp_thumb.jpg', '2023-10-10', 1),
('Advanced MIPS Architecture', 'Pipelines, Datapath and Control Signals.', 120.00, 'Advanced', 'English', 'mips_thumb.jpg', '2025-10-01', 4),
('Web Development with Laravel', 'Build secure web apps and APIs.', 89.99, 'Intermediate', 'English', 'laravel_thumb.jpg', '2025-09-15', 1),
('Git & Version Control', 'Master git stash, reset, and team workflows.', 25.50, 'Beginner', 'English', 'git_thumb.jpg', '2025-11-01', 4);

-- 3. Lessons (Linked to Courses)
INSERT INTO lessons (title, video, content, duration, courseId) VALUES
('Setup Environment', 'video_cpp_1.mp4', 'Installing compiler', 15, 1),
('Variables and Loops', 'video_cpp_2.mp4', 'Int, String, For, While', 45, 1),
('Pipeline Hazards', 'video_mips_1.mp4', 'Structural and Data hazards', 60, 2),
('Laravel Routing', 'video_web_1.mp4', 'Defining routes in web.php', 30, 3),
('Git Stash vs Commit', 'video_git_1.mp4', 'Saving changes temporarily', 20, 4);

-- 4. Enrollments (Students 2, 3, 5 enrolled in courses)
INSERT INTO enrollments (userId, courseId, enrollmentDate, progress) VALUES
(2, 3, '2025-11-26 10:00:00', 45.5), -- Ali in Web Dev
(2, 2, '2025-12-07 11:00:00', 10.0), -- Ali in MIPS
(3, 3, '2025-11-27 09:30:00', 80.0), -- Jana in Web Dev
(5, 4, '2025-11-29 14:20:00', 100.0); -- Saif in Git

-- 5. Payments (Corresponding to enrollments)
INSERT INTO payments (userId, courseId, amount, paymentDate, paymentMethod) VALUES
(2, 3, 89.99, '2025-11-26 10:05:00', 'Credit Card'),
(2, 2, 120.00, '2025-12-07 11:05:00', 'PayPal'),
(3, 3, 89.99, '2025-11-27 09:35:00', 'Credit Card'),
(5, 4, 25.50, '2025-11-29 14:25:00', 'Debit Card');

-- 6. Reviews
INSERT INTO reviews (userId, courseId, rating, comment, reviewDate) VALUES
(5, 4, 5, 'Great explanation of git stash!', '2025-12-01'),
(3, 3, 4, 'Good project structure but fast paced.', '2025-12-05'),
(2, 2, 5, 'Dr. Ann explains logic puzzles well.', '2025-12-08');

-- 7. Admin
INSERT INTO admin (firstName, lastName, email, passwordHash, instructorId, userId) VALUES
('Super', 'Admin', 'root@admin.com', 'adminpass123', 1, 1);

-- 8. Cart (Active shopping carts)
INSERT INTO cart (userId, courseId, addedAt) VALUES
(2, 4, '2025-12-09'), -- Ali has Git course in cart
(5, 1, '2025-12-10'); -- Saif has C++ in cart

-- 9. CartItems
INSERT INTO cartItems (cartId, courseId, quantity) VALUES
(1, 4, 1),
(2, 1, 1);