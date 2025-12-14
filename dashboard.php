<?php
session_start();
require 'conn.php';
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 2) {
    header('Location: login.php');
    exit();
}
$userId = (int)($_SESSION['userId'] ?? 0);

// Handle create course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_course') {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $thumbnail_url = trim($_POST['thumbnail_url'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $level = $_POST['level'] ?? 'beginner';
        if ($title && $slug) {
            $stmt = $conn->prepare("INSERT INTO courses (instructor_id, title, slug, description, thumbnail_url, price, level) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issssds', $userId, $title, $slug, $description, $thumbnail_url, $price, $level);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'create_lesson') {
        $course_id = (int)($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $position = (int)($_POST['position'] ?? 1);
        $duration = (int)($_POST['duration_seconds'] ?? 0);
        if ($course_id && $title) {
            $stmt = $conn->prepare("INSERT INTO lessons (course_id, title, video_url, content, position, duration_seconds) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssii', $course_id, $title, $video_url, $content, $position, $duration);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'delete_course') {
        $course_id = (int)($_POST['course_id'] ?? 0);
        if ($course_id) {
            $stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND instructor_id = ?");
            $stmt->bind_param('ii', $course_id, $userId);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'delete_lesson') {
        $lesson_id = (int)($_POST['lesson_id'] ?? 0);
        if ($lesson_id) {
            $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
            $stmt->bind_param('i', $lesson_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch my courses
$courses = [];
$stmt = $conn->prepare("SELECT id, title, slug FROM courses WHERE instructor_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $courses[] = $row; }
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .container{max-width:1100px;margin:24px auto;padding:0 16px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
        .card{border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff}
        input,textarea,select{width:100%;padding:10px;margin:6px 0;border:1px solid #d1d5db;border-radius:8px}
        button{padding:10px 14px;border-radius:8px;border:none;background:#111827;color:#fff;cursor:pointer}
        table{width:100%;border-collapse:collapse}
        th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
    </style>
</head>
<body>
<?php include('components/navbar.php'); ?>
<div class="container">
    <h1>Instructor Dashboard</h1>
    <div class="grid">
        <div class="card">
            <h2>Create Course</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_course" />
                <label>Title</label>
                <input name="title" required />
                <label>Slug</label>
                <input name="slug" required />
                <label>Description</label>
                <textarea name="description" rows="4" required></textarea>
                <label>Thumbnail URL</label>
                <input name="thumbnail_url" placeholder="https://images.unsplash.com/..." />
                <label>Price</label>
                <input name="price" type="number" step="0.01" value="0" />
                <label>Level</label>
                <select name="level">
                    <option value="beginner">beginner</option>
                    <option value="intermediate">intermediate</option>
                    <option value="advanced">advanced</option>
                </select>
                <button type="submit">Add Course</button>
            </form>
        </div>
        <div class="card">
            <h2>Add Lesson</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_lesson" />
                <label>Course</label>
                <select name="course_id">
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Title</label>
                <input name="title" required />
                <label>Video URL (YouTube embed)</label>
                <input name="video_url" placeholder="https://www.youtube.com/embed/..." />
                <label>Content</label>
                <textarea name="content" rows="3"></textarea>
                <label>Position</label>
                <input name="position" type="number" value="1" />
                <label>Duration (seconds)</label>
                <input name="duration_seconds" type="number" value="0" />
                <button type="submit">Add Lesson</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:24px">
        <h2>My Courses</h2>
        <table>
            <thead><tr><th>Title</th><th>Slug</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td><?= htmlspecialchars($c['slug']) ?></td>
                    <td>
                        <a href="course.php?id=<?= (int)$c['id'] ?>">View</a>
                        | <a href="course-player.php?course_id=<?= (int)$c['id'] ?>">Play</a>
                        | <form method="post" style="display:inline">
                            <input type="hidden" name="action" value="delete_course" />
                            <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>" />
                            <button type="submit" onclick="return confirm('Delete course?')">Delete</button>
                          </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
