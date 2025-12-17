<?php
session_start();
require 'conn.php';

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

// Fetch enrolled courses
$sql = "SELECT * FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.user_id = $userId ORDER BY c.created_at DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// Compute progress
foreach ($courses as $course) {

    $cid = $course['id'];

    $totalRes = $conn->query("SELECT COUNT(*) AS total FROM lessons WHERE course_id = $cid");
    $total = $totalRes->fetch_assoc()['total'];

    $doneRes = $conn->query("SELECT COUNT(*) AS done FROM lessons_progress lp JOIN lessons l ON l.id = lp.lesson_id WHERE l.course_id = $cid AND lp.user_id = $userId AND lp.completed_at IS NOT NULL");
    $done = $doneRes->fetch_assoc()['done'];

    if ($total > 0) {
        $courseProgress[$cid] = round(($done / $total) * 100);
    } else {
        $courseProgress[$cid] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/courses.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/courses.js" defer></script>
    <script src="assets/js/my-courses.js" defer></script>
</head>

<body>

<?php include("components/navbar.php"); ?>

<div class="section-title">
    <h1 class="section-name">My Courses</h1>
    <p class="section-description">Keep up the good work!</p>
</div>

<div class="courses">

    <?php if (!empty($courses)): ?>
    <?php foreach ($courses as $course): ?>
        <?php $progress = $courseProgress[$course['id']]; ?>
        <a href="course-player.php?course_id=<?= $course['id'] ?>" class="course">
            <img src="<?= $course['thumbnail_url'] != '' ? $course['thumbnail_url'] : 'assets/images/course.png' ?>" class="course-image">
            <div class="course-details">
                <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>

                <div style="width:100%;background:#e5e7eb;border-radius:4px;height:8px;margin-top:8px;">
                    <div style="width:<?= $progress ?>%;background:#10b981;height:100%;border-radius:4px;"></div>
                </div>

                <span style="display:block;margin-top:4px;font-size:13px;color:#6b7280;"><?= $progress ?>% Complete</span>
            </div>
        </a>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!isset($courses)): ?>
        <p>You have no enrolled courses yet.</p>
    <?php endif; ?>

</div>

<?php include("components/footer.php") ?>

</body>
</html>