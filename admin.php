<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lerno</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>

    <div class="admin-wrapper">

        <div class="sidebar" id="sidebar">
            <div class="logo-area">
                <h2>Lerno Admin</h2>
            </div>
            <ul class="side-nav">
                <li class="active"><a href="#">📊 Dashboard</a></li>
                <li><a href="#">🎓 Courses</a></li>
                <li><a href="#">👥 Students</a></li>
                <li><a href="#">💰 Revenue</a></li>
                <li><a href="#">⚙️ Settings</a></li>
                <li class="logout"><a href="index.html">🚪 Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            
            <div class="top-bar">
                <div class="toggle-btn" id="menu-toggle">☰</div>
                <div class="user-profile">
                    <span>Admin User</span>
                    <div class="avatar">A</div>
                </div>
            </div>

            <div class="dashboard-content">
                
                <h2 class="page-title">Dashboard Overview</h2>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3>Total Students</h3>
                            <p class="number">18,200</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎓</div>
                        <div class="stat-info">
                            <h3>Active Courses</h3>
                            <p class="number">120</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <h3>Total Revenue</h3>
                            <p class="number">$450,900</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⭐</div>
                        <div class="stat-info">
                            <h3>Avg Rating</h3>
                            <p class="number">4.9</p>
                        </div>
                    </div>
                </div>

                <div class="recent-orders">
                    <h3>Recent Enrollments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1023</td>
                                <td>Ali Nasser</td>
                                <td>Python Basics</td>
                                <td>Dec 12, 2025</td>
                                <td><span class="badge success">Paid</span></td>
                                <td>$89.99</td>
                            </tr>
                            <tr>
                                <td>#1024</td>
                                <td>Sara Ahmed</td>
                                <td>UI/UX Design</td>
                                <td>Dec 12, 2025</td>
                                <td><span class="badge success">Paid</span></td>
                                <td>$49.99</td>
                            </tr>
                            <tr>
                                <td>#1025</td>
                                <td>John Doe</td>
                                <td>C++ Masterclass</td>
                                <td>Dec 11, 2025</td>
                                <td><span class="badge warning">Pending</span></td>
                                <td>$200.00</td>
                            </tr>
                            <tr>
                                <td>#1026</td>
                                <td>Emily Smith</td>
                                <td>Data Science</td>
                                <td>Dec 11, 2025</td>
                                <td><span class="badge danger">Failed</span></td>
                                <td>$150.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</body>
</html>