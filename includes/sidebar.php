<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars toggle-icon"></i>
    <i class="fas fa-times toggle-icon" style="display: none;"></i>
</button>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-leaf"></i> Plant AI</h3>
        <button class="close-btn">&times;</button>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li>
                <a href="disease_detect.php" class="<?php echo $current_page === 'disease_detect.php' ? 'active' : ''; ?>">
                    <i class="fas fa-diagnoses"></i> Disease Detection
                </a>
            </li>
            <li>
                <a href="plant_database.php" class="<?php echo $current_page === 'plant_database.php' ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i> Plant Database
                </a>
            </li>
            <li>
                <a href="gallery.php" class="<?php echo $current_page === 'gallery.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> History
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="profile.php" class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                </li>
                <li>
                    <a href="history.php" class="<?php echo $current_page === 'history.php' ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i> Scan History
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="login.php" class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li>
                    <a href="register.php" class="<?php echo $current_page === 'register.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
