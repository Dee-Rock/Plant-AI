<?php
require_once 'config/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';
$error = '';
$email = '';

// Process password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        // Validate email
        if (empty($email)) {
            $error = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                // Check if email exists
                $stmt = $pdo->prepare('SELECT id, username FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                // Always show success message, even if email doesn't exist (security best practice)
                $message = 'If an account exists with this email, you will receive a password reset link shortly.';
                
                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Store token in database
                    $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
                    $stmt->execute([$token, $expires, $user['id']]);
                    
                    // Create reset link
                    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
                    
                    // In a production environment, you would send an email here
                    // For development, we'll store the link in the session
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_link'] = $resetLink;
                }
            } catch (PDOException $e) {
                error_log('Password reset request error: ' . $e->getMessage());
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
}

// Generate CSRF token for the form
$csrf_token = generateCsrfToken();
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
    <div class="container" style="max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #2e7d32; text-align: center; margin-bottom: 20px;">Reset Your Password</h2>
        
        <?php if ($error): ?>
            <div class="message" style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="message" style="background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo $message; ?>
                
                <?php if (isset($_SESSION['reset_link'])): ?>
                    <div style="margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 4px; font-size: 13px; word-break: break-all;">
                        <p><strong>Development Notice:</strong> In a production environment, an email would be sent to the user.</p>
                        <p>Reset link: <a href="<?php echo $_SESSION['reset_link']; ?>" target="_blank"><?php echo $_SESSION['reset_link']; ?></a></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="reset-request-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; margin-bottom: 5px; font-weight: 500;">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo htmlspecialchars($email); ?>" 
                       required 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                       autocomplete="email"
                       autofocus>
            </div>
            
            <button type="submit" style="width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; transition: background-color 0.3s;">
                Send Reset Link
            </button>
            
            <div style="text-align: center; margin-top: 20px; font-size: 14px;">
                Remember your password? <a href="login.php" style="color: #2e7d32; text-decoration: none;">Sign In</a>
            </div>
        </form>
    </div>
</body>
</html>