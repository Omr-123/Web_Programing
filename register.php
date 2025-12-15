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
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) && $_POST['role'] === 'instructor' ? 2 : 1; // Default role is student
    $profile_image_url = (isset($_POST['profile_image_url']) && !empty($_POST['profile_image_url'])) ? $_POST['profile_image_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($first_name . '+' . $last_name) . '&size=200&background=9a0176&color=fff';
    $instructor_bio = (isset($_POST['instructor_bio']) && !empty($_POST['instructor_bio'])) ? $_POST['instructor_bio'] : null;

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        die('All fields are required.');
    }

    // Photo and bio are optional for all roles

    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user with optional profile image and bio for both roles
    $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, role_id, profile_image_url, bio, joined_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) { die('Prepare failed: ' . $conn->error); }
    $stmt->bind_param("ssssiss", $first_name, $last_name, $email, $password_hash, $role, $profile_image_url, $instructor_bio);
    if ($stmt->execute()) {
        header('Location: login.php');
        exit();
    } else { die('Registration failed: ' . $stmt->error); }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/auth.js"></script>
    <script>
        // Optional fields; keep visible for all roles and never required
        $(document).ready(function() {
            // No dynamic required toggling; fields are optional
        });
    </script>

</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>Join us and start learning today.</p>
            </div>

            <div class="form-box">
                <form id="registerForm" action="register.php" method="post" novalidate>
                    <div class="input-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" placeholder="John" required>
                    </div>
                    <div class="input-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" placeholder="Doe" required>
                    </div>
                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="example@email.com" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="input-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    <div class="input-group">
                        <label>Role</label>
                        <div>
                            <input type="radio" name="role" value="student" id="student" checked>
                            <label for="student">Student</label>
                            <input type="radio" name="role" value="instructor" id="instructor">
                            <label for="instructor">Instructor</label>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Profile Image URL (optional)</label>
                        <input type="url" name="profile_image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="input-group">
                        <label>Bio (optional)</label>
                        <textarea name="instructor_bio" placeholder="Tell us about yourself..." rows="4"></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Create Account</button>
                </form>

                <div class="auth-footer-text">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>

</body>

</html>