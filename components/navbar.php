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
            <?php if (isset($_SESSION['role']) && (int)$_SESSION['role'] === 3): ?>
                <li class="nav-link"><a href="admin/index.php">Admin</a></li>
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
                            if ($row = $res->fetch_assoc()) { 
                                $avatarUrl = $row['profile_image_url']; 
                                // If no avatar, use default
                                if (empty($avatarUrl)) {
                                    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname'] ?? 'User') . '&size=200&background=9a0176&color=fff';
                                }
                            }
                            $stmt->close();
                        }
                    }
                ?>
                <li class="nav-link"><a href="#"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User') ?></a></li>
                <li class="nav-link"><a href="logout.php">Logout</a></li>
                <li class="nav-link user-avatar-dropdown" style="display:flex; align-items:center; margin-left:12px; position:relative;">
                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid #ddd;cursor:pointer;" />
                        <?php if (isset($_SESSION['role']) && (int)$_SESSION['role'] === 1): ?>
                        <div class="avatar-dropdown-menu">
                            <div class="dropdown-section">
                                <h4>Change Photo</h4>
                                <form method="post" action="index.php">
                                    <input type="hidden" name="action" value="update_student_photo" />
                                    <input type="url" name="profile_image_url" placeholder="https://..." value="<?= htmlspecialchars($avatarUrl) ?>" required />
                                    <button type="submit">Update</button>
                                </form>
                            </div>
                            <div class="dropdown-section">
                                <h4>Delete Photo</h4>
                                <form method="post" action="index.php">
                                    <input type="hidden" name="action" value="delete_student_photo" />
                                    <button type="submit" style="background:#dc2626;">Delete</button>
                                </form>
                            </div>
                            <div class="dropdown-section">
                                <h4>Change Password</h4>
                                <form method="post" action="index.php">
                                    <input type="hidden" name="action" value="change_student_password" />
                                    <input type="email" name="email" placeholder="Your email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required />
                                    <input type="password" name="current_password" placeholder="Current password" required />
                                    <input type="password" name="new_password" placeholder="New password" required />
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