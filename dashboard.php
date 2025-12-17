<?php
session_start();
require 'conn.php';

// Detect AJAX early so we can return JSON errors instead of redirects
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

/* allow instructors only */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
        exit();
    }
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

/* handle actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

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
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($affected > 0) {
                echo json_encode(['ok' => true]);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Course not found or not owned by you']);
            }
            exit();
        }
    }

    /* delete lesson */
    if ($_POST['action'] === 'delete_lesson') {

        $lesson_id = $_POST['lesson_id'];

        $stmt = $conn->prepare("DELETE l FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id = ? AND c.instructor_id = ?");
        $stmt->bind_param('ii', $lesson_id, $userId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($affected > 0) {
                echo json_encode(['ok' => true]);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Lesson not found or not owned by you']);
            }
            exit();
        }
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
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<?php include('components/navbar.php'); ?>

<div class="container">
    <h1 class="page-title">Instructor Dashboard</h1>
    
    <div class="grid four-col">
        
        <div class="card">
            <h2>Update Profile Image</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_profile_image" />
                <?php if (!empty($current_profile_image)): ?>
                <div class="mb-2">
                    <p class="muted-caption">Current:</p>
                    <img src="<?= htmlspecialchars($current_profile_image) ?>" alt="Profile" class="profile-preview">
                </div>
                <?php endif; ?>
                <label>Profile Image URL</label>
                <input type="url" name="avatar" placeholder="https://example.com/image.jpg" value="<?= htmlspecialchars($current_profile_image ?? '') ?>" required />
                <button type="submit" class="btn btn-primary">Update Image</button>
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
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>

        <div class="card">
            <h2>Create Course</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_course" />
                <label>Title</label>
                <input type="text" name="title" required />
                <label>Description</label>
                <textarea name="description" rows="4" required></textarea>
                <label>Thumbnail URL</label>
                <input type="text" name="thumbnail_url" placeholder="https://images.unsplash.com/..." />
                <div class="flex-row">
                    <div class="flex-1">
                        <label>Price</label>
                        <input type="number" name="price" step="0.01" value="0" />
                    </div>
                    <div class="flex-1">
                        <label>Level</label>
                        <select name="level">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Course</button>
            </form>
        </div>

        <div class="card">
            <h2>Add Lesson</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_lesson" />
                <label>Select Course</label>
                <select name="course_id">
                    <?php if(!empty($courses)): foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <label>Lesson Title</label>
                <input type="text" name="title" required />
                <label>Video URL (YouTube)</label>
                <input type="text" name="video_url" placeholder="https://www.youtube.com/embed/..." />
                <label>Content</label>
                <textarea name="content" rows="2"></textarea>
                <div class="flex-row">
                    <div class="flex-1">
                        <label>Position</label>
                        <input type="number" name="position" value="1" />
                    </div>
                    <div class="flex-1">
                        <label>Duration (s)</label>
                        <input type="number" name="duration_seconds" value="0" />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Lesson</button>
            </form>
        </div>
    </div>

    <div class="card mt-4 card--no-padding">
        <h2 class="card-title">My Courses</h2>
        <div class="table-wrapper table-wrapper--flat">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): foreach ($courses as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['title']) ?></strong></td>
                        <td class="text-right nowrap">
                            <a href="course.php?id=<?= $c['id'] ?>" class="action-link">View</a>
                            <a href="course-player.php?course_id=<?= $c['id'] ?>" class="action-link">Play</a>
                            <button type="button" class="btn btn-sm btn-outline toggle-lessons-btn" data-course-id="<?= $c['id'] ?>">
                                Lessons
                            </button>
                            <form method="post" class="inline-form" style="display: inline;">
                                <input type="hidden" name="action" value="delete_course" />
                                <input type="hidden" name="course_id" value="<?= $c['id'] ?>" />
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="lessons-row" data-course-id="<?= $c['id'] ?>">
                        <td colspan="3">
                            <h3 class="lessons-header">Lessons: <?= htmlspecialchars($c['title']) ?></h3>
                            <?php 
                            $cid = $c['id'];
                            $lessons = $lessonsByCourse[$cid] ?? [];
                            ?>
                            <?php if (empty($lessons)): ?>
                                <p class="muted-italic">No lessons added yet.</p>
                            <?php else: ?>
                                <div class="table-wrapper">
                                    <table class="nested-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Duration</th>
                                                <th>Video URL</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lessons as $lesson): ?>
                                            <tr>
                                                <td><?= $lesson['position'] ?></td>
                                                <td><?= htmlspecialchars($lesson['title']) ?></td>
                                                <td><?= $lesson['duration_seconds'] ?>s</td>
                                                <td class="muted-ellipsis">
                                                    <?= htmlspecialchars($lesson['video_url'] ?: 'N/A') ?>
                                                </td>
                                                <td class="text-right">
                                                    <form method="post" class="inline-form-inline">
                                                        <input type="hidden" name="action" value="delete_lesson" />
                                                        <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>" />
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this lesson?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="3" class="empty-message">No courses found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Toggle lessons visibility with simple animation logic
document.querySelectorAll('.toggle-lessons-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const courseId = btn.getAttribute('data-course-id');
        const lessonsRow = document.querySelector(`.lessons-row[data-course-id="${courseId}"]`);
        
        if (lessonsRow) {
            const visible = lessonsRow.classList.toggle('visible');
            if (visible) {
                btn.textContent = 'Hide';
                btn.style.backgroundColor = '#02413b';
                btn.style.color = '#fff';
            } else {
                btn.textContent = 'Lessons';
                btn.style.backgroundColor = 'transparent';
                btn.style.color = '#02413b';
            }
        }
    });
});
</script>

</body>
</html>