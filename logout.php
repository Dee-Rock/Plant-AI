<?php
require_once 'config/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Clear all session data
$_SESSION = [];

// Clear the remember me token if it exists
if (isset($_COOKIE['remember_token'])) {
    // Delete the remember me token from the database
    $token = $_COOKIE['remember_token'];
    if (!empty($token)) {
        try {
            $stmt = $pdo->prepare('UPDATE users SET remember_token = NULL, token_expires = NULL WHERE remember_token = ?');
            $stmt->execute([$token]);
        } catch (PDOException $e) {
            error_log('Error clearing remember token: ' . $e->getMessage());
        }
    }
    
    // Clear the remember me cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Clear session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to login page with a success message
header('Location: login.php');
exit();