<?php
require_once 'config.php';
session_start();
$stmt = $pdo->query('SELECT c.*, u.username, i.id AS identification_id, i.plant_name, i.identified_at FROM comments c JOIN user u ON c.user_id = u.id JOIN identifications i ON c.identification_id = i.id ORDER BY c.created_at DESC LIMIT 50');
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Comments</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .comment-box { margin-top:2rem; text-align:left; }
        .comment { background:#f7f7f7; border-radius:0.5rem; padding:0.7rem 1rem; margin-bottom:1rem; }
        .comment .meta { color:#888; font-size:0.95em; margin-bottom:0.2rem; }
        .comment .plant-link { font-size:0.97em; color:#2193b0; text-decoration:underline; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">Plant AI</div>
            <div class="navbar-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="gallery.php" class="gallery-link">History</a>
                <a href="disease_detect.php" class="nav-link">Disease Detection</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="gallery-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">
        <h2>Community Comments</h2>
        <div class="comment-box">
            <?php foreach ($comments as $c): ?>
                <div class="comment">
                    <div class="meta">
                        <strong><?= htmlspecialchars($c['username']) ?></strong> &middot; <?= htmlspecialchars($c['created_at']) ?>
                        &middot; <a href="identification.php?id=<?= htmlspecialchars($c['identification_id']) ?>" class="plant-link">View Plant</a>
                    </div>
                    <div><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($comments)): ?><div>No comments yet.</div><?php endif; ?>
        </div>
    </div>
</body>
</html> 