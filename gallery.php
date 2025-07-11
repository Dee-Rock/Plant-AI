<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Identification History</title><link rel="stylesheet" href="style.css"></head><body><nav class="navbar"><div class="navbar-content"><div class="navbar-title">Plant AI</div><div class="navbar-links"><a href="index.php" class="nav-link">Home</a><a href="gallery.php" class="gallery-link">History</a><a href="disease_detect.php" class="nav-link">Disease Detection</a><a href="login.php" class="nav-link">Login</a><a href="register.php" class="gallery-link">Register</a></div></div></nav><div class="container"><h2>Identification History</h2><div class="message error">You must <a href="login.php">login</a> to view your identification history.</div></div></body></html>';
    exit;
}
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
    <style>
        .gallery { display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: center; }
        .gallery-item { background: #fff; border-radius: 1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 1rem; width: 260px; text-align: center; }
        .gallery-item img { max-width: 100%; border-radius: 0.5rem; margin-bottom: 0.5rem; }
        .gallery-item .date { color: #888; font-size: 0.9em; margin-bottom: 0.5rem; }
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
        <h2>Identification History</h2>
        <div class="gallery">
            <?php foreach ($identifications as $item): ?>
                <div class="gallery-item">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Plant image">
                    <div class="date">Identified at: <?= htmlspecialchars($item['identified_at']) ?></div>
                    <div><strong><?= htmlspecialchars($item['plant_name']) ?></strong></div>
                    <div><em><?= htmlspecialchars($item['scientific_name']) ?></em></div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="index.php">Back to Identification</a>
    </div>
</body>
</html> 