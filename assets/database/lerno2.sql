-- Lerno2 database schema
-- Charset: utf8mb4, Collation: utf8mb4_unicode_ci

DROP DATABASE IF EXISTS lerno2;
CREATE DATABASE lerno2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lerno2;

-- Users and roles
CREATE TABLE roles (
  id TINYINT UNSIGNED PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fname VARCHAR(50) NOT NULL,
  lname VARCHAR(50) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id TINYINT UNSIGNED NOT NULL,
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Courses
CREATE TABLE courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instructor_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  description TEXT NOT NULL,
  thumbnail_url VARCHAR(500) NULL,
  language VARCHAR(40) DEFAULT 'en',
  level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  INDEX(instructor_id),
  CONSTRAINT fk_courses_instructor FOREIGN KEY (instructor_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Lessons (videos)
CREATE TABLE lessons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  video_url VARCHAR(500) NULL,
  content TEXT NULL,
  duration_seconds INT UNSIGNED DEFAULT 0,
  position INT UNSIGNED NOT NULL,
  is_preview TINYINT(1) DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(course_id),
  CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Enrollments
CREATE TABLE enrollments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_course (user_id, course_id),
  INDEX(user_id), INDEX(course_id),
  CONSTRAINT fk_enroll_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_enroll_course FOREIGN KEY (course_id) REFERENCES courses(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Progress per lesson
CREATE TABLE lesson_progress (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  lesson_id INT UNSIGNED NOT NULL,
  status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
  last_watched_second INT UNSIGNED DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_lesson (user_id, lesson_id),
  INDEX(user_id), INDEX(lesson_id),
  CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_progress_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Course assets (images or resources)
CREATE TABLE course_assets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  kind ENUM('image','resource') NOT NULL,
  url VARCHAR(500) NOT NULL,
  title VARCHAR(200) NULL,
  position INT UNSIGNED DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(course_id),
  CONSTRAINT fk_assets_course FOREIGN KEY (course_id) REFERENCES courses(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed roles
INSERT INTO roles (id, name) VALUES
  (1, 'student'),
  (2, 'instructor'),
  (3, 'admin');

-- Seed users (passwords are bcrypt placeholders)
INSERT INTO users (fname, lname, email, password, role_id) VALUES
  ('Seif', 'Emad', 'seifemad@hotmail.com', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJK', 2),
  ('John', 'Doe', 'john@example.com', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJK', 1),
  ('Admin', 'User', 'admin@lerno.com', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJK', 3);

-- Seed courses
INSERT INTO courses (instructor_id, title, slug, description, thumbnail_url, language, level, price) VALUES
  (1, 'Full-Stack Web Development', 'full-stack-web-dev', 'Learn HTML, CSS, JS, PHP, and MySQL by building projects.', 'https://images.unsplash.com/photo-1517433456452-f9633a875f6f', 'en', 'beginner', 69),
  (1, 'Advanced PHP Patterns', 'advanced-php-patterns', 'Master advanced PHP design patterns and best practices.', 'https://images.unsplash.com/photo-1518779578993-ec3579fee39f', 'en', 'advanced', 49);

-- Seed lessons (YouTube embeds)
INSERT INTO lessons (course_id, title, video_url, content, duration_seconds, position, is_preview) VALUES
  (1, 'Intro to Web Dev', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Overview and setup', 600, 1, 1),
  (1, 'HTML Basics', 'https://www.youtube.com/embed/pQN-pnXPaVg', 'Learn HTML structure', 900, 2, 0),
  (1, 'CSS Fundamentals', 'https://www.youtube.com/embed/yfoY53QXEnI', 'Styling web pages', 1200, 3, 0),
  (2, 'SOLID in PHP', 'https://www.youtube.com/embed/TMuno5RZNeE', 'Applying SOLID principles', 1100, 1, 1),
  (2, 'Dependency Injection', 'https://www.youtube.com/embed/2lZ-LwB1nBM', 'DI containers and patterns', 1000, 2, 0);

-- Seed enrollments
INSERT INTO enrollments (user_id, course_id) VALUES
  (2, 1),
  (2, 2);

-- Seed assets
INSERT INTO course_assets (course_id, kind, url, title, position) VALUES
  (1, 'image', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c', 'Coding Setup', 1),
  (1, 'image', 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4', 'HTML & CSS', 2),
  (2, 'image', 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d', 'PHP Patterns', 1);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_course (user_id, course_id),
  INDEX(user_id), INDEX(course_id),
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_cart_course FOREIGN KEY (course_id) REFERENCES courses(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
