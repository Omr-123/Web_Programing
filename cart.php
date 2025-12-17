<?php
session_start();
require 'conn.php';

// Require login
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

// Handle remove item from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_course_id'])) {
    $removeCourseId = $_POST['remove_course_id'];

    if ($removeCourseId) {
        $conn->query("DELETE FROM cart WHERE user_id = $userId AND course_id = $removeCourseId");
    }

    header('Location: cart.php');
    exit();
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    // Get cart items
    $result = $conn->query("SELECT course_id FROM cart WHERE user_id = $userId");

    while ($row = $result->fetch_assoc()) {
        $courseId = $row['course_id'];

        // Add to enrollments (enrolled_at handled by DB)
        $conn->query("INSERT INTO enrollments (user_id, course_id) VALUES ($userId, $courseId)");
    }

    // Clear cart
    $conn->query("DELETE FROM cart WHERE user_id = $userId");

    header('Location: my-courses.php');
    exit();
}

// Fetch cart items
$items = array();
$sql = "SELECT crs.id, crs.title, crs.description, crs.price, crs.thumbnail_url FROM cart c JOIN courses crs ON c.course_id = crs.id WHERE c.user_id = $userId";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'];
}

$taxes = $subtotal * 0.1;
$totalPrice = $subtotal + $taxes;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/cart.js" defer></script>
</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="cart-wrapper">
        <div class="cart-title">
            <h2>Your Shopping Cart</h2>
            <p><?php echo count($items) ?> Courses in Cart</p>
        </div>

        <div class="cart-container">

            <div class="cart-items">
                <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($item['thumbnail_url'] != '' ? $item['thumbnail_url'] : 'assets/images/course.png') ?>" alt="Course" class="item-img">
                    <div class="item-details">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <div class="item-actions">
                            <form method="post">
                                <input type="hidden" name="remove_course_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                    <div class="item-price-box">
                        <span class="price" data-price="<?= number_format($item['price'], 2) ?>"><?= $item['price'] > 0 ? '$' . number_format($item['price'], 2) : "Free" ?></span>
                    </div>
                </div>
                <?php endforeach ?>
                <?php endif; ?>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal-price"><?php echo $subtotal ? '$' . round($subtotal, 2) : "Free" ?></span>
                </div>

                <div class="summary-row">
                    <span>Tax (10%)</span>
                    <span id="tax-price"><?php echo $taxes ? '$' . round($taxes, 2) : "Free" ?></span>
                </div>

                <div class="divider"></div>

                <div class="summary-row total">
                    <span>Total</span>
                    <span id="total-price"><?php echo $totalPrice ? '$' . round($totalPrice, 2) : "Free" ?></span>
                </div>

                <form method="post">
                    <button type="submit" name="checkout" value="1" class="checkout-btn">Proceed to Checkout</button>
                </form>

                <div class="coupon-box">
                    <input type="text" placeholder="Coupon Code">
                    <button>Apply</button>
                </div>
            </div>

        </div>
    </div>

<?php include("components/footer.php") ?>

</body>

</html>
