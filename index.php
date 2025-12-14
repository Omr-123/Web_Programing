<?php session_start() ?>
<?php include_once("conn.php") ?>

<?php 
// Fetch instructors per new schema
$stmt = $conn->prepare("SELECT id, fname, lname FROM users WHERE role_id = 2 LIMIT 4");
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
                <img src="https://img.freepik.com/free-photo/young-bearded-man-with-striped-shirt_273609-5677.jpg?semt=ais_hybrid&w=740&q=80" alt="Ahmed" class="student-img">
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
            <?php foreach($instructors as $instructor): ?>
            <div class="course">
                <img src="https://img.freepik.com/free-photo/young-bearded-man-with-striped-shirt_273609-5677.jpg?semt=ais_hybrid&w=740&q=80" alt="Inst" class="course-image">
                <div class="course-details">
                    <h3 class="course-title"><?php echo $instructor['fname'] . " " . $instructor['lname'] ?></h3>
                    <p class="course-description">Senior Web Developer</p>
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
