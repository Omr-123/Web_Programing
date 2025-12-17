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
$ins = $conn->prepare("INSERT INTO enrollments (user_id, course_id, enrolled_at)
                       SELECT ?, ?, NOW() FROM DUAL WHERE NOT EXISTS (
                           SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?
                       )");
$ins->bind_param('iiii', $userId, $courseId, $userId, $courseId);
$ins->execute();
$ins->close();

// Optional: remove from cart if present
$delCart = $conn->prepare('DELETE FROM cart WHERE user_id = ? AND course_id = ?');
$delCart->bind_param('ii', $userId, $courseId);
$delCart->execute();
$delCart->close();

// Redirect to my courses
header('Location: my-courses.php');
exit();
?>