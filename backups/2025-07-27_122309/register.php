<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

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
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => ''
];

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log('Registration form submitted: ' . print_r($_POST, true));
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            throw new Exception('Invalid request. Please try again.');
        }
        // Sanitize and validate input
        $formData['username'] = trim($_POST['username'] ?? '');
        $formData['email'] = trim($_POST['email'] ?? '');
        $formData['full_name'] = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);
        
        // Validation
        $errors = [];
        
        // Username validation
        if (empty($formData['username'])) {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $formData['username'])) {
            $errors[] = 'Username must be 3-20 characters long and contain only letters, numbers, and underscores.';
        }
        
        // Email validation
        if (empty($formData['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Password validation
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        }
        
        // Terms acceptance
        if (!$terms) {
            $errors[] = 'You must accept the terms and conditions.';
        }
        
        // If no validation errors, proceed with registration
        if (empty($errors)) {
            // Check if username or email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            error_log('Checking for existing user with username: ' . $formData['username'] . ', email: ' . $formData['email']);
            $stmt->execute([$formData['username'], $formData['email']]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception('Username or email is already registered.');
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate verification token
                $verificationToken = bin2hex(random_bytes(32));
                $verificationExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Insert new user with verification fields
                // Matching the login page's expected schema
                $sql = 'INSERT INTO users (username, email, password, full_name, verification_token, verification_expires, is_verified, is_active, login_attempts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    $formData['username'],
                    $formData['email'],
                    $passwordHash,  // Stored in 'password' column (not password_hash)
                    !empty($formData['full_name']) ? $formData['full_name'] : null,
                    $verificationToken,
                    $verificationExpires,
                    0,  // is_verified
                    1,  // is_active (default to active)
                    0   // login_attempts (start at 0)
                ]);
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log('Database error: ' . print_r($errorInfo, true));
                    throw new PDOException('Registration failed. Please try again.');
                }
                
                // Log successful registration
                error_log('User registered successfully: ' . $formData['email']);
                
                // Store user data in session for success page
                $_SESSION['registered_email'] = $formData['email'];
                $_SESSION['verification_token'] = $verificationToken;
                
                // Redirect to success page
                header('Location: register-success.php');
                exit();
                    
        }
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        $error = $e->getMessage();
    }
        } else {
            $error = implode('<br>', $errors);
        }
    }
    $message = $error; // For backward compatibility
}

// Generate CSRF token for the form
$csrf_token = generateCsrfToken();
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
    <title>Register - Plant AI</title>
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
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .register-container h2 {
            margin-bottom: 1.5rem;
            color: #2e7d32;
        }
        .register-container input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .register-container button {
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
        .register-container button:hover {
            background-color: #1b5e20;
        }
        .register-links {
            margin-top: 1rem;
            font-size: 14px;
        }
        .register-links a {
            color: #2e7d32;
            text-decoration: none;
            margin: 0 5px;
        }
        .register-links a:hover {
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
        .message a {
            color: #2e7d32;
            text-decoration: none;
        }
        .message a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <img src="assets/images/logo.png" alt="Plant AI Logo" class="logo">
        <h2>Create Account</h2>
        <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
        <form method="post" class="registration-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <input type="text" 
                       name="username" 
                       placeholder="Username" 
                       value="<?php echo htmlspecialchars($formData['username']); ?>" 
                       required
                       autocomplete="username"
                       autofocus>
                <small class="form-text">3-20 characters, letters, numbers, and underscores only</small>
            </div>
            
            <div class="form-group">
                <input type="email" 
                       name="email" 
                       placeholder="Email" 
                       value="<?php echo htmlspecialchars($formData['email']); ?>" 
                       required
                       autocomplete="email">
            </div>
            
            <div class="form-group">
                <input type="text" 
                       name="full_name" 
                       placeholder="Full Name (Optional)" 
                       value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                       autocomplete="name">
            </div>
            
            <div class="form-group">
                <input type="password" 
                       name="password" 
                       placeholder="Password" 
                       required
                       autocomplete="new-password"
                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                       title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">
                <small class="form-text">At least 8 characters with uppercase, lowercase, and number</small>
            </div>
            
            <div class="form-group">
                <input type="password" 
                       name="confirm_password" 
                       placeholder="Confirm Password" 
                       required
                       autocomplete="new-password">
            </div>
            <div class="form-group terms">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
            </div>
            
            <button type="submit" class="btn-register">Create Account</button>
            
            <div class="already-have-account">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </form>
        <div class="social-login">
            <div class="divider">
                <span>or sign up with</span>
            </div>
            <div class="social-buttons">
                <a href="#" class="social-btn google">
                    <i class="fab fa-google"></i> Google
                </a>
                <a href="#" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
            </div>
        </div>
    </div>
</body>
</html>