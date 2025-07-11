<?php
require_once 'config.php';
session_start();
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: gallery.php');
    exit;
}
// Fetch identification
$stmt = $pdo->prepare('SELECT * FROM identifications WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: gallery.php');
    exit;
}
$plant = json_decode($row['result_json'], true);
// Handle new comment
$comment_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $comment_message = 'You must be logged in to comment.';
    } else {
        $comment = trim($_POST['comment']);
        if ($comment) {
            $stmt = $pdo->prepare('INSERT INTO comments (identification_id, user_id, comment) VALUES (?, ?, ?)');
            $stmt->execute([$id, $_SESSION['user_id'], $comment]);
        } else {
            $comment_message = 'Comment cannot be empty.';
        }
    }
}
// Fetch comments
$stmt = $pdo->prepare('SELECT c.*, u.username FROM comments c JOIN user u ON c.user_id = u.id WHERE c.identification_id = ? ORDER BY c.created_at DESC');
$stmt->execute([$id]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identification Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .comment-box { margin-top:2rem; text-align:left; }
        .comment { background:#f7f7f7; border-radius:0.5rem; padding:0.7rem 1rem; margin-bottom:1rem; }
        .comment .meta { color:#888; font-size:0.95em; margin-bottom:0.2rem; }
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
        <h2>Identification Details</h2>
        <?php if (file_exists($row['image_path'])): ?>
            <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Plant image" style="max-width:180px;border-radius:0.7rem;margin-bottom:1rem;" />
        <?php endif; ?>
        <div><strong>Plant Name:</strong> <?= htmlspecialchars($plant['plant_name'] ?? 'Unknown') ?></div>
        <div><strong>Scientific Name:</strong> <?= htmlspecialchars($plant['plant_details']['scientific_name'] ?? 'Unknown') ?></div>
        <div><strong>Identified At:</strong> <?= htmlspecialchars($row['identified_at']) ?></div>
        <?php if (!empty($plant['plant_details']['common_names'])): ?>
            <div><strong>Common Names:</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['common_names'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($plant['plant_details']['wiki_description']['value'])): ?>
            <div><strong>Description:</strong> <?= htmlspecialchars($plant['plant_details']['wiki_description']['value']) ?></div>
        <?php endif; ?>
        <!-- Comments Section -->
        <div class="comment-box">
            <h3>Comments</h3>
            <?php if ($comment_message): ?><div class="message error"><?= htmlspecialchars($comment_message) ?></div><?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="post" style="margin-bottom:1.2rem;">
                    <textarea name="comment" rows="2" style="width:100%;border-radius:0.5rem;padding:0.5rem;resize:vertical;" placeholder="Add a comment..."></textarea>
                    <button type="submit">Post Comment</button>
                </form>
            <?php else: ?>
                <div style="margin-bottom:1.2rem;">You must <a href="login.php">login</a> to comment.</div>
            <?php endif; ?>
            <?php foreach ($comments as $c): ?>
                <div class="comment">
                    <div class="meta"><strong><?= htmlspecialchars($c['username']) ?></strong> &middot; <?= htmlspecialchars($c['created_at']) ?></div>
                    <div><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($comments)): ?><div>No comments yet.</div><?php endif; ?>
        </div>
        <a href="gallery.php" style="display:inline-block;margin-top:2rem;">&larr; Back to History</a>
    </div>
</body>
</html> 