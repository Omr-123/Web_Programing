<?php
session_start();
require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = ($_POST['role'] == 'instructor') ? 2 : 1;

    $avatar = trim($_POST['avatar']) ?: null;

    // Simple validation
    if (!$first_name || !$last_name || !$email || !$password || !$confirm_password) {
        $error = 'All fields are required.';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match.';
    } else {

        // Check email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param('s', $email);
        $check->execute();
        $r = $check->get_result();
        $exists = $r->fetch_assoc();
        $check->close();

        if ($exists) {
            $error = 'Email already exists.';
        } else {

            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // joined_at has default current_timestamp() in SQL, so no need to insert it
            $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, role_id, avatar) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssis', $first_name, $last_name, $email, $password_hash, $role, $avatar);

            if ($stmt->execute()) {
                $stmt->close();
                header('Location: login.php');
                exit();
            }

            $stmt->close();
            $error = 'Registration failed.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/auth.css">
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

            <?php if ($error): ?>
                <div class="auth-error" style="color:#c53030;margin-bottom:12px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="registerForm" action="register.php" method="post">
                <div class="input-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>

                <div class="input-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
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
                    <input type="url" name="avatar" placeholder="https://example.com/image.jpg">
                </div>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="auth-footer-text">
                Already have an account? <a href="login.php">Sign In</a>
            </div>

        </div>
    </div>
</div>

<?php include("components/footer.php") ?>

</body>
</html>