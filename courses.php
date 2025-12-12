<?php
session_start();
include_once 'conn.php';
//if(!isset($_SESSION['StudentID'])){
//    header('Location: login.html');
 //   exit();
//}
//$studentID = $_SESSION['StudentID'];
//$CourseID = isset($_GET['CourseID']) ? intval($_GET['CourseID']) : 0;
//if($CourseID <= 0){
//    die("Invalid Course ID.");
//}
$stmt = $conn->prepare("SELECT * FROM courses");
$stmt->execute();
$courses = $stmt->get_result();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //$_SESSION['StudentID'] = $studentID;
    //$_SESSION['CourseID'] = $CourseID;
    //$_SESSION['EnrollmentDate'] = date('Y-m-d');
    $studentID = 2;
    $courseID = $_REQUEST['course_id'];

    $stmt = $conn->prepare("INSERT INTO cart (userId, courseId) VALUES (?, ?)");
    $stmt->bind_param("ii", $studentID, $courseID);

    if($stmt->execute()){

    header("Location: courses.php");
    exit();
    } else {
        echo "Error enrolling: " . $stmt->error;
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/courses.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/courses.js" defer></script>
</head>

<body>
    
    <div class="header">
        <div class="nav">
            <a class="nav-logo" href="index.html">
                <img src="assets/images/Lerno.png" alt="Logo">
            </a>
            <ul class="nav-links">
                <li class="nav-link"><a href="index.html">Home</a></li>
                <li class="nav-link active"><a href="courses.php">Courses</a></li>
                <li class="nav-link"><a href="my-courses.php">My Courses</a></li>
                <li class="nav-link"><a href="login.html">Login</a></li>
                <li class="nav-link"><a href="register.html">Register</a></li>
                <li class="nav-link"><a href="cart.php">Cart</a></li>
            </ul>
        </div>
    </div>

    <div class="section-title">
        <h1 class="section-name">Courses</h1>
        <p class="section-description">Catch Up</p>
    </div>
    
    <div class="courses">
        <?php foreach($courses as $course): ?>
        <div class="course">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS_FvzJIVensrB8Tl1umSkp0xH253U1_qMvEQ&s" alt="course" class="course-image">
            <div class="course-details">
                <h3 class="course-title"><?php echo $course['name']; ?></h3>
                <p class="course-description"><?php echo $course['description']; ?></p>
                <span class="course-price">$<?php echo round($course['price'],2) ?></span>
                <a href="course.html" class="course-button">Learn More</a>
                <!-- Form to handle Add To Cart -->
                <form action="courses.php" method="post">
                    <input type="hidden" name="course_id" value="<?php echo $course['courseId']; ?>">
                    <button type="submit" class="course-button">Add To Cart</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>
</body>

</html>

