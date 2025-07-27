<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include database configuration
require_once 'config.php';

// Test database connection
try {
    $pdo->query('SELECT 1');
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage() . '. Check your config.php settings.');
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$username = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Check user credentials
            $stmt = $pdo->prepare('SELECT id, username, password, email FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Update last login time
                $updateStmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
                $updateStmt->execute([$user['id']]);
                
                // Redirect to dashboard or home page
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username/email or password.';
            }
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'Login error: ' . $e->getMessage();
            // For debugging - remove in production
            $error .= '<br>Query: ' . $stmt->queryString;
            $error .= '<br>Params: ' . print_r([$username, $username], true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Plant AI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-group button:hover {
            background-color: #1b5e20;
        }
        .error {
            color: #d32f2f;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
        }
        .success {
            color: #2e7d32;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login to Plant AI</h2>
        
        <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
            <div class="success">Registration successful! Please log in.</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>
</body>
</html>
