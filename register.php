<?php
// Start session
session_start();

// Include database configuration
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => ''
];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $formData['username'] = trim($_POST['username'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate username
    if (empty($formData['username'])) {
        $errors['username'] = 'Username is required';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $formData['username'])) {
        $errors['username'] = 'Username must be 3-20 characters long and contain only letters, numbers, and underscores';
    }
    
    // Validate email
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If no validation errors, proceed with registration
    if (empty($errors)) {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$formData['username'], $formData['email']]);
            
            if ($stmt->fetch()) {
                $errors['general'] = 'Username or email already exists';
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, full_name, created_at) VALUES (?, ?, ?, ?, NOW())');
                $result = $stmt->execute([
                    $formData['username'],
                    $formData['email'],
                    $passwordHash,
                    !empty($formData['full_name']) ? $formData['full_name'] : null
                ]);
                
                if ($result) {
                    // Registration successful
                    $_SESSION['registration_success'] = true;
                    header('Location: login.php?registered=1');
                    exit();
                } else {
                    $errors['general'] = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
            $errors['general'] = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Plant AI</title>
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
        .register-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        .register-container h2 {
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
            padding: 12px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .form-group button:hover {
            background-color: #1b5e20;
        }
        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 5px;
        }
        .form-error {
            color: #d32f2f;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .password-requirements {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create an Account</h2>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="form-error"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($formData['username']); ?>" 
                       required
                       <?php echo isset($errors['username']) ? 'class="error-border"' : ''; ?>>
                <?php if (isset($errors['username'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['username']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($formData['email']); ?>" 
                       required
                       <?php echo isset($errors['email']) ? 'class="error-border"' : ''; ?>>
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['email']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?php echo htmlspecialchars($formData['full_name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" 
                       required
                       <?php echo isset($errors['password']) ? 'class="error-border"' : ''; ?>>
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php else: ?>
                    <div class="password-requirements">Must be at least 8 characters long</div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       required
                       <?php echo isset($errors['confirm_password']) ? 'class="error-border"' : ''; ?>>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit">Create Account</button>
            </div>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
</body>
</html>
