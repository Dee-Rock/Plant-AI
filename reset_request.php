<?php
require_once 'config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Simulate sending a reset link (in real app, send email)
            $reset_link = 'reset_password.php?email=' . urlencode($email);
            $message = 'A password reset link: <a href="' . $reset_link . '">' . $reset_link . '</a> (Simulated, check your email in a real app)';
        } else {
            $message = 'No account found with that email.';
        }
    } else {
        $message = 'Please enter your email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        <h2>Reset Password</h2>
        <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html> 