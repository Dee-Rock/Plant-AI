<?php
session_start();
require_once 'env.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Plant Identifier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">Plant AI</div>
            <div class="navbar-links">
                <a href="gallery.php" class="gallery-link">View Identification History</a>
                <a href="disease_detect.php" class="nav-link">Disease Detection</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">
        <h2>AI Plant Identifier</h2>
        <form action="identify.php" method="post" enctype="multipart/form-data">
            <input type="file" name="plant_image" accept="image/*" required>
            <button type="submit">Identify Plant</button>
        </form>
        <?php if (isset($_GET['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <div class="main-links">
            <!-- <a href="gallery.php">Identification History</a> -->
            <!-- <a href="disease_detect.php">Plant Disease Detection</a> -->
            <a href="comments.php" style="background:#ffb347; color:#333;">Community Comments</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Your Profile</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <a href="identification.php?id=1">Sample Identification Detail</a>
        </div>
    </div>
</body>
</html> 