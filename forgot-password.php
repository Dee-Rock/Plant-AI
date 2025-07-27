<?php
require_once 'config/db_connect.php';

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
                
                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Store token in database
                    $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
                    $stmt->execute([$token, $expires, $user['id']]);
                    
                    // Create reset link
                    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
                    
                    // Send email with reset link
                    $to = $email;
                    $subject = 'Password Reset Request';
                    $message = "
                        <h2>Password Reset Request</h2>
                        <p>Hello " . htmlspecialchars($user['username']) . ",</p>
                        <p>We received a request to reset your password. Click the link below to set a new password:</p>
                        <p><a href='$resetLink'>$resetLink</a></p>
                        <p>This link will expire in 1 hour for security reasons.</p>
                        <p>If you didn't request this, please ignore this email and your password will remain unchanged.</p>
                    ";
                    
                    $headers = "From: no-reply@plantai.com\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    
                    // In production, uncomment the following line to send the email
                    // mail($to, $subject, $message, $headers);
                    
                    // For development, store the reset link in session
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_link'] = $resetLink;
                    
                    // Show success message (even if email doesn't exist to prevent user enumeration)
                    $message = 'If an account exists with that email, you will receive a password reset link.';
                } else {
                    // Don't reveal if the email exists or not (security best practice)
                    $message = 'If an account exists with that email, you will receive a password reset link.';
                }
            } catch (PDOException $e) {
                error_log('Password reset error: ' . $e->getMessage());
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
    <title>Forgot Password - Plant AI</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/plant-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .forgot-password-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .forgot-password-container h2 {
            margin-bottom: 1.5rem;
            color: #2e7d32;
        }
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn-reset {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-reset:hover {
            background-color: #1b5e20;
        }
        .back-to-login {
            margin-top: 1rem;
            font-size: 14px;
        }
        .back-to-login a {
            color: #2e7d32;
            text-decoration: none;
        }
        .back-to-login a:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-size: 14px;
        }
        .message.error {
            background-color: #ffebee;
            color: #c62828;
        }
        .message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <img src="assets/images/logo.png" alt="Plant AI Logo" class="logo">
        <h2>Forgot Your Password?</h2>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <p>Enter your email address and we'll send you a link to reset your password.</p>
        
        <form method="post" class="forgot-password-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo htmlspecialchars($email); ?>" 
                       required 
                       autocomplete="email"
                       autofocus>
            </div>
            
            <button type="submit" class="btn-reset">Send Reset Link</button>
        </form>
        
        <div class="back-to-login">
            Remember your password? <a href="login.php">Sign In</a>
        </div>
        
        <?php if (isset($_SESSION['reset_link'])): ?>
            <!-- For development purposes only - remove in production -->
            <div class="development-notice" style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-radius: 4px; font-size: 12px; text-align: left;">
                <p><strong>Development Notice:</strong> In a production environment, an email would be sent to the user.</p>
                <p>Reset link: <a href="<?php echo $_SESSION['reset_link']; ?>" target="_blank"><?php echo $_SESSION['reset_link']; ?></a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
