<?php session_start() ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/course.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/course.js" defer></script>
</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="hero">
        <div class="hero-content">
            <div class="tag">Python</div>
            <div class="tag">Programming</div>

            <h1>Python for Web & Software Development</h1>
            <div class="course-desc">
                Step into the world of Python! 🐍 Learn the basics, build real projects,
                experiment freely, and become the developer who actually enjoys problem-solving.
            </div>

            <div class="stats">
                <div><strong>4.9</strong>(18,200 reviews)</div>
                <div><strong>18.2k</strong>Students enrolled</div>
                <div><strong>10 weeks</strong>Course duration</div>
            </div>
        </div>
    </div>

    <div class="main-container">

        <div class="content">

            <div class="learn-grid">
                <div class="learn-item">Build Python applications from scratch</div>
                <div class="learn-item">Master functions & loops</div>
                <div class="learn-item">Work with files & modules</div>
                <div class="learn-item">Object-Oriented Programming (OOP)</div>
            </div>

            <div class="curriculum-title">Course Curriculum</div>

            <div class="curriculum-list">

                <div class="curriculum-item">
                    <div class="circle">1</div>
                    <div class="text">
                        <h3>Python Basics</h3>
                        <p>10 lessons • 2h 20m</p>
                    </div>
                </div>

                <div class="curriculum-item">
                    <div class="circle">2</div>
                    <div class="text">
                        <h3>Data Types, Lists & Dictionaries</h3>
                        <p>12 lessons • 3h</p>
                    </div>
                </div>

                <div class="curriculum-item">
                    <div class="circle">3</div>
                    <div class="text">
                        <h3>Functions & Modules</h3>
                        <p>9 lessons • 2h 40m</p>
                    </div>
                </div>

                <div class="curriculum-item">
                    <div class="circle">4</div>
                    <div class="text">
                        <h3>Object-Oriented Programming</h3>
                        <p>8 lessons • 3h 10m</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="sidebar">

            <div class="box">
                <div class="price-title">$89.99</div>
                <div class="small-note">Get a certificate after completing this course</div>

                <form action="enroll.php" method="post">
                    <!-- TODO: set the correct course_id for this course -->
                    <input type="hidden" name="course_id" value="1">
                    <button type="submit" class="enroll-btn">Enroll Now</button>
                </form>
                <button class="secondary-btn">♡ Add to Wishlist</button>
                <button class="secondary-btn">↗ Share This Course</button>
            </div>

            <div class="box">
                <div class="details-row"><strong>Duration:</strong> 10 weeks</div>
                <div class="details-row"><strong>Level:</strong> Beginner</div>
                <div class="details-row"><strong>Students:</strong> 18.2k</div>
                <div class="details-row"><strong>Rating:</strong> ⭐ 4.9</div>
            </div>

            <div class="help-box">
                Need help? Our support team is available 24/7.
            </div>

        </div>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>

</body>

</html>