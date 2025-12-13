<?php
require 'conn.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        die('Email and password are required.');
    }

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM Student WHERE Email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['PasswordHash'])) {
            echo 'Login successful. Welcome, ' . htmlspecialchars($user['FirstName']) . ' ' . htmlspecialchars($user['LastName']) . '!';
        } else {
            echo 'Invalid password.';
        }
    } else {
        echo 'No user found with this email.';
    }
}
?>