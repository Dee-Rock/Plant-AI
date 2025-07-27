<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'config.php';

// Function to generate CSRF token
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';
$error = '';
$validToken = false;
$token = $_GET['token'] ?? '';

// Validate token
if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    try {
        // Check if token is valid and not expired
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $validToken = true;
            $userId = $user['id'];
            
            // Process password reset form
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Verify CSRF token
                if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                    $error = 'Invalid request. Please try again.';
                } else {
                    $password = $_POST['password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';
                    
                    // Validate passwords
                    if (empty($password) || empty($confirmPassword)) {
                        $error = 'Please fill in all fields.';
                    } elseif (strlen($password) < 8) {
                        $error = 'Password must be at least 8 characters long.';
                    } elseif (!preg_match('/[A-Z]/', $password)) {
                        $error = 'Password must contain at least one uppercase letter.';
                    } elseif (!preg_match('/[a-z]/', $password)) {
                        $error = 'Password must contain at least one lowercase letter.';
                    } elseif (!preg_match('/[0-9]/', $password)) {
                        $error = 'Password must contain at least one number.';
                    } elseif ($password !== $confirmPassword) {
                        $error = 'Passwords do not match.';
                    } else {
                        try {
                            // Hash the new password
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Update password and clear reset token
                            $stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL, updated_at = NOW() WHERE id = ?');
                            $stmt->execute([$passwordHash, $userId]);
                            
                            // Set success message
                            $_SESSION['password_reset_success'] = 'Your password has been reset successfully. You can now log in with your new password.';
                            
                            // Redirect to login page
                            header('Location: login.php');
                            exit();
                            
                        } catch (PDOException $e) {
                            error_log('Password reset error: ' . $e->getMessage());
                            $error = 'An error occurred while resetting your password. Please try again.';
                        }
                    }
                }
            }
        } else {
            $error = 'Invalid or expired reset token. Please request a new password reset link.';
        }
    } catch (PDOException $e) {
        error_log('Token validation error: ' . $e->getMessage());
        $error = 'An error occurred while processing your request. Please try again.';
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
    <div class="container" style="max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #2e7d32; text-align: center; margin-bottom: 20px;">Set a New Password</h2>
        
        <?php if ($error): ?>
            <div class="message" style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['password_reset_success'])): ?>
            <div class="message" style="background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php 
                echo $_SESSION['password_reset_success'];
                unset($_SESSION['password_reset_success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($validToken): ?>
            <form method="post" id="resetPasswordForm" class="reset-password-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div style="margin-bottom: 20px;">
                    <label for="password" style="display: block; margin-bottom: 5px; font-weight: 500;">New Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="8"
                           autocomplete="new-password"
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                           oninput="checkPasswordStrength(this.value)">
                    <div class="password-strength" style="margin-top: 5px; height: 4px; background: #f0f0f0; border-radius: 2px; overflow: hidden;">
                        <div class="strength-meter" id="passwordStrength" style="height: 100%; width: 0%; transition: width 0.3s, background 0.3s;"></div>
                    </div>
                    <small class="form-text" style="display: block; margin-top: 5px; font-size: 12px; color: #666;">
                        Password must be at least 8 characters long and include uppercase, lowercase, and number.
                    </small>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: 500;">Confirm New Password</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required 
                           minlength="8"
                           autocomplete="new-password"
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                           oninput="checkPasswordsMatch()">
                    <small id="passwordMatch" style="display: block; margin-top: 5px; font-size: 12px; color: #666;"></small>
                </div>
                
                <button type="submit" id="submitBtn" style="width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; transition: background-color 0.3s;" disabled>
                    Reset Password
                </button>
                
                <div style="text-align: center; margin-top: 20px; font-size: 14px;">
                    Remember your password? <a href="login.php" style="color: #2e7d32; text-decoration: none;">Sign In</a>
                </div>
            </form>
            
            <script>
                function checkPasswordStrength(password) {
                    let strength = 0;
                    const strengthBar = document.getElementById('passwordStrength');
                    
                    // Check password length
                    if (password.length >= 8) strength += 20;
                    
                    // Check for uppercase letters
                    if (/[A-Z]/.test(password)) strength += 20;
                    
                    // Check for lowercase letters
                    if (/[a-z]/.test(password)) strength += 20;
                    
                    // Check for numbers
                    if (/[0-9]/.test(password)) strength += 20;
                    
                    // Check for special characters
                    if (/[^A-Za-z0-9]/.test(password)) strength += 20;
                    
                    // Update strength bar
                    strengthBar.style.width = strength + '%';
                    
                    // Update color based on strength
                    if (strength < 40) {
                        strengthBar.style.backgroundColor = '#f44336'; // Red
                    } else if (strength < 80) {
                        strengthBar.style.backgroundColor = '#ffc107'; // Yellow
                    } else {
                        strengthBar.style.backgroundColor = '#4caf50'; // Green
                    }
                    
                    // Check if passwords match
                    checkPasswordsMatch();
                }
                
                function checkPasswordsMatch() {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    const matchText = document.getElementById('passwordMatch');
                    const submitBtn = document.getElementById('submitBtn');
                    
                    if (password && confirmPassword) {
                        if (password === confirmPassword) {
                            matchText.textContent = 'Passwords match';
                            matchText.style.color = '#4caf50';
                            submitBtn.disabled = false;
                        } else {
                            matchText.textContent = 'Passwords do not match';
                            matchText.style.color = '#f44336';
                            submitBtn.disabled = true;
                        }
                    } else {
                        matchText.textContent = '';
                        submitBtn.disabled = true;
                    }
                }
            </script>
        <?php endif; ?>
    </div>
</body>
</html>