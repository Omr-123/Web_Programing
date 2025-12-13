<?php
require 'conn.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        die('All fields are required.');
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password,role, joinedAt) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssss', $first_name, $last_name, $email,$role, $password_hash);

    if ($stmt->execute()) {
        echo 'Registration successful. You can now log in.';
    } else {
        echo 'Registration failed. Please try again.';
    }
}
?>