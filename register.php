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
    $profile_image_url = (isset($_POST['profile_image_url']) && !empty($_POST['profile_image_url'])) ? $_POST['profile_image_url'] : null;
    $instructor_bio = (isset($_POST['instructor_bio']) && !empty($_POST['instructor_bio'])) ? $_POST['instructor_bio'] : null;

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        die('All fields are required.');
    }

    // For instructors, profile image URL is required
    if ($role === 2 && empty($profile_image_url)) {
        die('Instructors must provide a profile image URL.');
    }
    // For instructors, bio is also required
    if ($role === 2 && empty($instructor_bio)) {
        die('Instructors must provide a professional bio.');
    }

    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if ($role === 2) { // If instructor
        // Insert instructor user with profile image and bio
        $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, role_id, profile_image_url, bio, joined_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        // 7 parameters: s,s,s,s,i,s,s
        $stmt->bind_param("ssssiss", $first_name, $last_name, $email, $password_hash, $role, $profile_image_url, $instructor_bio);
        if ($stmt->execute()) {
            header('Location: login.php');
            exit();
        } else { 
            die('Registration failed: ' . $stmt->error); 
        }
    } else {
        // Insert student into the database
        $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, role_id, joined_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$stmt) { die('Prepare failed: ' . $conn->error); }
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $password_hash, $role);
        if ($stmt->execute()) { echo 'Registration successful. You can now log in.'; }
        else { die('Registration failed: ' . $stmt->error); }
    }
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
        // Show/hide profile image and bio fields based on role selection
        $(document).ready(function() {
            function setInstructorFields(required) {
                const img = $('input[name="profile_image_url"]');
                const bio = $('textarea[name="instructor_bio"]');
                if (required) {
                    $('#instructor-image-group').show();
                    $('#instructor-bio-group').show();
                    img.attr('required', true);
                    bio.attr('required', true);
                } else {
                    $('#instructor-image-group').hide();
                    $('#instructor-bio-group').hide();
                    img.removeAttr('required');
                    bio.removeAttr('required');
                }
            }

            // Initialize based on default checked role
            const isInstructor = $('input[name="role"][value="instructor"]').is(':checked');
            setInstructorFields(isInstructor);

            // Toggle on change
            $('input[name="role"]').on('change', function() {
                setInstructorFields($(this).val() === 'instructor');
            });
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
                    <div class="input-group" id="instructor-image-group" style="display: none;">
                        <label>Profile Image URL (for instructors)</label>
                        <input type="url" name="profile_image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="input-group" id="instructor-bio-group" style="display: none;">
                        <label>Professional Bio (for instructors)</label>
                        <textarea name="instructor_bio" placeholder="Describe your expertise and experience..." rows="4"></textarea>
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