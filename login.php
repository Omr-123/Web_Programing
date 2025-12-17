<?php
session_start();
require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, fname, lname, email, password, role_id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['userId'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role_id'];
            $_SESSION['fullname'] = $user['fname'] . ' ' . $user['lname'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid credentials.';
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
                        <label>Forgot Password?</label>
                        <a href="reset-password.php">Reset Password</a>
                    </div>
                    <button type="submit" class="submit-btn">Sign In</button>
                </form>

                <div class="auth-footer-text">
                    Don't have an account? <a href="register.php">Sign Up</a>
                </div>
            </div>
        </div>
    </div>

<?php include("components/footer.php") ?>

</body>

</html>