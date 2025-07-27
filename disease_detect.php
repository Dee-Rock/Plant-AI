<?php
$message = '';
$disease = null;
$care = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['plant_image'])) {
    $file = $_FILES['plant_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmpName = $file['tmp_name'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            $message = 'Only JPG, JPEG, PNG allowed.';
        } else {
            // Plant.id Disease Detection API
            $apiKey = 'C2fuI6zKWRrywWILYy07xRJpYF6WPWl2bHrLjiUtuAREOuWfVw';
            $url = 'https://api.plant.id/v2/health_assessment';
            $imageData = base64_encode(file_get_contents($tmpName));
            $data = [
                'images' => [$imageData],
                'modifiers' => ['crops_simple'],
                'plant_language' => 'en',
                'disease_details' => ['cause', 'description', 'treatment', 'common_names']
            ];
            $options = [
                'http' => [
                    'header'  => [
                        'Content-type: application/json',
                        'Api-Key: ' . $apiKey
                    ],
                    'method'  => 'POST',
                    'content' => json_encode($data),
                    'ignore_errors' => true
                ]
            ];
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $response = json_decode($result, true);
            if (!empty($response['health_assessment']['diseases'])) {
                $disease = $response['health_assessment']['diseases'][0]['name'] ?? null;
                $care = $response['health_assessment']['diseases'][0]['treatment'] ?? null;
            } else {
                $message = 'No disease detected or API error.';
            }
        }
    } else {
        $message = 'File upload error.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Disease Detection</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
    <div class="container">
        <h2>Plant Disease Detection</h2>
        <?php if ($message): ?><div class="message error"><?= $message ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="plant_image" accept="image/*" required>
            <button type="submit">Detect Disease</button>
        </form>
        <?php if ($disease): ?>
            <div style="margin-top:1.5rem;">
                <h3>Disease Detected:</h3>
                <p><strong><?= htmlspecialchars($disease) ?></strong></p>
                <?php if ($care): ?>
                    <h4>Care Tips:</h4>
                    <p><?= htmlspecialchars($care) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
    // Wait for the DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebarToggle');
        const main = document.querySelector('.main-content');
        
        // Make sure the sidebar is visible and positioned correctly
        if (sidebar) {
            sidebar.style.display = 'block';
            sidebar.style.left = '-250px'; // Start off-screen
        }
        
        if (toggle && sidebar && main) {
            // Toggle sidebar when button is clicked
            toggle.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (sidebar.style.left === '0px') {
                    sidebar.style.left = '-250px';
                    main.classList.remove('shift-right');
                } else {
                    sidebar.style.left = '0';
                    main.classList.add('shift-right');
                }
                return false;
            };
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== toggle && !sidebar.contains(e.target)) {
                    sidebar.style.left = '-250px';
                    main.classList.remove('shift-right');
                }
            });
        }
    });
    </script>
    
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