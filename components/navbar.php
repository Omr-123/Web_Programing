<div class="header">
    <div class="nav">
        <a class="nav-logo" href="index.php">
            <img src="assets/images/Lerno.png" alt="Logo">
        </a>

        <ul class="nav-links">
            <li class="nav-link"><a href="index.php">Home</a></li>
            <li class="nav-link"><a href="courses.php">Courses</a></li>
            <li class="nav-link"><a href="my-courses.php">My Courses</a></li>


            <?php if (isset($_SESSION['userId'])): ?>
                
            <?php if ($_SESSION['role'] == 2): ?>
                <li class="nav-link"><a href="dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] == 3): ?>
                <li class="nav-link"><a href="admin/">Admin</a></li>
            <?php endif; ?>
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

                <li class="nav-link user-profile" style="position:relative">
                    <a href="#" class="profile-anchor" aria-haspopup="true" aria-expanded="false" aria-controls="avatar-dropdown-menu">
                        <img src="<?= $avatar ?: 'assets/images/avatar.jpg' ?>" alt="Avatar" class="user-avatar">
                        <span class="profile-name"><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                    </a>

                    <div class="avatar-dropdown-menu" id="avatar-dropdown-menu" role="menu" aria-hidden="true">
                        <div class="dropdown-section" style="margin-bottom:10px;">
                            <h4 style="margin:0 0 8px 0;font-size:14px">Change Photo</h4>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="update_student_photo">
                                <input type="url" name="avatar" placeholder="https://..." value="<?= htmlspecialchars($avatar ?? '') ?>" style="width:100%;padding:8px;margin-bottom:8px;border:1px solid #ddd;border-radius:6px;" required>
                                <button type="submit" class="btn">Update</button>
                            </form>
                        </div>

                        <div class="dropdown-section" style="margin-bottom:10px;">
                            <h4 style="margin:0 0 8px 0;font-size:14px">Delete Photo</h4>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="delete_student_photo">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>

                        <div class="dropdown-section">
                            <h4 style="margin:0 0 8px 0;font-size:14px">Change Password</h4>
                            <?php $passStatus = $_GET['pass_status'] ?? null; if ($passStatus): ?>
                                <?php if ($passStatus === 'success'): ?>
                                    <div class="notice success" style="margin-bottom:8px;color:green">Password updated successfully.</div>
                                <?php elseif ($passStatus === 'wrong_current'): ?>
                                    <div class="notice error" style="margin-bottom:8px;color:#b91c1c">Current password is incorrect.</div>
                                <?php elseif ($passStatus === 'invalid'): ?>
                                    <div class="notice error" style="margin-bottom:8px;color:#b91c1c">Please provide a new password (min 6 chars).</div>
                                <?php elseif ($passStatus === 'notfound'): ?>
                                    <div class="notice error" style="margin-bottom:8px;color:#b91c1c">User not found.</div>
                                <?php else: ?>
                                    <div class="notice error" style="margin-bottom:8px;color:#b91c1c">Unable to update password.</div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="change_student_password">
                                <input type="password" name="current_password" placeholder="Current password" required style="width:100%;padding:8px;margin-bottom:8px;border:1px solid #ddd;border-radius:6px;">
                                <input type="password" name="new_password" placeholder="New password" required style="width:100%;padding:8px;margin-bottom:8px;border:1px solid #ddd;border-radius:6px;">
                                <button type="submit" class="btn">Change</button>
                            </form>
                        </div>
                    </div>
                </li>

            <?php else: ?>
                <li class="nav-link"><a href="login.php">Login</a></li>
                <li class="nav-link"><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<style>
.avatar-dropdown-menu{display:none;position:absolute;right:0;top:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:12px;z-index:1000;min-width:260px;box-shadow:0 10px 20px rgba(0,0,0,0.08);}
.user-profile:hover .avatar-dropdown-menu,.user-profile:focus-within .avatar-dropdown-menu,.avatar-dropdown-menu.open{display:block}
</style>

<script>
// Avatar dropdown toggle + outside click close
(function(){
    const anchor = document.querySelector('.profile-anchor');
    const menu = document.getElementById('avatar-dropdown-menu');
    if (!anchor || !menu) return;

    anchor.addEventListener('click', (e) => {
        e.preventDefault();
        const open = menu.classList.toggle('open');
        anchor.setAttribute('aria-expanded', open.toString());
        menu.setAttribute('aria-hidden', (!open).toString());
    });

    // close on outside click
    document.addEventListener('click', (e) => {
        if (!menu || !anchor) return;
        if (menu.contains(e.target) || anchor.contains(e.target)) return;
        if (menu.classList.contains('open')) {
            menu.classList.remove('open');
            anchor.setAttribute('aria-expanded','false');
            menu.setAttribute('aria-hidden','true');
        }
    });

    // close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('open')) {
            menu.classList.remove('open');
            anchor.setAttribute('aria-expanded','false');
            menu.setAttribute('aria-hidden','true');
            anchor.focus();
        }
    });
})();
</script>