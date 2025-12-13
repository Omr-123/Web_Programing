<?php
session_start();
require 'conn.php'; // Database connection

// Fetch courses in the cart
$stmt = $conn->prepare("SELECT * FROM cart JOIN courses ON cart.courseId = courses.courseId WHERE cart.userId = 2");
$stmt->execute();
$items = $stmt->get_result();

$subtotal = 0;

foreach ($items as $item) {
    $subtotal += $item['price'];
}

$taxes = $subtotal * .1;
$totalPrice = $subtotal + $taxes;
if (isset($_POST['delete'])) {
    $id = $_POST['delete'];

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = 2");
$stmt->execute();

}
// // Handle payment process
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_cash'])) {
//     $stmt = $conn->prepare("INSERT INTO Enrollment (CourseID, EnrollmentDate) SELECT CourseID, NOW() FROM Cart");
//     if ($stmt->execute()) {
//         // Clear the cart after payment
//         $stmt = $conn->prepare("DELETE FROM Cart");
//         $stmt->execute();
//         echo 'Payment successful. Courses added to your account.';
//     } else {
//         echo 'Payment failed. Please try again.';
//     }
// }

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
    <link rel="stylesheet" href="assets/css/cart.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/cart.js" defer></script>
</head>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/cart.js" defer></script>
</head>

<body>
    
    <div class="header">
        <div class="nav">
            <a class="nav-logo" href="index.html">
                <img src="assets/images/Lerno.png" alt="Logo">
            </a>
            <ul class="nav-links">
                <li class="nav-link"><a href="index.html">Home</a></li>
                <li class="nav-link"><a href="courses.php">Courses</a></li>
                <li class="nav-link"><a href="my-courses.php">My Courses</a></li>
                <li class="nav-link"><a href="login.html">Login</a></li>
                <li class="nav-link"><a href="register.html">Register</a></li>
                <li class="nav-link active"><a href="cart.">phpCart</a></li>
            </ul>
        </div>
    </div>

    <div class="cart-wrapper">
        <div class="cart-title">
            <h2>Your Shopping Cart</h2>
            <p><?php if (!empty($items)) echo mysqli_num_rows($items) ?> Courses in Cart</p>
        </div>

        <div class="cart-container">
            
            <div class="cart-items">
                <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <img src="assets/images/<?php echo $item['thumb'] ?>" alt="Course" class="item-img">
                    <div class="item-details">
                        <h3><?php echo $item['name'] ?></h3>
                        <p><?php echo $item['description'] ?></p>
                        <div class="item-actions">
                            <form method="post">
                            <button type="submit" name="delete" value="5">Delete User</button>
                            </form>
                            <span class="remove-btn" >Remove</span>
                        </div>
                    </div>
                    <div class="item-price-box">
                        <span class="price" data-price="<?php echo round($item['price'], 2) ?>">$<?php echo round($item['price'], 2) ?></span>
                    </div>
                </div>
                <?php endforeach ?>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal-price">$<?php echo round($subtotal, 2) ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Tax (10%)</span>
                    <span id="tax-price">$<?php echo round($taxes, 2) ?></span>
                </div>

                <div class="divider"></div>

                <div class="summary-row total">
                    <span>Total</span>
                    <span id="total-price">$<?php echo round($totalPrice, 2) ?></span>
                </div>

                <button class="checkout-btn">Proceed to Checkout</button>
                
                <div class="coupon-box">
                    <input type="text" placeholder="Coupon Code">
                    <button>Apply</button>
                </div>
            </div>

        </div>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>

</body>

</html>