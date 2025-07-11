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
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">Plant AI</div>
            <div class="navbar-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="gallery.php" class="gallery-link">History</a>
                <a href="disease_detect.php" class="nav-link">Disease Detection</a>
            </div>
        </div>
    </nav>
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
</body>
</html> 