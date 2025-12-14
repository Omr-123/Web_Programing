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

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        die('All fields are required.');
    }

    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if ($role === 2) { // If instructor
        // Send a fake email
        $to = "info@lerno.com";
        $subject = "New Instructor Registration";
        $message = "Instructor Details:\nName: $first_name $last_name\nEmail: $email";
        $headers = "From: noreply@lerno.com";

        // Uncomment the line below to send the email in a real environment
        // mail($to, $subject, $message, $headers);

        // Redirect to approval page
        header('Location: instructor-approval.php');
        exit();
    } else {
        // Insert student into the database
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