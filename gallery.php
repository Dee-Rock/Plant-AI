<?php
require_once 'config.php';
session_start();

// Handle non-logged in users
if (!isset($_SESSION['user_id'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Identification History</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="assets/css/sidebar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <h2>Identification History</h2>
                <div class="message error">You must <a href="login.php">login</a> to view your identification history.</div>
            </div>
        </div>
        <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const main = document.querySelector('.main-content');
            
            // Make sure the sidebar is visible and positioned correctly
            if (sidebar) {
                sidebar.style.display = 'block';
                sidebar.style.left = '-250px';
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
    <?php
    exit;
}

// Get user's identifications
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM identifications WHERE user_id = ? ORDER BY identified_at DESC');
$stmt->execute([$userId]);
$identifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identification History</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gallery { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 1.5rem; 
            justify-content: center; 
            margin: 2rem 0;
        }
        .gallery-item { 
            background: #fff; 
            border-radius: 1rem; 
            box-shadow: 0 4px 16px rgba(0,0,0,0.08); 
            padding: 1.5rem; 
            width: 280px; 
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        .gallery-item img { 
            max-width: 100%; 
            border-radius: 0.5rem; 
            margin-bottom: 1rem;
            height: 200px;
            object-fit: cover;
        }
        .gallery-item .date { 
            color: #666; 
            font-size: 0.85em; 
            margin: 0.5rem 0;
        }
        .gallery-item .plant-name {
            font-size: 1.1em;
            font-weight: 600;
            color: #2c3e50;
            margin: 0.5rem 0;
        }
        .no-history {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h2><i class="fas fa-history"></i> Identification History</h2>
            <?php if (empty($identifications)): ?>
                <div class="no-history">
                    <p>You haven't identified any plants yet.</p>
                    <p><a href="index.php" class="button">Identify a Plant</a></p>
                </div>
            <?php else: ?>
                <div class="gallery">
                    <?php foreach ($identifications as $item): ?>
                        <div class="gallery-item">
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Identified plant">
                            <div class="date">Identified at: <?= htmlspecialchars($item['identified_at']) ?></div>
                            <div class="plant-name"><?= htmlspecialchars($item['plant_name'] ?? 'Unknown Plant') ?></div>
                            <div class="date">
                                <i class="far fa-calendar-alt"></i> 
                                <?= date('M j, Y', strtotime($item['identified_at'])) ?>
                                <span style="display: block; margin-top: 5px;">
                                    <i class="far fa-clock"></i> 
                                    <?= date('g:i A', strtotime($item['identified_at'])) ?>
                                </span>
                            </div>
                            <a href="identification.php?id=<?= $item['id'] ?>" class="button" style="display: inline-block; margin-top: 0.5rem;">
                                <i class="fas fa-search"></i> View Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <script>
        // Initialize sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const closeBtn = document.querySelector('.close-btn');
            const main = document.querySelector('.main-content');
            
            // Function to toggle sidebar
            function toggleSidebar() {
                if (!sidebar) return;
                
                const isOpen = sidebar.style.transform === 'translateX(0px)' || 
                              sidebar.style.transform === '';
                
                if (isOpen) {
                    sidebar.style.transform = 'translateX(-100%)';
                    if (main) main.classList.add('shift-right');
                } else {
                    sidebar.style.transform = 'translateX(0)';
                    if (main) main.classList.remove('shift-right');
                }
                
                // Toggle icons
                const barsIcon = document.querySelector('.sidebar-toggle .fa-bars');
                const timesIcon = document.querySelector('.sidebar-toggle .fa-times');
                
                if (barsIcon && timesIcon) {
                    barsIcon.style.display = isOpen ? 'none' : 'block';
                    timesIcon.style.display = isOpen ? 'block' : 'none';
                }
            }
            
            // Function to open sidebar
            function openSidebar() {
                if (sidebar) {
                    sidebar.style.transform = 'translateX(0)';
                    if (main) main.classList.remove('shift-right');
                    
                    // Update icons
                    const barsIcon = document.querySelector('.sidebar-toggle .fa-bars');
                    const timesIcon = document.querySelector('.sidebar-toggle .fa-times');
                    if (barsIcon && timesIcon) {
                        barsIcon.style.display = 'none';
                        timesIcon.style.display = 'block';
                    }
                }
            }
            
            // Function to close sidebar
            function closeSidebar() {
                if (sidebar) {
                    sidebar.style.transform = 'translateX(-100%)';
                    if (main) main.classList.add('shift-right');
                    
                    // Update icons
                    const barsIcon = document.querySelector('.sidebar-toggle .fa-bars');
                    const timesIcon = document.querySelector('.sidebar-toggle .fa-times');
                    if (barsIcon && timesIcon) {
                        barsIcon.style.display = 'block';
                        timesIcon.style.display = 'none';
                    }
                }
            }
            
            // Toggle sidebar when button is clicked
            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }
            
            // Close sidebar when close button is clicked
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeSidebar();
                });
            }
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== toggle && !sidebar.contains(e.target)) {
                    closeSidebar();
                }
            });
            
            // Close sidebar when pressing Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });
            
            // Initialize sidebar as open by default
            openSidebar();
        });
        </script>
    </div>
</body>
</html>