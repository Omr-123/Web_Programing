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
}
?>