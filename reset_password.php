<?php
require_once 'config.php';
$message = '';
$email = $_GET['email'] ?? '';
if (!$email) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE user SET password = ? WHERE email = ?');
        $stmt->execute([$hash, $email]);
        $message = 'Password reset successful! <a href="login.php">Login</a>';
    } else {
        $message = 'Please enter a new password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">Plant AI</div>
            <div class="navbar-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="gallery.php" class="gallery-link">History</a>
                <a href="disease_detect.php" class="nav-link">Disease Detection</a>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="gallery-link">Register</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h2>Set New Password</h2>
        <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
        <form method="post">
            <input type="password" name="password" placeholder="New Password" required>
            <button type="submit">Set Password</button>
        </form>
    </div>
</body>
</html> 