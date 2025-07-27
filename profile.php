<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];
$message = '';

// Fetch current user info
$stmt = $pdo->prepare('SELECT username, email, profile_photo FROM user WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $profilePhotoPath = $user['profile_photo'] ?? null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed)) {
            $uploadsDir = 'uploads/';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }
            $uniqueName = 'profile_' . $userId . '_' . uniqid() . '.' . $ext;
            $destPath = $uploadsDir . $uniqueName;
            move_uploaded_file($file['tmp_name'], $destPath);
            $profilePhotoPath = $destPath;
        } else {
            $message = 'Only JPG, JPEG, PNG allowed for profile photo.';
        }
    }
    if ($username && $email) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE user SET username = ?, email = ?, password = ?, profile_photo = ? WHERE id = ?');
            $stmt->execute([$username, $email, $hash, $profilePhotoPath, $userId]);
        } else {
            $stmt = $pdo->prepare('UPDATE user SET username = ?, email = ?, profile_photo = ? WHERE id = ?');
            $stmt->execute([$username, $email, $profilePhotoPath, $userId]);
        }
        $_SESSION['username'] = $username;
        $message = 'Profile updated successfully!';
        // Refresh user info
        $user['username'] = $username;
        $user['email'] = $email;
        $user['profile_photo'] = $profilePhotoPath;
    } else {
        $message = 'Username and email cannot be empty.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-photo {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 2px solid #6dd5ed;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">Plant AI</div>
            <div class="navbar-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="gallery.php" class="gallery-link">History</a>
                <a href="disease_detect.php" class="nav-link">Disease Detection</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h2>User Profile</h2>
        <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
        <?php if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
            <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo" class="profile-photo">
        <?php else: ?>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6dd5ed&color=fff&size=90" alt="Profile Photo" class="profile-photo">
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="profile_photo" accept="image/*">
            <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($user['username']) ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
            <button type="submit">Update Profile</button>
        </form>
    </div>
    
    <!-- Include Footer -->
    <?php include 'includes/footer_include.php'; ?>
    
    <script>
    // Make sure footer is at the bottom of the page
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;
        const footer = document.querySelector('footer');
        
        function adjustFooter() {
            const bodyHeight = body.offsetHeight;
            const windowHeight = window.innerHeight;
            
            if (bodyHeight < windowHeight && footer) {
                footer.style.position = 'fixed';
                footer.style.bottom = '0';
                footer.style.left = '0';
                footer.style.right = '0';
            } else if (footer) {
                footer.style.position = 'relative';
            }
        }
        
        // Run on load and on resize
        adjustFooter();
        window.addEventListener('resize', adjustFooter);
    });
    </script>
</body>
</html>