<?php session_start() ?>
<?php require 'conn.php' ?>

<?php
// Handle student actions from navbar dropdown
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    $userId = $_SESSION['userId'];
    $userRole = $_SESSION['role'];

    // Only allow students to update their profile
    if ($userRole == 1) {

        if ($_POST['action'] == 'update_student_photo') {

            $photo_url = trim($_POST['avatar']);

            if ($photo_url && filter_var($photo_url, FILTER_VALIDATE_URL)) {
                $up = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $up->bind_param('si', $photo_url, $userId);
                $up->execute();
                $up->close();
            }

            header('Location: index.php');
            exit();

        } elseif ($_POST['action'] == 'delete_student_photo') {

            // Remove photo from database (fallback will be local image in HTML)
            $up = $conn->prepare("UPDATE users SET avatar = '' WHERE id = ?");
            $up->bind_param('i', $userId);
            $up->execute();
            $up->close();

            header('Location: index.php');
            exit();

        } elseif ($_POST['action'] == 'change_student_password') {

            $email = trim($_POST['email']);
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];

            if ($email && $current_password && $new_password && strlen($new_password) >= 6) {

                // Verify email and current password
                $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? AND id = ?");
                $stmt->bind_param('si', $email, $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                $user = $res->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($current_password, $user['password'])) {
                    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $up->bind_param('si', $new_password_hash, $userId);
                    $up->execute();
                    $up->close();
                }

                header('Location: index.php');
                exit();
            }
        }
    }
}

// Simple SELECTs
$sql = "SELECT id, fname, lname, avatar FROM users WHERE role_id = 2 LIMIT 4";
$result = $conn->query($sql);
$instructors = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$sql = "SELECT id, title, description, thumbnail_url FROM courses WHERE status = 1 ORDER BY created_at DESC LIMIT 4";
$result = $conn->query($sql);
$featured = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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
            <a href="courses.php"><button class="hero-btn">Explore Courses</button></a>
        </div>
    </div>

    <div class="section-container">
        <div class="section-title">
            <h2 class="section-name">Featured Courses</h2>
            <p class="section-description">Pick from our top rated selection</p>
        </div>

        <div class="courses">
            <?php if ($featured): ?>
            <?php foreach ($featured as $course): ?>
            <div class="course">
                <img src="<?= htmlspecialchars($course['thumbnail_url'] != '' ? $course['thumbnail_url'] : 'assets/images/course.png') ?>" alt="Course" class="course-image">
                <div class="course-details">
                    <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="course-description"><?= htmlspecialchars(substr($course['description'], 0, 140)) ?>...</p>
                    <a href="course.php?id=<?= $course['id'] ?>" class="course-button">Learn More</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
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
            <?php if ($instructors): ?>
            <?php foreach ($instructors as $instructor): ?>
            <div class="course">
                <img src="<?= htmlspecialchars($instructor['avatar'] != '' ? $instructor['avatar'] : 'assets/images/user.png') ?>" alt="<?= htmlspecialchars($instructor['fname']) ?>" class="course-image">
                <div class="course-details">
                    <h3 class="course-title"><?= htmlspecialchars($instructor['fname'] . " " . $instructor['lname']) ?></h3>
                    <p class="course-description">Experienced Instructor</p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php include("components/footer.php") ?>

</body>

</html>
