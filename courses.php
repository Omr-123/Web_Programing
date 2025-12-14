<?php
session_start();
include_once 'conn.php';

// Only set $userId if session exists
$userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : null;

$stmt = $conn->prepare("SELECT id, title, description, price, thumbnail_url FROM courses WHERE is_published = 1 ORDER BY created_at DESC");
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Require login to add to cart
    if (!isset($_SESSION['userId'])) {
        header('Location: login.php');
        exit();
    }

    
    $courseID = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    if ($courseID <= 0) {
        header('Location: courses.php');
        exit();
    }

    // Avoid duplicate cart items: check cart and enrollments separately and close statements
    $checkCart = $conn->prepare("SELECT 1 FROM cart WHERE user_id = ? AND course_id = ? LIMIT 1");
    $checkCart->bind_param('ii', $userId, $courseID);
    $checkCart->execute();
    $cartRes = $checkCart->get_result();
    $inCart = $cartRes && $cartRes->num_rows > 0;
    $checkCart->close();

    $checkEnroll = $conn->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1");
    $checkEnroll->bind_param('ii', $userId, $courseID);
    $checkEnroll->execute();
    $enrollRes = $checkEnroll->get_result();
    $inCourses = $enrollRes && $enrollRes->num_rows > 0;
    $checkEnroll->close();

    if ($inCart) {
        // Already in cart, redirect back
        header('Location: cart.php');
        exit();
    } else if ($inCourses) {
        // Already enrolled, redirect back
        header('Location: my-courses.php');
        exit();
    }

    if (!$inCart && !$inCourses) {
        // include `addedAt` (use SQL CURDATE()) because the column is NOT NULL in the schema
        $stmt = $conn->prepare("INSERT INTO cart (user_id, course_id, added_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('ii', $userId, $courseID);
        if (!$stmt->execute()) {
            echo "Error adding to cart: " . $stmt->error;
        } else {
            // Redirect back to courses after handling
            header('Location: courses.php');
            exit();
        }
        $stmt->close();
    }
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
    <?php include("components/navbar.php") ?>

    <div class="section-title">
        <h1 class="section-name">Courses</h1>
        <p class="section-description">Catch Up</p>
    </div>
    
    <div class="courses">
        <?php foreach($courses as $course): ?>
        <div class="course">
            <img src="<?= htmlspecialchars($course['thumbnail_url'] ?? 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4') ?>" alt="course" class="course-image">
            <div class="course-details">
                <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                <span class="course-price">$<?= number_format((float)$course['price'],2) ?></span>
                <a href="course.php?id=<?= (int)$course['id'] ?>" class="course-button">Learn More</a>
                <!-- Form to handle Add To Cart -->
                <form action="courses.php" method="post">
                    <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
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

