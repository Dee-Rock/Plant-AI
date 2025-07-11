<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $email && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO user (username, email, password) VALUES (?, ?, ?)');
        try {
            $stmt->execute([$username, $email, $hash]);
            $message = 'Registration successful! You can now <a href="login.php">login</a>.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Username or email already exists.';
            } else {
                $message = 'Registration failed: ' . $e->getMessage();
            }
        }
    } else {
        $message = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        <h2>Register</h2>
        <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html> 