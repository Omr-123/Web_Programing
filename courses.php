<?php
session_start();
require 'conn.php';

// Fetch courses
$sql = "SELECT c.id, c.title, c.description, c.price, c.thumbnail_url, u.fname, u.lname, u.avatar FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.status = 1 ORDER BY c.created_at DESC";
$result = $conn->query($sql);
$courses = $result->fetch_all(MYSQLI_ASSOC);

// Handle Add To Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_SESSION['userId'])) {
        header('Location: login.php');
        exit();
    }

    $userId = $_SESSION['userId'];
    $courseId = $_POST['course_id'];

    $check = $conn->query("SELECT id FROM cart WHERE user_id = $userId AND course_id = $courseId");
    if ($check->num_rows > 0) {
        header('Location: cart.php');
        exit();
    }

    $check2 = $conn->query("SELECT id FROM enrollments WHERE user_id = $userId AND course_id = $courseId");
    if ($check2->num_rows > 0) {
        header('Location: my-courses.php');
        exit();
    }

    $conn->query("INSERT INTO cart (user_id, course_id, added_at) VALUES ($userId, $courseId, NOW())");
    header('Location: courses.php');
    exit();
}
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
    <script src="assets/js/courses.js" defer></script>
</head>

<body>

<?php include("components/navbar.php"); ?>

<div class="section-title">
    <h1 class="section-name">Courses</h1>
    <p class="section-description">Catch Up</p>
</div>

<div class="courses">
    <?php foreach ($courses as $course): ?>
        <div class="course">
            <img src="<?= $course['thumbnail_url'] != '' ? $course['thumbnail_url'] : 'assets/images/course.png' ?>" class="course-image">

            <div class="course-details">
                <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>

                <p style="font-size:12px;color:#666;margin:8px 0;">
                    <strong>Instructor:</strong>
                    <?= htmlspecialchars($course['fname'] . ' ' . $course['lname']) ?>
                </p>

                <span class="course-price">$<?= number_format($course['price'], 2) ?></span>

                <a href="course.php?id=<?= $course['id'] ?>" class="course-button">Learn More</a>

                <form method="post">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <button type="submit" class="course-button">Add To Cart</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="footer">
    <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
</div>

</body>
</html>
