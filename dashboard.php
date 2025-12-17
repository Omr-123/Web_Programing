<?php
session_start();
require 'conn.php';

/* allow instructors only */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

/* handle actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    /* create course */
    if ($_POST['action'] === 'create_course') {

        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $thumbnail_url = trim($_POST['thumbnail_url']);
        $price = $_POST['price'];
        $level = $_POST['level'];

        if ($title) {
            $stmt = $conn->prepare("INSERT INTO courses (instructor_id, title, description, thumbnail_url, price, level, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param('isssds', $userId, $title, $description, $thumbnail_url, $price, $level);
            $stmt->execute();
            $stmt->close();
        }
    }

    /* create lesson */
    if ($_POST['action'] === 'create_lesson') {

        $course_id = $_POST['course_id'];
        $title = trim($_POST['title']);
        $video_url = trim($_POST['video_url']);
        $content = trim($_POST['content']);
        $order = $_POST['position'];
        $duration = $_POST['duration_seconds'];

        if ($course_id && $title) {
            $stmt = $conn->prepare("INSERT INTO lessons (course_id, title, video_url, content, `order`, duration_seconds) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssii', $course_id, $title, $video_url, $content, $order, $duration);
            $stmt->execute();
            $stmt->close();
        }
    }

    /* delete course */
    if ($_POST['action'] === 'delete_course') {

        $course_id = $_POST['course_id'];

        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND instructor_id = ?");
        $stmt->bind_param('ii', $course_id, $userId);
        $stmt->execute();
        $stmt->close();
    }

    /* delete lesson */
    if ($_POST['action'] === 'delete_lesson') {

        $lesson_id = $_POST['lesson_id'];

        $stmt = $conn->prepare("DELETE l FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id = ? AND c.instructor_id = ?");
        $stmt->bind_param('ii', $lesson_id, $userId);
        $stmt->execute();
        $stmt->close();
    }

    /* update profile image */
    if ($_POST['action'] === 'update_profile_image') {

        $avatar = trim($_POST['avatar']);

        if ($avatar) {
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->bind_param('si', $avatar, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    /* change password */
    if ($_POST['action'] === 'change_instructor_password') {

        $email = trim($_POST['email']);
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];

        if ($email && $current && $new) {

            $stmt = $conn->prepare("SELECT password FROM users WHERE email = ? AND id = ?");
            $stmt->bind_param('si', $email, $userId);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                if (password_verify($current, $row['password'])) {
                    $hash = password_hash($new, PASSWORD_BCRYPT);
                    $up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $up->bind_param('si', $hash, $userId);
                    $up->execute();
                    $up->close();
                }
            }

            $stmt->close();
        }
    }
}

/* fetch current avatar */
$stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$current_profile_image = $row['avatar'];
$stmt->close();

/* fetch courses */
$stmt = $conn->prepare("SELECT id, title FROM courses WHERE instructor_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();

/* fetch lessons */
$lessonsByCourse = [];
while ($c = $courses->fetch_assoc()) {

    $cid = $c['id'];

    $stmt = $conn->prepare("SELECT id, title, video_url, `order` AS position, duration_seconds FROM lessons WHERE course_id = ? ORDER BY `order`");
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($l = $res->fetch_assoc()) {
        $lessonsByCourse[$cid][] = $l;
    }

    $stmt->close();
}
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
        .grid.three-col{grid-template-columns:1fr 1fr 1fr}
        .grid.four-col{grid-template-columns:1fr 1fr 1fr 1fr}
        .card{border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff}
        input,textarea,select{width:100%;padding:10px;margin:6px 0;border:1px solid #d1d5db;border-radius:8px;box-sizing:border-box}
        button{padding:10px 14px;border-radius:8px;border:none;background:#111827;color:#fff;cursor:pointer}
        table{width:100%;border-collapse:collapse}
        th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
    </style>
</head>
<body>
<?php include('components/navbar.php'); ?>
<div class="container">
    <h1>Instructor Dashboard</h1>
    <div class="grid four-col">
        <div class="card">
            <h2>Update Profile Image</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_profile_image" />
                <?php if ($current_profile_image): ?>
                <div style="margin-bottom: 12px;">
                    <p style="font-size: 14px; color: #666;">Current Profile Image:</p>
                    <img src="<?= htmlspecialchars($current_profile_image) ?>" alt="Profile" style="max-width: 150px; border-radius: 8px;">
                </div>
                <?php endif; ?>
                <label>Profile Image URL</label>
                <input type="url" name="avatar" placeholder="https://example.com/image.jpg" value="<?= htmlspecialchars($current_profile_image ?? '') ?>" required />
                <button type="submit">Update Image</button>
            </form>
        </div>

        <div class="card">
            <h2>Change Password</h2>
            <form method="post">
                <input type="hidden" name="action" value="change_instructor_password" />
                <label>Email</label>
                <input type="email" name="email" placeholder="Your email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required />
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Current password" required />
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="New password (min 6 chars)" required />
                <button type="submit">Change Password</button>
            </form>
        </div>
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
                        | <button type="button" class="toggle-lessons-btn" data-course-id="<?= (int)$c['id'] ?>" style="background:#3b82f6;padding:4px 8px;font-size:13px;">Show Lessons</button>
                        | <form method="post" style="display:inline">
                            <input type="hidden" name="action" value="delete_course" />
                            <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>" />
                            <button type="submit" onclick="return confirm('Delete course?')" style="background:#dc2626;padding:4px 8px;font-size:13px;">Delete</button>
                          </form>
                    </td>
                </tr>
                <tr class="lessons-row" data-course-id="<?= (int)$c['id'] ?>" style="display:none;">
                    <td colspan="3" style="background:#f9fafb;padding:16px;">
                        <h3 style="margin-bottom:12px;font-size:16px;">Lessons for: <?= htmlspecialchars($c['title']) ?></h3>
                        <?php 
                        $cid = (int)$c['id'];
                        $lessons = $lessonsByCourse[$cid] ?? [];
                        ?>
                        <?php if (empty($lessons)): ?>
                            <p style="color:#6b7280;font-style:italic;">No lessons added yet.</p>
                        <?php else: ?>
                            <table style="width:100%;background:#fff;">
                                <thead>
                                    <tr style="background:#f3f4f6;">
                                        <th style="padding:8px;">Position</th>
                                        <th style="padding:8px;">Title</th>
                                        <th style="padding:8px;">Duration</th>
                                        <th style="padding:8px;">Video URL</th>
                                        <th style="padding:8px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lessons as $lesson): ?>
                                    <tr>
                                        <td style="padding:8px;"><?= (int)$lesson['position'] ?></td>
                                        <td style="padding:8px;"><?= htmlspecialchars($lesson['title']) ?></td>
                                        <td style="padding:8px;"><?= (int)$lesson['duration_seconds'] ?> sec</td>
                                        <td style="padding:8px;font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?= htmlspecialchars($lesson['video_url'] ?: 'N/A') ?>
                                        </td>
                                        <td style="padding:8px;">
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_lesson" />
                                                <input type="hidden" name="lesson_id" value="<?= (int)$lesson['id'] ?>" />
                                                <button type="submit" onclick="return confirm('Delete this lesson?')" style="background:#dc2626;padding:4px 8px;font-size:12px;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
// Toggle lessons visibility
document.querySelectorAll('.toggle-lessons-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const courseId = btn.getAttribute('data-course-id');
        const lessonsRow = document.querySelector(`.lessons-row[data-course-id="${courseId}"]`);
        if (lessonsRow) {
            if (lessonsRow.style.display === 'none') {
                lessonsRow.style.display = '';
                btn.textContent = 'Hide Lessons';
                btn.style.background = '#6b7280';
            } else {
                lessonsRow.style.display = 'none';
                btn.textContent = 'Show Lessons';
                btn.style.background = '#3b82f6';
            }
        }
    });
});
</script>
</body>
</html>
