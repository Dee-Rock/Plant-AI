<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'plantapp');
define('DB_USER', 'root');  // Change this to your database username
define('DB_PASS', 'root');  // Change this to your database password

// Plant.id API Key
define('PLANT_ID_API_KEY', 'C2fuI6zKWRrywWILYy07xRJpYF6WPWl2bHrLjiUtuAREOuWfVw');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection function
function getDbConnection() {
    static $pdo;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the error and show a user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die("We're experiencing technical difficulties. Please try again later.");
        }
    }
    
    return $pdo;
}

// Function to redirect to another page
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('/Plant-AI/login.php');
    }
}

// Function to sanitize output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

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

// Function to set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Function to upload file
function uploadFile($file, $targetDir = UPLOAD_DIR) {
    // Create upload directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Get file info
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    
    // Check for errors
    if ($fileError !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $fileError);
    }
    
    // Check file size
    if ($fileSize > MAX_FILE_SIZE) {
        throw new Exception('File is too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
    }
    
    // Check file type
    if (!in_array($fileType, ALLOWED_FILE_TYPES)) {
        throw new Exception('Invalid file type. Only ' . implode(', ', ALLOWED_FILE_TYPES) . ' are allowed.');
    }
    
    // Generate unique filename
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('plant_') . '.' . $fileExt;
    $targetPath = $targetDir . $newFileName;
    
    // Move uploaded file
    if (!move_uploaded_file($fileTmpName, $targetPath)) {
        throw new Exception('Failed to move uploaded file.');
    }
    
    return [
        'name' => $newFileName,
        'original_name' => $fileName,
        'path' => $targetPath,
        'size' => $fileSize,
        'type' => $fileType
    ];
}
?>
