<div class="header">
    <div class="nav">
        <a class="nav-logo" href="index.php">
            <img src="assets/images/Lerno.png" alt="Logo">
        </a>
        <ul class="nav-links">
            <li class="nav-link active"><a href="index.php">Home</a></li>
            <li class="nav-link"><a href="courses.php">Courses</a></li>
            <li class="nav-link"><a href="my-courses.php">My Courses</a></li>
            <?php if (isset($_SESSION['userId'])): ?>
                <li class="nav-link"><span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></span></li>
                <li class="nav-link"><a href="logout.php">Logout</a></li>
                <li class="nav-link"><a href="cart.php">Cart</a></li>
            <?php else: ?>
                <li class="nav-link"><a href="login.php">Login</a></li>
                <li class="nav-link"><a href="register.html">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>