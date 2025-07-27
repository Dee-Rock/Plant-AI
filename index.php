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
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php 
    echo '<!-- Including sidebar from: ' . __DIR__ . '/includes/sidebar.php -->';
    if (file_exists(__DIR__ . '/includes/sidebar.php')) {
        include 'includes/sidebar.php';
        echo '<!-- Sidebar included successfully -->';
    } else {
        echo '<!-- Error: Sidebar file not found -->';
    }
    ?>
    <div class="main-content">
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
    <script>
    // Wait for the DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebarToggle');
        const main = document.querySelector('.main-content');
        
        // Make sure the sidebar is visible and positioned correctly
        if (sidebar) {
            sidebar.style.display = 'block';
            sidebar.style.left = '-250px'; // Start off-screen
        }
        
        if (toggle && sidebar && main) {
            // Toggle sidebar when button is clicked
            toggle.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (sidebar.style.left === '0px') {
                    sidebar.style.left = '-250px';
                    main.classList.remove('shift-right');
                } else {
                    sidebar.style.left = '0';
                    main.classList.add('shift-right');
                }
                return false;
            };
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== toggle && !sidebar.contains(e.target)) {
                    sidebar.style.left = '-250px';
                    main.classList.remove('shift-right');
                }
            });
        }
    });
    </script>
</body>
</html>