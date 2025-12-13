<?php
session_start();
require 'conn.php';

// Require login
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

// Expect course_id
$courseId = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
if ($courseId <= 0) {
    header('Location: courses.php');
    exit();
}

$userId = $_SESSION['userId'];

// Enroll if not already enrolled
$ins = $conn->prepare("INSERT INTO enrollments (userId, courseId)
                       SELECT ?, ? FROM DUAL WHERE NOT EXISTS (
                           SELECT 1 FROM enrollments WHERE userId = ? AND courseId = ?
                       )");
$ins->bind_param('iiii', $userId, $courseId, $userId, $courseId);
$ins->execute();
$ins->close();

// Optional: remove from cart if present
$delCart = $conn->prepare('DELETE FROM cart WHERE userId = ? AND courseId = ?');
$delCart->bind_param('ii', $userId, $courseId);
$delCart->execute();
$delCart->close();

// Redirect to home
header('Location: index.php');
exit();
?>