<?php session_start() ?>
<?php include_once("conn.php") ?>

<?php 
// Handle student actions from navbar dropdown
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = (int)($_SESSION['userId'] ?? 0);
    $userRole = (int)($_SESSION['role'] ?? 0);
    
    // Only allow students to update their profile
    if ($userId > 0 && $userRole === 1) {
        if ($_POST['action'] === 'update_student_photo') {
            $photo_url = trim($_POST['profile_image_url'] ?? '');
            if ($photo_url && filter_var($photo_url, FILTER_VALIDATE_URL)) {
                $stmt = $conn->prepare("UPDATE users SET profile_image_url = ? WHERE id = ?");
                $stmt->bind_param('si', $photo_url, $userId);
                $stmt->execute();
                $stmt->close();
                header('Location: index.php');
                exit();
            }
        } elseif ($_POST['action'] === 'delete_student_photo') {
            // Set to default avatar instead of NULL
            $default_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname'] ?? 'User') . '&size=200&background=9a0176&color=fff';
            $stmt = $conn->prepare("UPDATE users SET profile_image_url = ? WHERE id = ?");
            $stmt->bind_param('si', $default_avatar, $userId);
            $stmt->execute();
            $stmt->close();
            header('Location: index.php');
            exit();
        } elseif ($_POST['action'] === 'change_student_password') {
            $email = trim($_POST['email'] ?? '');
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            
            if ($email && $current_password && $new_password && strlen($new_password) >= 6) {
                // Verify email and current password
                $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? AND id = ?");
                $stmt->bind_param('si', $email, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($current_password, $user['password'])) {
                        // Update password
                        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $updateStmt->bind_param('si', $new_password_hash, $userId);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }
                $stmt->close();
                header('Location: index.php');
                exit();
            }
        }
    }
}

// Fetch instructors with non-empty profile image and bio
$stmt = $conn->prepare("SELECT id, fname, lname, profile_image_url, bio FROM users WHERE role_id = 2 AND profile_image_url IS NOT NULL AND profile_image_url <> '' AND bio IS NOT NULL AND bio <> '' LIMIT 4");
$stmt->execute();
$instructors = $stmt->get_result();
$stmt->close();

// Fetch featured courses
$stmt = $conn->prepare("SELECT id, title, description, thumbnail_url FROM courses WHERE is_published = 1 ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$featured = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/courses.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/home.js" defer></script>
    <script src="assets/js/courses.js" defer></script>
</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="home-hero">
        <div class="hero-content centered">
            <h1>Best Learning Education Platform in The World</h1>
            <p>Unlock your potential with expert-led courses.</p>
            <a href="courses.php">
                <button class="hero-btn">Explore Courses</button>
            </a>
        </div>
    </div>

    <div class="section-container">
        <div class="section-title">
            <h2 class="section-name">Featured Courses</h2>
            <p class="section-description">Pick from our top rated selection</p>
        </div>

        <div class="courses">
            <?php foreach ($featured as $course): ?>
            <div class="course">
                <img src="<?= htmlspecialchars($course['thumbnail_url'] ?? 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4') ?>" alt="Course" class="course-image">
                <div class="course-details">
                    <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="course-description"><?= htmlspecialchars(substr($course['description'],0,140)) ?>...</p>
                    <a href="course.php?id=<?= (int)$course['id'] ?>" class="course-button">Learn More</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section-container bg-light">
        <div class="section-title">
            <h2 class="section-name">What Our Students Say</h2>
        </div>

        <div class="testimonial-wrapper">
            <div class="testimonial active">
                <img src="https://via.placeholder.com/100x100?text=Student" alt="Student" class="student-img">
                <p>"This platform changed the way I learn! Highly recommend."</p>
                <div class="stars">★★★★★</div>
                <h4>Ahmed A.</h4>
            </div>
        </div>
    </div>

    <div class="section-container">
        <div class="section-title">
            <h2 class="section-name">Our Achievements</h2>
        </div>
        <div class="stats-container">
            <div class="stat-box">
                <div class="icon">🎓</div>
                <div class="stat-number" data-target="1500">1500</div>
                <p>Students Enrolled</p>
            </div>
            <div class="stat-box">
                <div class="icon">📚</div>
                <div class="stat-number" data-target="120">120</div>
                <p>Courses Available</p>
            </div>
            <div class="stat-box">
                <div class="icon">🏆</div>
                <div class="stat-number" data-target="10">10</div>
                <p>Years Experience</p>
            </div>
            <div class="stat-box">
                <div class="icon">🌟</div>
                <div class="stat-number" data-target="5000">5</div>
                <p>Happy Students</p>
            </div>
        </div>
    </div>

    <div class="cta-banner">
        <h2>Join Thousands of Learners Today</h2>
        <p>Start your learning journey with our top-quality courses.</p>
        <a href="index.php" class="cta-btn">Get Started</a>
    </div>

    <div class="section-container">
        <div class="section-title">
            <h2 class="section-name">Meet Our Instructors</h2>
        </div>
        
        <div class="courses"> 
            <?php foreach($instructors as $instructor):  ?>
            <div class="course">
                <img src="<?= htmlspecialchars($instructor['profile_image_url'] ?? 'https://via.placeholder.com/300x300?text=Instructor') ?>" alt="<?= htmlspecialchars($instructor['fname']) ?>" class="course-image">
                <div class="course-details">
                    <h3 class="course-title"><?php echo htmlspecialchars($instructor['fname'] . " " . $instructor['lname']) ?></h3>
                    <p class="course-description"><?= htmlspecialchars($instructor['bio'] ?? 'Experienced Instructor') ?></p>
                </div>
            </div>
            <?php endforeach ?>
        </div>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>

</body>

</html>
