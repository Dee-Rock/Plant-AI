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
$username = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            try {
                // Prepare statement to prevent SQL injection
                $stmt = $pdo->prepare('SELECT id, username, password, email, is_active, login_attempts, last_login_attempt FROM users WHERE username = ? OR email = ? LIMIT 1');
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                // Check if user exists and account is active
                if ($user) {
                    // Check for too many failed attempts
                    if ($user['login_attempts'] >= 5 && strtotime($user['last_login_attempt']) > strtotime('-15 minutes')) {
                        $error = 'Too many failed login attempts. Please try again later.';
                    } else if (password_verify($password, $user['password'])) {
                        // Reset login attempts on successful login
                        $stmt = $pdo->prepare('UPDATE users SET login_attempts = 0, last_login_attempt = NULL, last_login = NOW() WHERE id = ?');
                        $stmt->execute([$user['id']]);
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['last_activity'] = time();
                        
                        // Set remember me cookie if requested
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expires = time() + (86400 * 30); // 30 days
                            setcookie('remember_token', $token, $expires, '/', '', true, true);
                            
                            // Store hashed token in database
                            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare('UPDATE users SET remember_token = ?, token_expires = ? WHERE id = ?');
                            $stmt->execute([$hashedToken, date('Y-m-d H:i:s', $expires), $user['id']]);
                        }
                        
                        // Redirect to intended page or home
                        $redirect = $_SESSION['redirect_url'] ?? 'index.php';
                        unset($_SESSION['redirect_url']);
                        header('Location: ' . $redirect);
                        exit();
                    } else {
                        // Increment failed login attempts
                        $stmt = $pdo->prepare('UPDATE users SET login_attempts = login_attempts + 1, last_login_attempt = NOW() WHERE id = ?');
                        $stmt->execute([$user['id']]);
                        $error = 'Invalid username or password.';
                    }
                } else {
                    // User doesn't exist
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                error_log('Login error: ' . $e->getMessage());
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
    $message = $error; // For backward compatibility
}

// Generate new CSRF token for the form
$csrf_token = generateCsrfToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $message = 'Invalid username or password.';
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
    <title>Login - Plant AI</title>
    <link rel="stylesheet" href="style.css">
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
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 1.5rem;
            color: #2e7d32;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .login-container button {
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
        .login-container button:hover {
            background-color: #1b5e20;
        }
        .login-links {
            margin-top: 1rem;
            font-size: 14px;
        }
        .login-links a {
            color: #2e7d32;
            text-decoration: none;
            margin: 0 5px;
        }
        .login-links a:hover {
            text-decoration: underline;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 1.5rem;
        }
        .message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/images/logo.png" alt="Plant AI Logo" class="logo">
        <h2>Welcome Back</h2>
        <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
        <form method="POST" action="login.php" class="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="text" 
                   name="username" 
                   placeholder="Username or Email" 
                   value="<?php echo htmlspecialchars($username); ?>" 
                   required 
                   autocomplete="username"
                   autofocus>
            <input type="password" 
                   name="password" 
                   placeholder="Password" 
                   required 
                   autocomplete="current-password">
            <div class="form-group remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn-login">Login</button>
            <div class="forgot-password">
                <a href="forgot-password.php">Forgot your password?</a>
            </div>
        </form>
        <div class="login-links">
            <a href="register.php">Create Account</a> | 
            <a href="reset_request.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>