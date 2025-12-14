<?php
session_start();
require 'conn.php'; // Database connection

// Require login: redirect if not authenticated
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

// Handle remove item from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_course_id'])) {
    $removeCourseId = intval($_POST['remove_course_id']);
    if ($removeCourseId > 0) {
        $del = $conn->prepare('DELETE FROM cart WHERE user_id = ? AND course_id = ?');
        if (!$del) {
            die('Error preparing DELETE: ' . $conn->error);
        }
        $del->bind_param('ii', $_SESSION['userId'], $removeCourseId);
        if (!$del->execute()) {
            die('Error executing DELETE: ' . $del->error);
        }
        $del->close();
    }
    header('Location: cart.php');
    exit();
}

// Handle checkout: move cart items to my courses (enrollments)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $userId = $_SESSION['userId'];
    // Insert all cart items for this user into enrollments (avoid duplicates)
    $ins = $conn->prepare("INSERT INTO enrollments (user_id, course_id) 
                           SELECT c.user_id, c.course_id FROM cart c 
                           WHERE c.user_id = ? AND NOT EXISTS (
                               SELECT 1 FROM enrollments e 
                               WHERE e.user_id = c.user_id AND e.course_id = c.course_id
                           )");
    $ins->bind_param('i', $userId);
    $ins->execute();
    $ins->close();

    // Clear cart for this user
    $delAll = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
    $delAll->bind_param('i', $userId);
    $delAll->execute();
    $delAll->close();

    // Redirect to home
    header('Location: index.php');
    exit();
}

// Fetch courses in the cart for the logged-in user (lerno2 schema)
$stmt = $conn->prepare("SELECT c.id as cart_id, crs.id, crs.title, crs.description, crs.price, crs.thumbnail_url
                        FROM cart c
                        JOIN courses crs ON c.course_id = crs.id
                        WHERE c.user_id = ?");
$stmt->bind_param('i', $_SESSION['userId']);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

$subtotal = 0;

foreach ($items as $item) {
    $subtotal += $item['price'];
}

$taxes = $subtotal * .1;
$totalPrice = $subtotal + $taxes;

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

<body>

<body>
    <?php include("components/navbar.php") ?>

    <div class="cart-wrapper">
        <div class="cart-title">
            <h2>Your Shopping Cart</h2>
            <p><?php if (!empty($items)) echo mysqli_num_rows($items) ?> Courses in Cart</p>
        </div>

        <div class="cart-container">
            
            <div class="cart-items">
                <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($item['thumbnail_url'] ?? 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4') ?>" alt="Course" class="item-img">
                    <div class="item-details">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <div class="item-actions">
                            <form method="post">
                                <input type="hidden" name="remove_course_id" value="<?= (int)$item['id'] ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                    <div class="item-price-box">
                        <span class="price" data-price="<?= number_format((float)$item['price'], 2) ?>">$<?= number_format((float)$item['price'], 2) ?></span>
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

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>

</body>

</html>