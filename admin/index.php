<?php
session_start();
require '../conn.php';

/* admin only */
if (!isset($_SESSION['userId']) || (int) ($_SESSION['role'] ?? 0) !== 3) {
    header('Location: ../index.php');
    exit();
}

header_remove('X-Powered-By');

/* helpers */
function is_ajax_request()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/* actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    /* add course */
    if ($_POST['action'] === 'add_course') {

        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $instructorId = (int) $_POST['instructor_id'];
        $price = (float) $_POST['price'];
        $level = $_POST['level'];
        $language = $_POST['language'];
        $thumbnail = trim($_POST['thumbnail_url']);

        if ($title && $description && $instructorId > 0) {

            $stmt = $conn->prepare(
                "INSERT INTO courses (instructor_id, title, description, thumbnail_url, language, level, price, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $stmt->bind_param(
                'isssssd',
                $instructorId,
                $title,
                $description,
                $thumbnail,
                $language,
                $level,
                $price
            );
            $ok = $stmt->execute();
            $courseId = $conn->insert_id;
            $stmt->close();

            if (is_ajax_request()) {
                json_response(['ok' => $ok, 'course_id' => $courseId]);
            }

            header('Location: index.php?added=course');
            exit();
        }

        json_response(['ok' => false], 400);
    }

    /* add lesson */
    if ($_POST['action'] === 'add_lesson') {

        $courseId = (int) $_POST['course_id'];
        $title = trim($_POST['lesson_title']);
        $video = trim($_POST['video_url']);
        $content = trim($_POST['content']);
        $duration = (int) $_POST['duration_seconds'];
        $order = (int) $_POST['position'];

        if ($courseId && $title) {

            if ($order === 0) {
                $pos = $conn->prepare(
                    "SELECT COALESCE(MAX(`order`),0)+1 AS next_pos FROM lessons WHERE course_id = ?"
                );
                $pos->bind_param('i', $courseId);
                $pos->execute();
                $row = $pos->get_result()->fetch_assoc();
                $order = (int) $row['next_pos'];
                $pos->close();
            }

            $stmt = $conn->prepare(
                "INSERT INTO lessons (course_id, title, video_url, content, duration_seconds, `order`)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'isssii',
                $courseId,
                $title,
                $video,
                $content,
                $duration,
                $order
            );
            $stmt->execute();
            $stmt->close();

            header('Location: index.php?added=lesson');
            exit();
        }
    }

    /* delete course */
    if ($_POST['action'] === 'delete_course') {

        $id = (int) $_POST['course_id'];
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        exit();
    }

    /* delete lesson */
    if ($_POST['action'] === 'delete_lesson') {

        $id = (int) $_POST['lesson_id'];
        $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        exit();
    }

    /* set role by email */
    if ($_POST['action'] === 'set_role_by_email') {
        $email = trim($_POST['email'] ?? '');
        $role_id = (int) ($_POST['role_id'] ?? 0);
        $allowed = [1,2,3];

        if (!$email || !in_array($role_id, $allowed, true)) {
            if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Invalid input'], 400);
            header('Location: index.php?role_error=invalid');
            exit();
        }

        // find user
        $stmt = $conn->prepare("SELECT id, role_id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$user) {
            if (is_ajax_request()) json_response(['ok' => false, 'error' => 'User not found'], 404);
            header('Location: index.php?role_error=notfound');
            exit();
        }

        $targetUserId = (int) $user['id'];
        $currentRole = (int) $user['role_id'];
        $myId = (int) $_SESSION['userId'];
        $myRole = (int) ($_SESSION['role'] ?? 0);

        // Prevent demoting your own admin role
        if ($myId === $targetUserId && $myRole === 3 && $role_id !== 3) {
            if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Cannot demote yourself'], 403);
            header('Location: index.php?role_error=cannot_demote_self');
            exit();
        }

        // perform update
        $up = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $up->bind_param('ii', $role_id, $targetUserId);
        $ok = $up->execute();
        $up->close();

        if (is_ajax_request()) {
            json_response(['ok' => (bool)$ok, 'role_id' => $role_id]);
        }

        header('Location: index.php?role_updated=' . ($ok ? '1' : '0'));
        exit();
    }

    /* toggle admin role (AJAX-friendly) */
    if ($_POST['action'] === 'toggle_admin') {
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        if ($targetUserId <= 0) {
            if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Invalid user id'], 400);
            header('Location: index.php?role_error=invalid');
            exit();
        }

        $myId = (int) ($_SESSION['userId'] ?? 0);
        $myRole = (int) ($_SESSION['role'] ?? 0);

        // Prevent changing your own admin status
        if ($myId === $targetUserId) {
            if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Cannot change your own admin status'], 403);
            header('Location: index.php?role_error=cannot_change_self');
            exit();
        }

        // Get current role of target
        $stmt = $conn->prepare("SELECT role_id FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $targetUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$user) {
            if (is_ajax_request()) json_response(['ok' => false, 'error' => 'User not found'], 404);
            header('Location: index.php?role_error=notfound');
            exit();
        }

        $newRole = 1;
        $up = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $up->bind_param('ii', $newRole, $targetUserId);
        $ok = $up->execute();
        $up->close();

        if (is_ajax_request()) {
            json_response(['ok' => (bool)$ok, 'role_id' => $newRole]);
        }

        header('Location: index.php');
        exit();
    }
}

/* stats */
$stats = ['users' => 0, 'courses' => 0, 'lessons' => 0, 'instructors' => 0];

$q = $conn->query("SELECT COUNT(*) c FROM users");
$stats['users'] = (int) $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) c FROM courses");
$stats['courses'] = (int) $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) c FROM lessons");
$stats['lessons'] = (int) $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) c FROM users WHERE role_id = 2");
$stats['instructors'] = (int) $q->fetch_assoc()['c'];

/* courses */
$courses = [];
$stmt = $conn->prepare(
    "SELECT c.id, c.title, c.price, c.created_at, u.fname, u.lname, u.email FROM courses c JOIN users u ON u.id = c.instructor_id ORDER BY c.created_at DESC"
);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    array_push($courses, $row);
}
$stmt->close();

/* lessons */
$lessonsByCourse = [];
foreach ($courses as $c) {

    $cid = (int) $c['id'];
    $stmt = $conn->prepare(
        "SELECT id, title, duration_seconds, `order` AS position
         FROM lessons WHERE course_id = ? ORDER BY `order`"
    );
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($l = $res->fetch_assoc()) {
        if (!isset($lessonsByCourse[$cid])) {
            $lessonsByCourse[$cid] = [];
        }
        array_push($lessonsByCourse[$cid], $l);
    }
    $stmt->close();
}

/* admins (exclude current admin from list) */
$admins = [];
$myId = (int) ($_SESSION['userId'] ?? 0);
$stmt = $conn->prepare("SELECT id, fname, lname, email FROM users WHERE role_id = 3 AND id != ? ORDER BY joined_at DESC");
$stmt->bind_param('i', $myId);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    array_push($admins, $r);
}
$stmt->close();

/* instructors */
$instructors = [];
$res = $conn->query(
    "SELECT id, fname, lname, email FROM users WHERE role_id IN (2,3) ORDER BY fname, lname"
);
while ($r = $res->fetch_assoc()) {
    array_push($instructors, $r);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="../assets/Lerno.png">
    <link rel="stylesheet" href="../assets/css/reset.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/admin.js" defer></script>
    <style>
        /* Ensure public site navbar is hidden on admin pages */
        .header {
            display: none !important;
        }

        /* Remove top padding added by public layout */
        body {
            padding-top: 0 !important;
        }

        .section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .flex {
            display: flex;
            gap: 20px;
        }

        .w-66 {
            flex: 2;
        }

        .w-33 {
            flex: 1;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .btn-outline {
            background: #fff;
            border: 1px solid #ccc;
        }

        .role-form input,
        .role-form select {
            padding: 8px;
            width: 100%;
            margin-bottom: 8px;
        }

        .lesson-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .lesson-meta {
            font-size: 12px;
            color: #666;
        }

        .muted {
            color: #666;
            font-size: 12px;
        }

        .table-actions {
            display: flex;
            gap: 8px;
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <!-- <aside class="sidebar">
            <div class="logo-area">
                <h2>⚙️</h2>
                <span class="logo-text">Lerno Admin</span>
            </div>
            <ul class="side-nav">
                <li class="active"><a href="index.php"><img class="admin-icon" src="https://img.icons8.com/ios-filled/50/ffffff/dashboard.png" alt="" /><span class="link-text">Dashboard</span></a></li>
                <li><a href="../courses.php"><img class="admin-icon" src="https://img.icons8.com/ios-glyphs/30/ffffff/book.png" alt="" /><span class="link-text">Courses</span></a></li>
                <li><a href="../index.php"><img class="admin-icon" src="https://img.icons8.com/ios-glyphs/30/ffffff/home.png" alt="" /><span class="link-text">Home</span></a></li>
                <li class="logout"><a href="../logout.php"><img class="admin-icon" src="https://img.icons8.com/ios-glyphs/30/ffffff/exit.png" alt="" /><span class="link-text">Logout</span></a></li>
            </ul>
        </aside> -->

        <main class="main-content">
            <div class="top-bar">
                <div class="page-name">
                    <h3>Admin Dashboard</h3>
                </div>
                <div class="user-profile">
                    <a href="../" class="nav-link">Home</a>
                    <span><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></span>
                    <div class="avatar">A</div>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M3 13h6v8H3zM9 3h6v18H9zM15 8h6v13h-6z" />
                            </svg></div>
                        <div class="stat-info">
                            <h3>Total Users</h3>
                            <div class="number"><?= (int) $stats['users'] ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 3l7 4v10l-7 4l-7-4V7z" />
                            </svg></div>
                        <div class="stat-info">
                            <h3>Courses</h3>
                            <div class="number"><?= (int) $stats['courses'] ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z" />
                            </svg></div>
                        <div class="stat-info">
                            <h3>Lessons</h3>
                            <div class="number"><?= (int) $stats['lessons'] ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 17l-5 3l1.9-5.9L4 9h6l2-6l2 6h6l-4.9 5.1L17 20z" />
                            </svg></div>
                        <div class="stat-info">
                            <h3>Instructors</h3>
                            <div class="number"><?= (int) $stats['instructors'] ?></div>
                        </div>
                    </div>
                </div>

                <div class="flex">
                    <div class="w-66">
                        <div class="section">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <h3>All Courses</h3>
                                <button class="btn" style="background:#02413b;color:#fff;" id="show-add-course-btn">+ Add Course</button>
                            </div>

                            <!-- Add Course Form -->
                            <div id="add-course-form" style="display:none;background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h4 style="margin-bottom:12px;">Add New Course</h4>
                                <form method="post" action="index.php">
                                    <input type="hidden" name="action" value="add_course" />
                                    <div style="margin-bottom:10px;">
                                        <label style="display:block;margin-bottom:4px;font-weight:bold;">Course Title*</label>
                                        <input type="text" name="title" placeholder="e.g., Advanced JavaScript" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" />
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <label style="display:block;margin-bottom:4px;font-weight:bold;">Description*</label>
                                        <textarea name="description" rows="3" placeholder="Course description..." required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"></textarea>
                                    </div>
                                    <div style="display:flex;gap:10px;margin-bottom:10px;">
                                        <div style="flex:1;">
                                            <label style="display:block;margin-bottom:4px;font-weight:bold;">Instructor*</label>
                                            <select name="instructor_id" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                                                <option value="">Select Instructor</option>
                                                <?php foreach ($instructors as $inst): ?>
                                                    <option value="<?= (int) $inst['id'] ?>"><?= htmlspecialchars($inst['fname'] . ' ' . $inst['lname']) ?> (<?= htmlspecialchars($inst['email']) ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div style="flex:1;">
                                            <label style="display:block;margin-bottom:4px;font-weight:bold;">Price ($)</label>
                                            <input type="number" name="price" step="0.01" min="0" value="0" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" />
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:10px;margin-bottom:10px;">
                                        <div style="flex:1;">
                                            <label style="display:block;margin-bottom:4px;font-weight:bold;">Level</label>
                                            <select name="level" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                                                <option value="beginner">Beginner</option>
                                                <option value="intermediate">Intermediate</option>
                                                <option value="advanced">Advanced</option>
                                            </select>
                                        </div>
                                        <div style="flex:1;">
                                            <label style="display:block;margin-bottom:4px;font-weight:bold;">Language</label>
                                            <input type="text" name="language" value="en" placeholder="en, ar, etc." style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" />
                                        </div>
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <label style="display:block;margin-bottom:4px;font-weight:bold;">Thumbnail URL</label>
                                        <input type="text" name="thumbnail_url" placeholder="https://example.com/image.jpg" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" />
                                    </div>
                                    <div style="display:flex;gap:10px;">
                                        <button type="submit" class="btn" style="background:#02413b;color:#fff;">Create Course</button>
                                        <button type="button" class="btn btn-outline" id="cancel-add-course-btn">Cancel</button>
                                    </div>
                                </form>
                            </div>

                            <div class="recent-orders">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Instructor</th>
                                            <th>Price</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="courses-table-body">
                                        <?php foreach ($courses as $c): ?>
                                            <tr data-course-id="<?= (int) $c['id'] ?>">
                                                <td>#<?= (int) $c['id'] ?></td>
                                                <td><?= htmlspecialchars($c['fname'] . ' ' . $c['lname']) ?>
                                                    <div class="muted"><?= htmlspecialchars($c['email']) ?></div>
                                                </td>
                                                <td>$<?= number_format((float) $c['price'], 2) ?></td>
                                                <td><?= htmlspecialchars($c['created_at']) ?></td>
                                                <td class="table-actions">
                                                    <button class="btn btn-outline toggle-lessons">Lessons</button>
                                                    <button class="btn btn-danger delete-course">Delete</button>
                                                </td>
                                            </tr>
                                            <tr class="lessons-row" data-course-id="<?= (int) $c['id'] ?>" style="display:none;background:#fafafa;">
                                                <td colspan="6">
                                                    <?php $cid = (int) $c['id'];
                                                    $less = $lessonsByCourse[$cid] ?? []; ?>

                                                    <!-- Add Lesson Form -->
                                                    <div style="background:#fff;padding:12px;border-radius:6px;margin-bottom:12px;border:1px solid #ddd;">
                                                        <button class="btn btn-outline show-add-lesson-btn" data-course-id="<?= $cid ?>" style="width:100%;">+ Add Lesson to This Course</button>
                                                        <form method="post" action="index.php" class="add-lesson-form" data-course-id="<?= $cid ?>" style="display:none;margin-top:12px;">
                                                            <input type="hidden" name="action" value="add_lesson" />
                                                            <input type="hidden" name="course_id" value="<?= $cid ?>" />
                                                            <div style="margin-bottom:8px;">
                                                                <label style="display:block;margin-bottom:4px;font-weight:bold;font-size:13px;">Lesson Title*</label>
                                                                <input type="text" name="lesson_title" placeholder="e.g., Introduction to Variables" required style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:13px;" />
                                                            </div>
                                                            <div style="margin-bottom:8px;">
                                                                <label style="display:block;margin-bottom:4px;font-weight:bold;font-size:13px;">Video URL</label>
                                                                <input type="text" name="video_url" placeholder="https://www.youtube.com/embed/..." style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:13px;" />
                                                            </div>
                                                            <div style="margin-bottom:8px;">
                                                                <label style="display:block;margin-bottom:4px;font-weight:bold;font-size:13px;">Content</label>
                                                                <textarea name="content" rows="2" placeholder="Lesson description or text content..." style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:13px;"></textarea>
                                                            </div>
                                                            <div style="display:flex;gap:8px;margin-bottom:8px;">
                                                                <div style="flex:1;">
                                                                    <label style="display:block;margin-bottom:4px;font-weight:bold;font-size:13px;">Duration (seconds)</label>
                                                                    <input type="number" name="duration_seconds" min="0" value="0" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:13px;" />
                                                                </div>
                                                                <div style="flex:1;">
                                                                    <label style="display:block;margin-bottom:4px;font-weight:bold;font-size:13px;">Position (0=auto)</label>
                                                                    <input type="number" name="position" min="0" value="0" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:13px;" />
                                                                </div>
                                                            </div>
                                                            <div style="margin-bottom:8px;">
                                                                <label style="display:flex;align-items:center;font-size:13px;">
                                                                    <input type="checkbox" name="is_preview" value="1" style="margin-right:6px;" />
                                                                    <span>Free Preview Lesson</span>
                                                                </label>
                                                            </div>
                                                            <div style="display:flex;gap:8px;">
                                                                <button type="submit" class="btn" style="background:#02413b;color:#fff;font-size:13px;">Add Lesson</button>
                                                                <button type="button" class="btn btn-outline cancel-add-lesson-btn" style="font-size:13px;">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <?php if (!$less): ?>
                                                        <div class="muted">No lessons in this course.</div>
                                                    <?php else: ?>
                                                        <?php foreach ($less as $l): ?>
                                                            <div class="lesson-row" data-lesson-id="<?= (int) $l['id'] ?>">
                                                                <div>
                                                                    <strong><?= (int) $l['position'] ?>.</strong>
                                                                    <?= htmlspecialchars($l['title']) ?>
                                                                    <span class="lesson-meta">(<?= (int) $l['duration_seconds'] ? gmdate('i:s', (int) $l['duration_seconds']) : '—' ?>)</span>
                                                                </div>
                                                                <div>
                                                                    <button class="btn btn-danger delete-lesson">Delete Lesson</button>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="w-33">
                        <div class="section">
                            <h3 style="margin-bottom:12px;">Admin Accounts</h3>
                            <div id="admins-list">
                                <?php if (!$admins): ?>
                                    <div class="muted">No admins found.</div>
                                <?php else: ?>
                                    <?php foreach ($admins as $u): ?>
                                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0f0f0;" data-user-id="<?= (int) $u['id'] ?>">
                                            <div>
                                                <div><strong><?= htmlspecialchars($u['fname'] . ' ' . $u['lname']) ?></strong></div>
                                                <div class="muted"><?= htmlspecialchars($u['email']) ?></div>
                                            </div>
                                            <div>
                                                <button class="btn btn-outline toggle-admin" data-current-role="3">Remove Admin</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="section">
                            <h3 style="margin-bottom:12px;">Set Role by Email</h3>
                            <form class="role-form" id="role-form" method="post" action="index.php">
                                <input type="hidden" name="action" value="set_role_by_email" />
                                <label>Email</label>
                                <input type="email" name="email" placeholder="user@example.com" required />
                                <label>Role</label>
                                <select name="role_id" required>
                                    <option value="1">Student</option>
                                    <option value="2">Instructor</option>
                                    <option value="3">Admin</option>
                                </select>
                                <button type="submit" class="btn" style="background:#02413b;color:#fff;width:100%;">Update Role</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</body>

</html>