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
// Fetch courses enrolled by the logged-in user (lerno2 schema)
$sql = "SELECT c.id, c.title, c.slug, c.description, c.thumbnail_url
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    WHERE e.user_id = ?
    ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['userId']);
$stmt->execute();
$result = $stmt->get_result();
$courses = [];
while ($row = $result->fetch_assoc()) { $courses[] = $row; }
$stmt->close();

// Compute progress for each course
$user_id = (int)$_SESSION['userId'];
$courseProgress = [];
foreach ($courses as $course) {
    $cid = (int)$course['id'];
    // Total lessons
    $stmtTotal = $conn->prepare("SELECT COUNT(*) as total FROM lessons WHERE course_id = ?");
    $stmtTotal->bind_param('i', $cid);
    $stmtTotal->execute();
    $totalRes = $stmtTotal->get_result()->fetch_assoc();
    $total = (int)$totalRes['total'];
    $stmtTotal->close();
    
    // Completed lessons
    $stmtDone = $conn->prepare("SELECT COUNT(*) as done FROM lesson_progress lp JOIN lessons l ON l.id = lp.lesson_id WHERE l.course_id = ? AND lp.user_id = ? AND lp.status = 'completed'");
    $stmtDone->bind_param('ii', $cid, $user_id);
    $stmtDone->execute();
    $doneRes = $stmtDone->get_result()->fetch_assoc();
    $done = (int)$doneRes['done'];
    $stmtDone->close();
    
    $courseProgress[$cid] = $total > 0 ? round(($done / $total) * 100) : 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
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
        <?php $progress = $courseProgress[(int)$course['id']] ?? 0; ?>
        <a href="course-player.php?course_id=<?= (int)$course['id'] ?>" class="course">
            <img src="<?= htmlspecialchars($course['thumbnail_url'] ?? 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4') ?>" alt="course" class="course-image">
            <div class="course-details">
                <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                <div style="width:100%;background:#e5e7eb;border-radius:4px;height:8px;margin-top:8px;">
                    <div style="width:<?= $progress ?>%;background:#10b981;height:100%;border-radius:4px;"></div>
                </div>
                <span class="course-progress" style="display:block;margin-top:4px;font-size:13px;color:#6b7280;"><?= $progress ?>% Complete</span>
            </div>
        </a>
        <?php endforeach ?>
        <?php if (empty($courses)): ?>
            <p>You have no enrolled courses yet.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>
</body>

</html>