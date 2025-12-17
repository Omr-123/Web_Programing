<?php
session_start();
require '../conn.php';

// Admin only
if (!isset($_SESSION['userId']) || $_SESSION['role'] != 2) {
    header('Location: ../index.php');
    exit();
}

/* Handle actions */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Add course
    if ($_POST['action'] == 'add_course') {
        $stmt = $conn->prepare("INSERT INTO courses (instructor_id, title, description, thumbnail_url, price, level, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param('isssds', $_POST['instructor_id'], $_POST['title'], $_POST['description'], $_POST['thumbnail_url'], $_POST['price'], $_POST['level']);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit();
    }

    // Add lesson
    if ($_POST['action'] == 'add_lesson') {
        $pos = $_POST['position'];
        if (!$pos) {
            $r = $conn->query("SELECT COALESCE(MAX(`order`),0)+1 p FROM lessons WHERE course_id = ".$_POST['course_id']);
            $pos = $r->fetch_assoc()['p'];
        }
        $stmt = $conn->prepare("INSERT INTO lessons (course_id, title, video_url, content, `order`, duration_seconds) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssii', $_POST['course_id'], $_POST['lesson_title'], $_POST['video_url'], $_POST['content'], $pos, $_POST['duration_seconds']);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit();
    }

    // Delete course
    if ($_POST['action'] == 'delete_course') {
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param('i', $_POST['course_id']);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit();
    }

    // Delete lesson
    if ($_POST['action'] == 'delete_lesson') {
        $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->bind_param('i', $_POST['lesson_id']);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit();
    }

    // Toggle admin
    if ($_POST['action'] == 'toggle_admin') {
        if ($_POST['user_id'] != $_SESSION['userId']) {
            $r = $conn->query("SELECT role_id FROM users WHERE id = ".$_POST['user_id']);
            $row = $r->fetch_assoc();
            $newRole = ($row['role_id'] == 3) ? 1 : 3;
            $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
            $stmt->bind_param('ii', $newRole, $_POST['user_id']);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: index.php');
        exit();
    }

    // Set role by email
    if ($_POST['action'] == 'set_role_by_email') {
        $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE email = ?");
        $stmt->bind_param('is', $_POST['role_id'], $_POST['email']);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit();
    }
}

/* Stats */
$statsUsers = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$statsCourses = $conn->query("SELECT COUNT(*) c FROM courses")->fetch_assoc()['c'];
$statsLessons = $conn->query("SELECT COUNT(*) c FROM lessons")->fetch_assoc()['c'];
$statsAdmins = $conn->query("SELECT COUNT(*) c FROM users WHERE role_id = 3")->fetch_assoc()['c'];

/* Courses */
$courses = $conn->query("SELECT c.id, c.title, c.price, c.created_at, u.fname, u.lname, u.email FROM courses c JOIN users u ON u.id = c.instructor_id ORDER BY c.created_at DESC")->fetch_all(MYSQLI_ASSOC);

/* Lessons */
$lessonsByCourse = [];
foreach ($courses as $c) {
    $lessonsByCourse[$c['id']] = $conn->query("SELECT id, title, duration_seconds, `order` as position FROM lessons WHERE course_id = ".$c['id']." ORDER BY `order`")->fetch_all(MYSQLI_ASSOC);
}

/* Admins */
$admins = $conn->query("SELECT id, fname, lname, email FROM users WHERE role_id = 3")->fetch_all(MYSQLI_ASSOC);

/* Instructors */
$instructors = $conn->query("SELECT id, fname, lname, email FROM users WHERE role_id IN (2,3)")->fetch_all(MYSQLI_ASSOC);
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
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="logo-area">
                <h2><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></h2>
                <span class="logo-text">Lerno Admin</span>
            </div>
            <ul class="side-nav">
                <li class="active">
                    <a href="index.php">
                        <svg class="admin-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="link-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="../courses.php">
                        <svg class="admin-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span class="link-text">Courses</span>
                    </a>
                </li>
                <li>
                    <a href="../index.php">
                        <svg class="admin-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="link-text">Home</span>
                    </a>
                </li>
                <li class="logout">
                    <a href="../logout.php">
                        <svg class="admin-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="link-text">Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <div class="page-name"><h3>Admin Dashboard</h3></div>
                <div class="user-profile">
                    <span><?= htmlspecialchars(isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin') ?></span>
                    <div class="avatar">A</div>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
                        <div class="stat-info"><h3>Total Users</h3><div class="number"><?= (int)$statsUsers ?></div></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg></div>
                        <div class="stat-info"><h3>Courses</h3><div class="number"><?= (int)$statsCourses ?></div></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                        <div class="stat-info"><h3>Lessons</h3><div class="number"><?= (int)$statsLessons ?></div></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><svg class="stat-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                        <div class="stat-info"><h3>Admins</h3><div class="number"><?= (int)$statsAdmins ?></div></div>
                    </div>
                </div>

                <div class="admin-row">
                    <div class="col-main">
                        <div class="admin-section">
                            <div class="section-header">
                                <h3>All Courses</h3>
                                <button class="btn btn-primary" id="show-add-course-btn">+ Add Course</button>
                            </div>
                            
                            <div id="add-course-form" class="form-panel hidden">
                                <h4 class="form-title">Add New Course</h4>
                                <form method="post" action="index.php">
                                    <input type="hidden" name="action" value="add_course" />
                                    <div class="form-group">
                                        <label class="form-label">Course Title*</label>
                                        <input type="text" class="form-control" name="title" placeholder="e.g., Advanced JavaScript" required />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Description*</label>
                                        <textarea class="form-control" name="description" rows="3" placeholder="Course description..." required></textarea>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Instructor*</label>
                                            <select class="form-control" name="instructor_id" required>
                                                <option value="">Select Instructor</option>
                                                <?php foreach ($instructors as $inst): ?>
                                                    <option value="<?= (int)$inst['id'] ?>"><?= htmlspecialchars($inst['fname'] . ' ' . $inst['lname']) ?> (<?= htmlspecialchars($inst['email']) ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Price ($)</label>
                                            <input type="number" class="form-control" name="price" step="0.01" min="0" value="0" />
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Level</label>
                                            <select class="form-control" name="level">
                                                <option value="beginner">Beginner</option>
                                                <option value="intermediate">Intermediate</option>
                                                <option value="advanced">Advanced</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Thumbnail URL</label>
                                            <input type="text" class="form-control" name="thumbnail_url" placeholder="https://example.com/image.jpg" />
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Create Course</button>
                                        <button type="button" class="btn btn-outline" id="cancel-add-course-btn">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Instructor</th>
                                            <th>Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="courses-table-body">
                                        <?php foreach ($courses as $c): ?>
                                        <tr data-course-id="<?= (int)$c['id'] ?>">
                                            <td>#<?= (int)$c['id'] ?></td>
                                            <td><?= htmlspecialchars($c['title']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($c['fname'] . ' ' . $c['lname']) ?>
                                                <div class="text-muted"><?= htmlspecialchars($c['email']) ?></div>
                                            </td>
                                            <td>$<?= number_format((float)$c['price'], 2) ?></td>
                                            <td class="table-actions">
                                                <button class="btn btn-outline btn-sm toggle-lessons">Lessons</button>
                                                <button class="btn btn-danger btn-sm delete-course">Delete</button>
                                            </td>
                                        </tr>
                                        <tr class="lessons-row hidden" data-course-id="<?= (int)$c['id'] ?>">
                                            <td colspan="5" class="lessons-cell">
                                                <?php $cid = (int)$c['id']; $less = isset($lessonsByCourse[$cid]) ? $lessonsByCourse[$cid] : array(); ?>
                                                
                                                <div class="lessons-panel">
                                                    <div class="panel-header">
                                                        <h4>Course Lessons</h4>
                                                        <button class="btn btn-outline btn-sm show-add-lesson-btn" data-course-id="<?= $cid ?>">+ Add Lesson</button>
                                                    </div>

                                                    <form method="post" action="index.php" class="add-lesson-form hidden" data-course-id="<?= $cid ?>">
                                                        <input type="hidden" name="action" value="add_lesson" />
                                                        <input type="hidden" name="course_id" value="<?= $cid ?>" />
                                                        
                                                        <div class="form-group">
                                                            <label class="form-label-sm">Lesson Title*</label>
                                                            <input type="text" class="form-control form-control-sm" name="lesson_title" required />
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label-sm">Video URL</label>
                                                            <input type="text" class="form-control form-control-sm" name="video_url" placeholder="https://..." />
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label-sm">Content</label>
                                                            <textarea class="form-control form-control-sm" name="content" rows="2"></textarea>
                                                        </div>
                                                        <div class="form-row">
                                                            <div class="form-group">
                                                                <label class="form-label-sm">Duration (sec)</label>
                                                                <input type="number" class="form-control form-control-sm" name="duration_seconds" min="0" value="0" />
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label-sm">Position</label>
                                                                <input type="number" class="form-control form-control-sm" name="position" min="0" value="0" />
                                                            </div>
                                                        </div>
                                                        <div class="form-actions">
                                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                            <button type="button" class="btn btn-outline btn-sm cancel-add-lesson-btn">Cancel</button>
                                                        </div>
                                                    </form>
                                                    
                                                    <div class="lesson-list">
                                                        <?php if (!$less): ?>
                                                            <div class="text-muted">No lessons yet.</div>
                                                        <?php else: ?>
                                                            <?php foreach ($less as $l): ?>
                                                            <div class="lesson-item" data-lesson-id="<?= (int)$l['id'] ?>">
                                                                <div class="lesson-info">
                                                                    <strong><?= (int)$l['position'] ?>.</strong>
                                                                    <?= htmlspecialchars($l['title']) ?>
                                                                    <span class="text-muted">(<?= (int)$l['duration_seconds'] ? gmdate('i:s', (int)$l['duration_seconds']) : '0:00' ?>)</span>
                                                                </div>
                                                                <button class="btn btn-danger btn-sm delete-lesson">Delete</button>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-side">
                        <div class="admin-section">
                            <h3 class="section-title">Admin Accounts</h3>
                            <div class="user-list">
                                <?php if (!$admins): ?>
                                    <div class="text-muted">No admins found.</div>
                                <?php else: ?>
                                    <?php foreach ($admins as $u): ?>
                                    <div class="user-item">
                                        <div class="user-info">
                                            <strong><?= htmlspecialchars($u['fname'] . ' ' . $u['lname']) ?></strong>
                                            <div class="text-muted"><?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                        <button class="btn btn-outline btn-sm toggle-admin" data-user-id="<?= (int)$u['id'] ?>">Remove</button>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="admin-section">
                            <h3 class="section-title">Manage User Role</h3>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="set_role_by_email" />
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="user@example.com" required />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Role</label>
                                    <select class="form-control" name="role_id" required>
                                        <option value="1">Student</option>
                                        <option value="2">Instructor</option>
                                        <option value="3">Admin</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Update Role</button>
                            </form>
                            <div class="text-muted small-note">Note: You cannot demote your own admin role.</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>