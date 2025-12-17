<?php
// Database connection settings
$servername = "127.0.0.1"; // prefer TCP to avoid unix-socket issues on some macOS setups
$username = "root";
$password = ""; // update if your MySQL root has a password
$db = "lerno";

// Create connection
$conn = new mysqli($servername, $username, $password, $db);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>