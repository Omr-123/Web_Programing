<?php
session_start();
include_once('conn.php');

// Redirect to login if not authenticated
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}
?>

<?php 
// Fetch courses enrolled by the logged-in user using a prepared statement
$sql = "SELECT courses.* FROM courses
        LEFT JOIN enrollments ON courses.courseId = enrollments.courseId
        WHERE enrollments.userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['userId']);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/courses.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/my-courses.js" defer></script>
    <script src="assets/js/courses.js" defer></script>
</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="section-title">
        <h1 class="section-name">My Courses</h1>
        <p class="section-description">Keep up the good work!</p>
    </div>

    <div class="courses">
        <?php foreach($courses as $course): ?>
        <a href="course-player.php" class="course">
            <img src="assets/images/<?php echo $course['thumb'] ?>" alt="course" class="course-image">
            <div class="course-details">
                <h3 class="course-title"><?php echo $course['name'] ?></h3>
                <p class="course-description"><?php echo $course['description'] ?></p>
                <span class="course-progress" value="100%"></span>
            </div>
        </a>
        <?php endforeach ?>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>
</body>

</html>