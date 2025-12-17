<?php
session_start();
require __DIR__ . '/../conn.php';

// Access control: only admins (role_id = 3)
if (!isset($_SESSION['userId']) || (isset($_SESSION['role']) ? (int)$_SESSION['role'] : 0) !== 3) {
	header('Location: ../index.php');
	exit();
} 

header_remove('X-Powered-By');

// Helpers
function is_ajax_request(): bool {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response($data, int $status = 200): void {
	http_response_code($status);
	header('Content-Type: application/json');
	echo json_encode($data);
	exit();
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	$action = $_POST['action'];
	$adminId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;

	if ($action === 'add_course') {
		$title = isset($_POST['title']) ? trim($_POST['title']) : '';
		$description = isset($_POST['description']) ? trim($_POST['description']) : '';
		$instructorId = isset($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : 0;
		$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
		$level = isset($_POST['level']) ? $_POST['level'] : 'beginner';
		$language = isset($_POST['language']) ? $_POST['language'] : 'en';
		$thumbnailUrl = isset($_POST['thumbnail_url']) ? trim($_POST['thumbnail_url']) : '';
		
		if ($title && $description && $instructorId > 0) {
			// Create slug from title
			$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
			$slug = $slug ?: 'course-' . time();
			
			// Check if slug exists and make unique
			$checkStmt = $conn->prepare('SELECT COUNT(*) as c FROM courses WHERE slug = ?');
			$checkStmt->bind_param('s', $slug);
			$checkStmt->execute();
			$checkRes = $checkStmt->get_result();
			$existing = $checkRes ? $checkRes->fetch_assoc() : null;
			$checkStmt->close();
			if (!empty($existing['c'])) {
				$slug .= '-' . time();
			}
			
			// Use `status` column (schema has `status` tinyint) instead of `is_published`.
			$stmt = $conn->prepare('INSERT INTO courses (instructor_id, title, slug, description, thumbnail_url, language, level, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
			$stmt->bind_param('issssssd', $instructorId, $title, $slug, $description, $thumbnailUrl, $language, $level, $price);
			$ok = $stmt->execute();
			$newId = $conn->insert_id;
			$stmt->close();
			
			
			if (is_ajax_request()) json_response(['ok' => $ok, 'course_id' => $newId]);
			header('Location: index.php?added=course');
			exit();
		}
		if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Missing required fields'], 400);
		header('Location: index.php?err=course_input');
		exit();
	}

	if ($action === 'add_lesson') {
		$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
		$title = isset($_POST['lesson_title']) ? trim($_POST['lesson_title']) : '';
		$videoUrl = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
		$content = isset($_POST['content']) ? trim($_POST['content']) : '';
		$duration = isset($_POST['duration_seconds']) ? (int)$_POST['duration_seconds'] : 0;
		$position = isset($_POST['position']) ? (int)$_POST['position'] : 0;
		
		if ($courseId > 0 && $title) {
			// If position is 0, get next position (schema uses `order`)
			if ($position === 0) {
					$posStmt = $conn->prepare('SELECT COALESCE(MAX(`order`), 0) + 1 as next_pos FROM lessons WHERE course_id = ?');
					$posStmt->bind_param('i', $courseId);
					$posStmt->execute();
					$posRes = $posStmt->get_result();
					$posRow = $posRes ? $posRes->fetch_assoc() : null;
						$position = isset($posRow['next_pos']) ? (int)$posRow['next_pos'] : 1;
				$ok = $stmt->execute();
				$newId = $conn->insert_id;
				$stmt->close();
			header('Location: index.php?added=lesson');
			exit();
		}
		if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Missing required fields'], 400);
		header('Location: index.php?err=lesson_input');
		exit();
	}

	if ($action === 'delete_course') {
		$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
		if ($courseId > 0) {
			$stmt = $conn->prepare('DELETE FROM courses WHERE id = ?');
			$stmt->bind_param('i', $courseId);
			$ok = $stmt->execute();
			$stmt->close();
			if (is_ajax_request()) json_response(['ok' => $ok, 'course_id' => $courseId]);
			header('Location: index.php');
			exit();
		}
		if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Invalid course id'], 400);
		header('Location: index.php?err=course');
		exit();
	}

	if ($action === 'delete_lesson') {
		$lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
		if ($lessonId > 0) {
			$stmt = $conn->prepare('DELETE FROM lessons WHERE id = ?');
			$stmt->bind_param('i', $lessonId);
			$ok = $stmt->execute();
			$stmt->close();
			if (is_ajax_request()) json_response(['ok' => $ok, 'lesson_id' => $lessonId]);
			header('Location: index.php');
			exit();
		}
		if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Invalid lesson id'], 400);
		header('Location: index.php?err=lesson');
		exit();
	}

	if ($action === 'toggle_admin') {
		$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
		if ($userId > 0) {
			if ($userId === $adminId) {
				if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Cannot change your own admin role'], 400);
				header('Location: /admin/index.php?err=self');
				exit();
			}
			// Get current role
			$stmt = $conn->prepare('SELECT role_id FROM users WHERE id = ?');
			$stmt->bind_param('i', $userId);
			$stmt->execute();
			$res = $stmt->get_result();
			$row = $res ? $res->fetch_assoc() : null;
			$stmt->close();
			if ($row) {
				$current = (int)$row['role_id'];
				$newRole = ($current === 3) ? 1 : 3; // demote to student by default, or promote
				$up = $conn->prepare('UPDATE users SET role_id = ? WHERE id = ?');
				$up->bind_param('ii', $newRole, $userId);
				$ok = $up->execute();
				$up->close();
				if (is_ajax_request()) json_response(['ok' => $ok, 'user_id' => $userId, 'role_id' => $newRole]);
				header('Location: index.php');
				exit();
			} 
		}
		if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Invalid user id'], 400);
		header('Location: index.php?err=user');
		exit();
	}

	if ($action === 'set_role_by_email') {
		$email = isset($_POST['email']) ? trim($_POST['email']) : '';
		$roleId = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
		if ($email && in_array($roleId, [1,2,3], true)) {
			// Prevent self-demotion via this form too
			$selfEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
			if (strcasecmp($email, $selfEmail) === 0 && $roleId !== 3) {
				if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Cannot change your own admin role'], 400);
				header('Location: index.php?err=self');
				exit();
			}
			$stmt = $conn->prepare('UPDATE users SET role_id = ? WHERE email = ?');
			$stmt->bind_param('is', $roleId, $email);
			$stmt->execute();
			$affected = $stmt->affected_rows;
			$stmt->close();
			if (is_ajax_request()) json_response(['ok' => true, 'updated' => $affected]);
			header('Location: index.php');
			exit();
		}
		if (is_ajax_request()) json_response(['ok' => false, 'error' => 'Invalid email or role'], 400);
		header('Location: index.php?err=input');
		exit();
	}
}

// Fetch stats
$stats = [
	'users' => 0,
	'courses' => 0,
	'lessons' => 0,
	'admins' => 0,
];
$q1 = $conn->query('SELECT COUNT(*) AS c FROM users'); if ($q1) { $stats['users'] = (int)$q1->fetch_assoc()['c']; }
$q2 = $conn->query('SELECT COUNT(*) AS c FROM courses'); if ($q2) { $stats['courses'] = (int)$q2->fetch_assoc()['c']; }
$q3 = $conn->query('SELECT COUNT(*) AS c FROM lessons'); if ($q3) { $stats['lessons'] = (int)$q3->fetch_assoc()['c']; }
$q4 = $conn->query('SELECT COUNT(*) AS c FROM users WHERE role_id = 3'); if ($q4) { $stats['admins'] = (int)$q4->fetch_assoc()['c']; }

// Fetch courses with instructor
$stmt = $conn->prepare('SELECT c.id, c.title, c.slug, c.price, c.status, c.created_at, u.fname, u.lname, u.email
                        FROM courses c
                        JOIN users u ON u.id = c.instructor_id
                        ORDER BY c.created_at DESC');
if ($stmt) {
	$stmt->execute();
	$res = $stmt->get_result();
	if ($res) { while ($r = $res->fetch_assoc()) { $courses[] = $r; } }
	$stmt->close();
}
// Fetch lessons grouped by course

if (!empty($courses)) {
	$ids = array_map(fn($r) => (int)$r['id'], $courses);
	$place = implode(',', array_fill(0, count($ids), '?'));
	$types = str_repeat('i', count($ids));
	$sql = "SELECT id, course_id, title, `order` AS position, duration_seconds FROM lessons WHERE course_id IN ($place) ORDER BY course_id, `order`";
	$stmt = $conn->prepare($sql);
	if ($stmt) {
		$stmt->bind_param($types, ...$ids);
		$stmt->execute();
		$res = $stmt->get_result();
		while ($row = $res->fetch_assoc()) {
			$cid = (int)$row['course_id'];
			if (!isset($lessonsByCourse[$cid])) $lessonsByCourse[$cid] = array();
			$lessonsByCourse[$cid][] = $row;
		}
		$stmt->close();
	}
}

// Fetch admins

$res = $conn->query('SELECT id, fname, lname, email FROM users WHERE role_id = 3 ORDER BY joined_at DESC');
if ($res) { while ($r = $res->fetch_assoc()) { $admins[] = $r; } }

// Fetch instructors for course creation

$res = $conn->query('SELECT id, fname, lname, email FROM users WHERE role_id IN (2, 3) ORDER BY fname, lname');
if ($res) { while ($r = $res->fetch_assoc()) { $instructors[] = $r; } }

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
		.header { display: none !important; }
		/* Remove top padding added by public layout */
		body { padding-top: 0 !important; }
		.section { background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.05); margin-bottom:24px; }
		.flex { display:flex; gap:20px; }
		.w-66 { flex: 2; }
		.w-33 { flex: 1; }
		.btn { padding:8px 12px; border:none; border-radius:6px; cursor:pointer; }
		.btn-danger { background:#dc2626; color:#fff; }
		.btn-outline { background:#fff; border:1px solid #ccc; }
		.role-form input, .role-form select { padding:8px; width:100%; margin-bottom:8px; }
		.lesson-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #f0f0f0; }
		.lesson-meta { font-size:12px; color:#666; }
		.muted { color:#666; font-size:12px; }
		.table-actions { display:flex; gap:8px; }
	</style>
	</head>
<body>
	<div class="admin-wrapper">
		<aside class="sidebar">
			<div class="logo-area">
				<h2>⚙️</h2>
				<span class="logo-text">Lerno Admin</span>
			</div>
			<ul class="side-nav">
				<li class="active"><a href="index.php"><img class="admin-icon" src="https://img.icons8.com/ios-filled/50/ffffff/dashboard.png" alt=""/><span class="link-text">Dashboard</span></a></li>
				<li><a href="../courses.php"><img class="admin-icon" src="https://img.icons8.com/ios-glyphs/30/ffffff/book.png" alt=""/><span class="link-text">Courses</span></a></li>
				<li><a href="../index.php"><img class="admin-icon" src="https://img.icons8.com/ios-glyphs/30/ffffff/home.png" alt=""/><span class="link-text">Home</span></a></li>
				<li class="logout"><a href="../logout.php"><img class="admin-icon" src="https://img.icons8.com/ios-glyphs/30/ffffff/exit.png" alt=""/><span class="link-text">Logout</span></a></li>
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
						<div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 13h6v8H3zM9 3h6v18H9zM15 8h6v13h-6z"/></svg></div>
						<div class="stat-info"><h3>Total Users</h3><div class="number"><?= (int)$stats['users'] ?></div></div>
					</div>
					<div class="stat-card">
						<div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3l7 4v10l-7 4l-7-4V7z"/></svg></div>
						<div class="stat-info"><h3>Courses</h3><div class="number"><?= (int)$stats['courses'] ?></div></div>
					</div>
					<div class="stat-card">
						<div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24"><path fill="currentColor" d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg></div>
						<div class="stat-info"><h3>Lessons</h3><div class="number"><?= (int)$stats['lessons'] ?></div></div>
					</div>
					<div class="stat-card">
						<div class="stat-icon"><svg class="stat-svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 17l-5 3l1.9-5.9L4 9h6l2-6l2 6h6l-4.9 5.1L17 20z"/></svg></div>
						<div class="stat-info"><h3>Admins</h3><div class="number"><?= (int)$stats['admins'] ?></div></div>
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
												<option value="<?= (int)$inst['id'] ?>"><?= htmlspecialchars($inst['fname'] . ' ' . $inst['lname']) ?> (<?= htmlspecialchars($inst['email']) ?>)</option>
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
										<tr data-course-id="<?= (int)$c['id'] ?>">
											<td>#<?= (int)$c['id'] ?></td>
											<td><?= htmlspecialchars($c['title']) ?><div class="muted">Slug: <?= htmlspecialchars($c['slug']) ?></div></td>
											<td><?= htmlspecialchars($c['fname'] . ' ' . $c['lname']) ?><div class="muted"><?= htmlspecialchars($c['email']) ?></div></td>
											<td>$<?= number_format((float)$c['price'], 2) ?></td>
											<td><?= htmlspecialchars($c['created_at']) ?></td>
											<td class="table-actions">
												<button class="btn btn-outline toggle-lessons">Lessons</button>
												<button class="btn btn-danger delete-course">Delete</button>
											</td>
										</tr>
										<tr class="lessons-row" data-course-id="<?= (int)$c['id'] ?>" style="display:none;background:#fafafa;">
											<td colspan="6">
							<?php $cid = (int)$c['id']; $less = isset($lessonsByCourse[$cid]) ? $lessonsByCourse[$cid] : array(); ?>
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
														<!-- Note: `is_preview` removed from schema; preview control is not available -->
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
													<div class="lesson-row" data-lesson-id="<?= (int)$l['id'] ?>">
														<div>
															<strong><?= (int)$l['position'] ?>.</strong>
															<?= htmlspecialchars($l['title']) ?>
															<span class="lesson-meta">(<?= (int)$l['duration_seconds'] ? gmdate('i:s', (int)$l['duration_seconds']) : '—' ?>)</span>
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
									<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0f0f0;" data-user-id="<?= (int)$u['id'] ?>">
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
							<div class="muted" style="margin-top:8px;">Note: You cannot demote your own admin role.</div>
						</div>
					</div>
				</div>

			</div>
		</main>
	</div>
</body>
</html>
