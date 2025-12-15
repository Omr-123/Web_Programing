<?php
session_start();
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        die('Email and password are required.');
    }

    // Check if the user exists (lerno2 schema)
    $stmt = $conn->prepare("SELECT id, fname, lname, email, password, role_id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session and redirect without printing a message
            $_SESSION['userId'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role_id'];
            $_SESSION['fullname'] = $user['fname'] . ' ' . $user['lname'];
            header("Location: index.php");
            exit();
        } else {
            echo 'Invalid password.';
        }
    } else {
        echo 'No user found with this email.';
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
                <h2>Welcome Back</h2>
                <p>Please enter your details to sign in.</p>
            </div>

            <div class="form-box">
                <form id="loginForm" action="login.php" method="post" novalidate>
                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="example@email.com" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="form-options">
                        <label><input type="checkbox"> Remember me</label>
                        <a href="reset-password.html">Reset Password</a>
                    </div>
                    <button type="submit" class="submit-btn">Sign In</button>
                </form>

                <div class="auth-footer-text">
                    Don't have an account? <a href="register.html">Sign Up</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>

</body>

</html>