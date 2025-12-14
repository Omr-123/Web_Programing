<?php
session_start();
require 'conn.php';
if (!isset($_SESSION['userId'])) { header('Location: login.php'); exit(); }
if (!isset($_GET['course_id'])) { header('Location: my-courses.php'); exit(); }
$course_id = (int)$_GET['course_id'];
$user_id = (int)$_SESSION['userId'];

// Handle mark as complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $lesson_id = (int)$_POST['lesson_id'];
    $stmt = $conn->prepare("INSERT INTO lesson_progress (user_id, lesson_id, status) VALUES (?, ?, 'completed') ON DUPLICATE KEY UPDATE status='completed'");
    $stmt->bind_param('ii', $user_id, $lesson_id);
    $stmt->execute();
    $stmt->close();
    header("Location: course-player.php?course_id=$course_id&lesson_id=$lesson_id");
    exit();
}

$course = null;
$lessons = [];
// Fetch course
$stmt = $conn->prepare("SELECT id, title, description FROM courses WHERE id = ?");
if ($stmt) { $stmt->bind_param('i', $course_id); $stmt->execute(); $course = $stmt->get_result()->fetch_assoc(); $stmt->close(); }
// Fetch lessons ordered
$stmt = $conn->prepare("SELECT id, title, video_url, content, duration_seconds, position FROM lessons WHERE course_id = ? ORDER BY position ASC");
if ($stmt) { $stmt->bind_param('i', $course_id); $stmt->execute(); $res = $stmt->get_result(); while ($row = $res->fetch_assoc()) { $lessons[] = $row; } $stmt->close(); }

// Determine active lesson
$lesson_id = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : null;
$activeLesson = null;
$activeLessonIndex = 0;
if ($lesson_id) {
    foreach ($lessons as $idx => $les) {
        if ((int)$les['id'] === $lesson_id) { $activeLesson = $les; $activeLessonIndex = $idx; break; }
    }
}
if (!$activeLesson && !empty($lessons)) { $activeLesson = $lessons[0]; $activeLessonIndex = 0; }

// Get progress for all lessons
$progressMap = [];
$stmt = $conn->prepare("SELECT lesson_id, status FROM lesson_progress WHERE user_id = ?");
if ($stmt) { $stmt->bind_param('i', $user_id); $stmt->execute(); $res = $stmt->get_result(); while ($row = $res->fetch_assoc()) { $progressMap[(int)$row['lesson_id']] = $row['status']; } $stmt->close(); }

// Previous/Next
$prevLesson = $activeLessonIndex > 0 ? $lessons[$activeLessonIndex - 1] : null;
$nextLesson = $activeLessonIndex < count($lessons) - 1 ? $lessons[$activeLessonIndex + 1] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playing: <?= htmlspecialchars($course['title'] ?? 'Course') ?> - Lerno</title>
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/player.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/player.js" defer></script>
</head>

<body>

    <?php include("components/navbar.php"); ?>

    <div class="player-wrapper">

        <div class="video-section">
            <div class="video-container">
                <iframe src="<?= htmlspecialchars(($activeLesson['video_url'] ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ')) ?>" title="YouTube video player"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="video-info">
                <h1 id="lesson-title"><?= htmlspecialchars($activeLesson['position'] ?? 1) ?>. <?= htmlspecialchars($activeLesson['title'] ?? 'Lesson') ?></h1>

                <div class="video-actions">
                    <?php if ($prevLesson): ?>
                        <a href="course-player.php?course_id=<?= (int)$course_id ?>&lesson_id=<?= (int)$prevLesson['id'] ?>" class="action-btn">Previous Lesson</a>
                    <?php else: ?>
                        <a href="#" class="action-btn disabled">Previous Lesson</a>
                    <?php endif; ?>
                    <?php if ($activeLesson): ?>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="lesson_id" value="<?= (int)$activeLesson['id'] ?>" />
                        <button type="submit" name="mark_complete" class="action-btn primary">Mark as Complete</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($nextLesson): ?>
                        <a href="course-player.php?course_id=<?= (int)$course_id ?>&lesson_id=<?= (int)$nextLesson['id'] ?>" class="action-btn">Next Lesson</a>
                    <?php else: ?>
                        <a href="#" class="action-btn disabled">Next Lesson</a>
                    <?php endif; ?>
                </div>

                <div class="lesson-desc">
                    <h3>About this lesson</h3>
                    <p><?= htmlspecialchars($activeLesson['content'] ?? 'Enjoy the lesson!') ?></p>
                </div>
            </div>
        </div>

        <div class="playlist-section">
            <div class="playlist-header">
                <h3>Course Content</h3>
                <p>35 Lessons (5h 20m)</p>
            </div>

            <div class="playlist-items">
                <?php foreach ($lessons as $lesson): ?>
                    <?php $isCompleted = isset($progressMap[(int)$lesson['id']]) && $progressMap[(int)$lesson['id']] === 'completed'; ?>
                    <a href="course-player.php?course_id=<?= (int)$course_id ?>&lesson_id=<?= (int)$lesson['id'] ?>" class="lesson-item <?= ($activeLesson && $activeLesson['id']==$lesson['id']) ? 'active' : '' ?>">
                        <div class="checkbox"><?= $isCompleted ? '✓' : '' ?></div>
                        <div class="lesson-details">
                            <span class="lesson-name"><?= (int)$lesson['position'] ?>. <?= htmlspecialchars($lesson['title']) ?></span>
                            <span class="lesson-time"><?= (int)$lesson['duration_seconds'] > 0 ? gmdate('i:s', (int)$lesson['duration_seconds']) : '' ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>