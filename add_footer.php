<?php
// List of main pages that need the footer
$pages = [
    'index.php',
    'disease_detect.php',
    'gallery.php',
    'identification.php',
    'identify.php',
    'login.php',
    'profile.php',
    'register.php',
    'reset_password.php',
    'reset_request.php'
];

$footerSnippet = '<?php include \'includes/footer_include.php\'; ?>';

foreach ($pages as $page) {
    $filePath = __DIR__ . '/' . $page;
    
    // Skip if file doesn't exist
    (!file_exists($filePath)) {
        echo "Skipping $page - File not found.\n";
        continue;
    }
    
    // Read the file content
    $content = file_get_contents($filePath);
    
    // Check if footer is already included
    if (strpos($content, 'footer_include.php') !== false) {
        echo "Skipping $page - Footer already exists.\n";
        continue;
    }
    
    // Find the closing body and html tags
    if (preg_match('/<\/body>.*<\/html>\s*$/is', $content)) {
        // Remove any existing closing body/html tags
        $content = preg_replace('/<\/body>.*<\/html>\s*$/is', '', $content);
        // Add our footer include
        $content .= "\n$footerSnippet\n";
        
        // Write the content back to the file
        if (file_put_contents($filePath, $content) !== false) {
            echo "Added footer to $page\n";
        } else {
            echo "Failed to update $page\n";
        }
    } else {
        // If no closing tags found, just append the footer
        if (file_put_contents($filePath, "\n$footerSnippet\n", FILE_APPEND) !== false) {
            echo "Appended footer to $page (no closing tags found)\n";
        } else {
            echo "Failed to append to $page\n";
        }
    }
}

echo "\nFooter addition complete!\n";
?>
