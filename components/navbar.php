<div class="header">
    <div class="nav">
        <a class="nav-logo" href="index.php">
            <img src="assets/images/Lerno.png" alt="Logo">
        </a>

        <ul class="nav-links">
            <li class="nav-link"><a href="index.php">Home</a></li>
            <li class="nav-link"><a href="courses.php">Courses</a></li>
            <li class="nav-link"><a href="my-courses.php">My Courses</a></li>

            <?php if ($_SESSION['role'] == 2): ?>
                <li class="nav-link"><a href="dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] == 3): ?>
                <li class="nav-link"><a href="admin/">Admin</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['userId'])): ?>
                <li class="nav-link"><a href="cart.php">Cart</a></li>

                <?php
                $userId = $_SESSION['userId'];
                $result = $conn->query("SELECT avatar FROM users WHERE id = $userId");
                $result = $result->fetch_assoc();
                $avatar = $result['avatar'];
                ?>

                <li class="nav-link">
                    <a href="logout.php">Logout</a>
                </li>

                <li class="nav-link user-profile">
                    <a href="profile.php" class="profile-anchor">
                        <img src="<?= $avatar ?: "assets/images/avatar.jpg" ?>" alt="Avatar" class="user-avatar">
                        <span class="profile-name"><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                    </a>

                    <?php if ($_SESSION['role'] == 1): ?>
                    <div class="avatar-dropdown-menu">
                        <div class="dropdown-section">
                            <h4>Change Photo</h4>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="update_student_photo">
                                <input type="url" name="pfp" placeholder="https://..." value="<?= htmlspecialchars($avatarUrl) ?>" required>
                                <button type="submit">Update</button>
                            </form>
                        </div>

                        <div class="dropdown-section">
                            <h4>Delete Photo</h4>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="delete_student_photo">
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                        </div>

                        <div class="dropdown-section">
                            <h4>Change Password</h4>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="change_student_password">
                                <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['email']) ?>" required>
                                <input type="password" name="current_password" placeholder="Current password" required>
                                <input type="password" name="new_password" placeholder="New password" required>
                                <button type="submit">Change</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </li>

            <?php else: ?>
                <li class="nav-link"><a href="login.php">Login</a></li>
                <li class="nav-link"><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>