<?php
session_start();
require 'conn.php';
$course = null;
if (isset($_GET['id'])) {
    $courseId = $_GET['id'];
    $stmt = $conn->prepare("SELECT id, title, description, price, level, thumbnail_url FROM courses WHERE id = ?");
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $res = $stmt->get_result();
    $course = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}
if (!$course) { header('Location: courses.php'); exit(); }
// Lessons summary
$stmt = $conn->prepare("SELECT COUNT(*) as cnt, SUM(duration_seconds) as dur FROM lessons WHERE course_id = ?");
$stmt->bind_param('i', $course['id']);
$stmt->execute();
$res = $stmt->get_result();
$stats = $res ? $res->fetch_assoc() : null;
$lessonCount = isset($stats['cnt']) ? $stats['cnt'] : 0;
$durationTotal = isset($stats['dur']) ? $stats['dur'] : 0;
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?></title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/course.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/course.js" defer></script>
</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="hero">
        <div class="hero-content">
            <img src="<?= htmlspecialchars(isset($course['thumbnail_url']) && $course['thumbnail_url'] !== '' ? $course['thumbnail_url'] : 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4') ?>" alt="thumbnail" style="max-width:240px;border-radius:8px;margin-bottom:12px;" />
            <h1><?= htmlspecialchars($course['title']) ?></h1>
            <div class="course-desc">
                <?= nl2br(htmlspecialchars($course['description'])) ?>
            </div>

            <div class="stats">
                <div><strong><?= $lessonCount ?></strong>Lessons</div>
                <div><strong><?= $durationTotal ? gmdate('H\h i\m', $durationTotal) : '—' ?></strong>Total time</div>
                <div><strong><?= htmlspecialchars($course['level']) ?></strong>Level</div>
            </div>
        </div>
    </div>

    <div class="main-container">

        <div class="content">

            <div class="curriculum-title">Curriculum</div>
            <?php
            $stmt = $conn->prepare("SELECT `order` AS position, title, duration_seconds FROM lessons WHERE course_id = ? ORDER BY `order` ASC");
            $stmt->bind_param('i', $course['id']);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) { while ($r = $res->fetch_assoc()) { $items[] = $r; } }
            $stmt->close();
            ?>
            <div class="curriculum-list">
                <?php foreach ($items as $it): ?>
                <div class="curriculum-item">
                    <div class="circle"><?= $it['position'] ?></div>
                    <div class="text">
                        <h3><?= htmlspecialchars($it['title']) ?></h3>
                        <p><?= $it['duration_seconds'] ? gmdate('i:s', $it['duration_seconds']) : '' ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="sidebar">

            <div class="box">
                <div class="price-title"><?= $course['price'] > 0 ? '$' . number_format($course['price'],2) : 'Free' ?></div>
                <div class="small-note">Get a certificate after completing this course</div>

                <form action="enroll.php" method="post">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <button type="submit" class="enroll-btn">Enroll Now</button>
                </form>
                <button class="secondary-btn">♡ Add to Wishlist</button>
                <button class="secondary-btn">↗ Share This Course</button>
            </div>

            <div class="box">
                <div class="details-row"><strong>Duration:</strong> 10 weeks</div>
                <div class="details-row"><strong>Level:</strong> Beginner</div>
                <div class="details-row"><strong>Students:</strong> 18.2k</div>
                <div class="details-row"><strong>Rating:</strong> ⭐ 4.9</div>
            </div>

            <div class="help-box">
                Need help? Our support team is available 24/7.
            </div>

        </div>
    </div>

<?php include("components/footer.php") ?>

</body>

</html>