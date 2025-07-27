<?php
/**
 * Script to safely update authentication system
 * This script will:
 * 1. Create backups of old authentication files
 * 2. Remove old authentication files
 * 3. Rename new authentication files to standard names
 * 4. Update references in other files
 */

// Configuration
$backupDir = __DIR__ . '/backups/' . date('Y-m-d_His');
$filesToUpdate = [
    'login_new.php' => 'login.php',
    'register_new.php' => 'register.php',
    'reset_password.php' => 'reset_password.php',
    'forgot_password.php' => 'forgot_password.php'
];

// Create backup directory
if (!file_exists(dirname($backupDir))) {
    mkdir(dirname($backupDir), 0755, true);
}

// Function to safely copy files
function safeCopy($source, $dest) {
    if (!file_exists($source)) {
        return false;
    }
    return copy($source, $dest);
}

// Function to safely delete files
function safeDelete($file) {
    if (file_exists($file)) {
        return unlink($file);
    }
    return true;
}

// Function to update file references
function updateFileReferences($oldFile, $newFile) {
    $filesToCheck = [
        'index.php',
        'header.php',
        'footer.php',
        'includes/nav.php',
        'includes/header.php',
        'includes/footer.php'
    ];
    
    foreach ($filesToCheck as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $content = str_replace($oldFile, $newFile, $content, $count);
            if ($count > 0) {
                file_put_contents($file, $content);
                echo "Updated references in $file\n";
            }
        }
    }
}

// Main execution
echo "Starting authentication system update...\n";

// Create backup directory
if (!file_exists($backupDir) && !mkdir($backupDir, 0755, true)) {
    die("Error: Could not create backup directory: $backupDir\n");
}

echo "Created backup directory: $backupDir\n";

// Process each file
foreach ($filesToUpdate as $newFile => $oldFile) {
    $newFilePath = __DIR__ . '/' . $newFile;
    $oldFilePath = __DIR__ . '/' . $oldFile;
    $backupPath = $backupDir . '/' . $oldFile;
    
    // Skip if new file doesn't exist
    if (!file_exists($newFilePath)) {
        echo "Skipping $newFile - file not found\n";
        continue;
    }
    
    // Backup old file if it exists
    if (file_exists($oldFilePath)) {
        if (safeCopy($oldFilePath, $backupPath)) {
            echo "Backed up $oldFile to $backupPath\n";
        } else {
            echo "Warning: Could not back up $oldFile\n";
        }
        
        // Remove old file
        if (safeDelete($oldFilePath)) {
            echo "Removed old $oldFile\n";
        } else {
            echo "Warning: Could not remove $oldFile\n";
        }
    }
    
    // Rename new file to standard name
    if (rename($newFilePath, $oldFilePath)) {
        echo "Renamed $newFile to $oldFile\n";
        
        // Update references in other files
        updateFileReferences($newFile, $oldFile);
    } else {
        echo "Error: Could not rename $newFile to $oldFile\n";
    }
}

// Clean up any temporary files
$tempFiles = [
    'register_test.php',
    'register-success.php',
    'database/update_users_table.sql',
    'database/update_schema.sql'
];

foreach ($tempFiles as $tempFile) {
    $tempPath = __DIR__ . '/' . $tempFile;
    if (file_exists($tempPath)) {
        if (safeDelete($tempPath)) {
            echo "Removed temporary file: $tempFile\n";
        }
    }
}

echo "\nUpdate complete! Your authentication system has been updated.\n";
echo "Backups are available in: $backupDir\n";
echo "Please test the following pages to ensure everything works correctly:\n";
echo "- login.php\n";
echo "- register.php\n";
echo "- forgot-password.php (if applicable)\n";

// Verify the new login system
$loginFile = __DIR__ . '/login.php';
if (file_exists($loginFile)) {
    echo "\nTo test the new login system, visit: " . 
         str_replace($_SERVER['DOCUMENT_ROOT'], '', $loginFile) . "\n";
} else {
    echo "\nWarning: login.php was not created successfully.\n";
}
?>
