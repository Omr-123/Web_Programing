<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Pending - Lerno</title>
    <link rel="icon" href="assets/Lerno.png">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/approval.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/approval.js" defer></script>
</head>

<body>
    <?php include("components/navbar.php") ?>

    <div class="approval-wrapper">
        <div class="approval-card">
            <div class="icon-area">
                <svg class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <h1>Application Under Review</h1>
            
            <p class="message">
                Thank you for applying to become an instructor at Lerno! 
                <br><br>
                Our team is currently reviewing your profile and credentials. 
                This process usually takes <strong>24-48 hours</strong>. 
                You will receive an email notification once your account status is updated.
            </p>

            <div class="status-badge">
                <span class="dot"></span> Pending Approval
            </div>

            <a href="./" class="back-btn">Return to Home</a>
        </div>
    </div>

    <div class="footer">
        <p class="footer-description">2025 &copy; All Right Reserved By Lerno</p>
    </div>
</body>
</html>