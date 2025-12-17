<?php
session_start();
require 'conn.php';

if (!isset($_SESSION['userId'])) {
    header('Location: my-courses.php');
    exit();
}

$userId = $_SESSION['userId'];
$courseId = $_GET['course_id'];

// Mark lesson as complete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_complete'])) {
    $lessonId = $_POST['lesson_id'];

    $stmt = $conn->prepare("INSERT INTO lessons_progress (user_id, lesson_id, completed_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE completed_at = NOW()");
    $stmt->bind_param('ii', $userId, $lessonId);
    $stmt->execute();
    $stmt->close();

    header("Location: course-player.php?course_id=$courseId&lesson_id=$lessonId");
    exit();
}

// Course info
$sql = "SELECT id, title, description FROM courses WHERE id = $courseId";
$result = $conn->query($sql);
$course = $result->fetch_assoc();

// Lessons
$sql = "SELECT id, title, video_url, content, duration_seconds, `order` AS position FROM lessons WHERE course_id = $courseId ORDER BY `order` ASC";
$result = $conn->query($sql);
$lessons = $result->fetch_all(MYSQLI_ASSOC);

// Active lesson
$lessonId = $_GET['lesson_id'] ?? $lessons[0]['id'];
$activeLesson = $lessons[0];
$activeLessonIndex = 0;

foreach ($lessons as $index => $lesson) {
    if ($lesson['id'] == $lessonId) {
        $activeLesson = $lesson;
        $activeLessonIndex = $index;
        break;
    }
}

// Progress
$sql = "SELECT lesson_id, completed_at FROM lessons_progress WHERE user_id = $userId";
$result = $conn->query($sql);
$progressRows = $result->fetch_all(MYSQLI_ASSOC);
$progressMap = array();

foreach ($progressRows as $row) {
    $progressMap[$row['lesson_id']] = 'completed';
}

// Previous / Next
$prevLesson = $activeLessonIndex > 0 ? $lessons[$activeLessonIndex - 1] : null;
$nextLesson = $activeLessonIndex < count($lessons) - 1 ? $lessons[$activeLessonIndex + 1] : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Playing: <?= htmlspecialchars($course['title']) ?> - Lerno</title>
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/player.css">
    <script src="assets/js/player.js" defer></script>
</head>

<body>

    <?php include("components/navbar.php"); ?>

    <div class="player-wrapper">

        <div class="video-section">
            <div class="video-container">
                <iframe src="<?= htmlspecialchars($activeLesson['video_url']) ?>" allowfullscreen></iframe>
            </div>

            <div class="video-info">
                <h1><?= $activeLesson['position'] . '. ' . htmlspecialchars($activeLesson['title']) ?></h1>

                <div class="video-actions">
                    <?php if ($prevLesson): ?>
                        <a href="course-player.php?course_id=<?= $courseId ?>&lesson_id=<?= $prevLesson['id'] ?>" class="action-btn">Previous Lesson</a>
                    <?php else: ?>
                        <a href="#" class="action-btn disabled">Previous Lesson</a>
                    <?php endif; ?>

                    <form method="post" style="display:inline">
                        <input type="hidden" name="lesson_id" value="<?= $activeLesson['id'] ?>">
                        <button type="submit" name="mark_complete" class="action-btn primary">Mark as Complete</button>
                    </form>

                    <?php if ($nextLesson): ?>
                        <a href="course-player.php?course_id=<?= $courseId ?>&lesson_id=<?= $nextLesson['id'] ?>" class="action-btn">Next Lesson</a>
                    <?php else: ?>
                        <a href="#" class="action-btn disabled">Next Lesson</a>
                    <?php endif; ?>
                </div>

                <div class="lesson-desc">
                    <h3>About this lesson</h3>
                    <p><?= htmlspecialchars($activeLesson['content']) ?></p>
                </div>
            </div>
        </div>

        <div class="playlist-section">
            <div class="playlist-header">
                <h3>Course Content</h3>
            </div>

            <div class="playlist-items">
                <?php foreach ($lessons as $lesson): ?>
                    <a href="course-player.php?course_id=<?= $courseId ?>&lesson_id=<?= $lesson['id'] ?>" class="lesson-item <?= $lesson['id'] == $activeLesson['id'] ? 'active' : '' ?>">
                        <div class="checkbox"><?= isset($progressMap[$lesson['id']]) ? '✓' : '' ?></div>
                        <div class="lesson-details">
                            <span class="lesson-name"><?= $lesson['position'] ?>. <?= htmlspecialchars($lesson['title']) ?></span>
                            <span class="lesson-time"><?= $lesson['duration_seconds'] ? gmdate('i:s', $lesson['duration_seconds']) : '' ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>