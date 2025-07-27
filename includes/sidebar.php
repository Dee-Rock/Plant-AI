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
        <ul class="top-menu">
            <!-- Main Navigation -->
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
                <a href="identify.php" class="<?php echo $current_page === 'identify.php' ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i> Identify Plant
                </a>
            </li>
            <li>
                <a href="identification.php" class="<?php echo ($current_page === 'identification.php' || $current_page === 'gallery.php') ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i> My Identifications
                </a>
            </li>
            <li>
                <a href="profile.php" class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="history.php" class="<?php echo $current_page === 'history.php' ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i> Scan History
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <!-- Bottom Menu (Logout) -->
        <ul class="bottom-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
