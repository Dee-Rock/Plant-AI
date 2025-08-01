<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant AI - Smart Plant Care Assistant</title>
    <link rel="stylesheet" href="/Plant-AI/style.css">
    <link rel="stylesheet" href="/Plant-AI/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
    <header>
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                    <i class="fas fa-leaf"></i> Plant AI
                </h1>
                <nav>
                    <ul style="display: flex; list-style: none; gap: 1.5rem;">
                        <li><a href="/Plant-AI/index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="/Plant-AI/disease_detect.php" class="<?php echo $current_page === 'disease_detect.php' ? 'active' : ''; ?>">Disease Detection</a></li>
                        <li><a href="/Plant-AI/identify.php" class="<?php echo $current_page === 'identify.php' ? 'active' : ''; ?>">Identify Plant</a></li>
                        <li><a href="/Plant-AI/identification.php" class="<?php echo $current_page === 'identification.php' ? 'active' : ''; ?>">My Identifications</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/Plant-AI/dashboard.php">My Dashboard</a></li>
                            <li><a href="/Plant-AI/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="/Plant-AI/login.php" class="btn btn-outline">Login</a></li>
                            <li><a href="/Plant-AI/register.php" class="btn">Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main class="container">
