<div class="header">
    <div class="nav">
        <a class="nav-logo" href="index.php">
            <img src="assets/images/Lerno.png" alt="Logo">
        </a>
        <ul class="nav-links">
            <li class="nav-link"><a href="index.php">Home</a></li>
            <li class="nav-link"><a href="courses.php">Courses</a></li>
            <li class="nav-link"><a href="my-courses.php">My Courses</a></li>
            <?php if (isset($_SESSION['role']) && (int)$_SESSION['role'] === 2): ?>
                <li class="nav-link"><a href="dashboard.php">Dashboard</a></li>
            <?php endif; ?>
            <?php if (isset($_SESSION['userId'])): ?>
                <li class="nav-link"><a href="cart.php">Cart</a></li>
                <?php
                    // Fetch and show circular avatar for any logged-in user if available
                    $avatarUrl = null;
                    if (isset($conn) && $conn instanceof mysqli) {
                        $uid = (int)($_SESSION['userId'] ?? 0);
                        if ($uid > 0 && ($stmt = $conn->prepare("SELECT profile_image_url FROM users WHERE id = ?"))) {
                            $stmt->bind_param('i', $uid);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            if ($row = $res->fetch_assoc()) { $avatarUrl = $row['profile_image_url']; }
                            $stmt->close();
                        }
                    }
                ?>
                <li class="nav-link"><a href="#"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User') ?></a></li>
                <li class="nav-link"><a href="logout.php">Logout</a></li>
                <?php if ($avatarUrl): ?>
                    <li class="nav-link" style="display:flex; align-items:center; margin-left:12px;">
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid #ddd;" />
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <li class="nav-link"><a href="login.php">Login</a></li>
                <li class="nav-link"><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>