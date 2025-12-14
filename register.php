<?php
session_start();
require 'conn.php'; // Database connection

// Check database connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = 1; // Default role

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        die('All fields are required.');
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, role, joinedAt) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $password_hash, $role);

    if ($stmt->execute()) {
        echo 'Registration successful. You can now log in.';
    } else {
        die('Registration failed: ' . $stmt->error);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playing: Python Basics - Lerno</title>
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/player.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/player.js" defer></script>
</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="player-wrapper">

        <div class="video-section">
            <div class="video-container">
                <iframe src="https://www.youtube.com/embed/kqtD5dpn9C8?rel=0" title="YouTube video player"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="video-info">
                <h1 id="lesson-title">1. Introduction to Python</h1>

                <div class="video-actions">
                    <a href="#" class="action-btn disabled">Previous Lesson</a>
                    <a href="#" class="action-btn primary" id="mark-complete">Mark as Complete</a>
                    <a href="play-course.html" class="action-btn">Next Lesson</a>
                </div>

                <div class="lesson-desc">
                    <h3>About this lesson</h3>
                    <p>In this video, we will cover the absolute basics of Python syntax, how to install Python on your
                        machine, and writing your very first "Hello World" program.</p>
                </div>
            </div>
        </div>

        <div class="playlist-section">
            <div class="playlist-header">
                <h3>Course Content</h3>
                <p>35 Lessons (5h 20m)</p>
            </div>

            <div class="playlist-items">

                <div class="playlist-group">
                    <div class="group-title">Section 1: Getting Started</div>

                    <a href="play-course.html" class="lesson-item active">
                        <div class="checkbox"></div>
                        <div class="lesson-details">
                            <span class="lesson-name">1. Introduction to Python</span>
                            <span class="lesson-time">10:00</span>
                        </div>
                    </a>

                    <a href="play-course.html" class="lesson-item">
                        <div class="checkbox"></div>
                        <div class="lesson-details">
                            <span class="lesson-name">2. Installing Python & VS Code</span>
                            <span class="lesson-time">15:30</span>
                        </div>
                    </a>

                    <a href="play-course.html" class="lesson-item">
                        <div class="checkbox"></div>
                        <div class="lesson-details">
                            <span class="lesson-name">3. Your First Program</span>
                            <span class="lesson-time">08:45</span>
                        </div>
                    </a>
                </div>

                <div class="playlist-group">
                    <div class="group-title">Section 2: Variables</div>

                    <a href="#" class="lesson-item locked-item">
                        <div class="checkbox locked">🔒</div>
                        <div class="lesson-details">
                            <span class="lesson-name">4. Understanding Variables</span>
                            <span class="lesson-time">12:20</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>