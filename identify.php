<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 90);
require_once 'env.php';
require_once 'config.php';

function curl_post_json($url, $data, $headers = [], $timeout = 20) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // TEMPORARY: Disable SSL verification
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        file_put_contents('plantid_curl_error.txt', $error);
    }
    curl_close($ch);
    return $response;
}

function curl_get($url, $timeout = 20) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // TEMPORARY: Disable SSL verification
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function callPlantIdAPI($imagePath) {
    $apiKey = $_ENV['PLANT_ID_API_KEY'] ?? '';
    if (!$apiKey) return ['error' => 'API key not set.'];
    $url = 'https://api.plant.id/v2/identify';
    $imageData = base64_encode(file_get_contents($imagePath));
    $data = [
        'images' => [$imageData],
        'organs' => ['leaf', 'flower', 'fruit', 'bark'],
        'details' => ['common_names', 'url', 'name_authority', 'wiki_description', 'taxonomy', 'synonyms', 'edible_parts', 'propagation_methods', 'watering', 'sunlight', 'toxicity'],
    ];
    $headers = [
        'Content-type: application/json',
        'Api-Key: ' . $apiKey
    ];
    $result = curl_post_json($url, $data, $headers);
    return json_decode($result, true);
}

function fetchWikipediaSummary($scientificName) {
    $url = 'https://en.wikipedia.org/api/rest_v1/page/summary/' . urlencode($scientificName);
    $response = curl_get($url);
    if ($response === false) return null;
    $data = json_decode($response, true);
    if (isset($data['extract'])) return $data['extract'];
    return null;
}

function fetchWikipediaImages($scientificName) {
    $url = 'https://en.wikipedia.org/w/api.php?action=query&titles=' . urlencode($scientificName) . '&prop=images&format=json&imlimit=10&origin=*';
    $response = curl_get($url);
    if ($response === false) return [];
    $data = json_decode($response, true);
    $images = [];
    if (isset($data['query']['pages'])) {
        foreach ($data['query']['pages'] as $page) {
            if (isset($page['images'])) {
                foreach ($page['images'] as $img) {
                    $imgTitle = $img['title'];
                    if (preg_match('/\.(jpg|jpeg|png)$/i', $imgTitle)) {
                        $images[] = $imgTitle;
                    }
                }
            }
        }
    }
    // Get image URLs
    $urls = [];
    foreach ($images as $imgTitle) {
        $imgInfoUrl = 'https://en.wikipedia.org/w/api.php?action=query&titles=' . urlencode($imgTitle) . '&prop=imageinfo&iiprop=url&format=json&origin=*';
        $imgInfoResp = curl_get($imgInfoUrl);
        if ($imgInfoResp !== false) {
            $imgInfoData = json_decode($imgInfoResp, true);
            foreach ($imgInfoData['query']['pages'] as $imgPage) {
                if (isset($imgPage['imageinfo'][0]['url'])) {
                    $urls[] = $imgPage['imageinfo'][0]['url'];
                }
            }
        }
    }
    return $urls;
}

function getCurrentDateTime() {
    return date('Y-m-d H:i');
}
if (isset($_GET['download_pdf']) && isset($_GET['id'])) {
    require_once('fpdf/fpdf.php');
    require_once 'config.php';
    $stmt = $pdo->prepare('SELECT * FROM identifications WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $row = $stmt->fetch();
    if ($row) {
        $plant = json_decode($row['result_json'], true);
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Plant Identification Result',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Ln(5);
        if (file_exists($row['image_path'])) {
            $pdf->Image($row['image_path'],null,null,60,60);
            $pdf->Ln(65);
        }
        $pdf->Cell(0,10,'Plant Name: ' . ($plant['plant_name'] ?? 'Unknown'),0,1);
        $pdf->Cell(0,10,'Scientific Name: ' . ($plant['plant_details']['scientific_name'] ?? 'Unknown'),0,1);
        $pdf->Cell(0,10,'Identified At: ' . $row['identified_at'],0,1);
        if (!empty($plant['plant_details']['common_names']))
            $pdf->MultiCell(0,8,'Common Names: ' . implode(', ', $plant['plant_details']['common_names']));
        if (!empty($plant['plant_details']['wiki_description']['value']))
            $pdf->MultiCell(0,8,'Description: ' . $plant['plant_details']['wiki_description']['value']);
        if (!empty($plant['plant_details']['watering']))
            $pdf->MultiCell(0,8,'Watering: ' . implode(', ', $plant['plant_details']['watering']));
        if (!empty($plant['plant_details']['sunlight']))
            $pdf->MultiCell(0,8,'Sunlight: ' . implode(', ', $plant['plant_details']['sunlight']));
        if (!empty($plant['plant_details']['toxicity']))
            $pdf->MultiCell(0,8,'Toxicity: ' . implode(', ', $plant['plant_details']['toxicity']));
        if (!empty($plant['plant_details']['propagation_methods']))
            $pdf->MultiCell(0,8,'Propagation Methods: ' . implode(', ', $plant['plant_details']['propagation_methods']));
        if (!empty($plant['plant_details']['edible_parts']))
            $pdf->MultiCell(0,8,'Edible Parts: ' . implode(', ', $plant['plant_details']['edible_parts']));
        $pdf->Output('D','plant_identification.pdf');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['plant_image'])) {
    $file = $_FILES['plant_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmpName = $file['tmp_name'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            header('Location: index.php?error=Only JPG, JPEG, PNG allowed');
            exit;
        }
        // Save uploaded image to uploads/ directory
        $uploadsDir = 'uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        $uniqueName = 'plant_' . uniqid() . '.' . $ext;
        $destPath = $uploadsDir . $uniqueName;
        move_uploaded_file($tmpName, $destPath);
        // Call Plant.id API
        $result = callPlantIdAPI($destPath);
        file_put_contents('plantid_debug.json', json_encode($result, JSON_PRETTY_PRINT));
        if (isset($result['error'])) {
            $error = $result['error'];
        } elseif (!empty($result['suggestions'])) {
            $plant = $result['suggestions'][0]; // Only the top suggestion
            // Save identification to database
            $userId = $_SESSION['user_id'] ?? null;
            $stmt = $pdo->prepare('INSERT INTO identifications (user_id, image_path, plant_name, scientific_name, result_json) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $userId,
                $destPath,
                $plant['plant_name'] ?? '',
                $plant['plant_details']['scientific_name'] ?? '',
                json_encode($plant)
            ]);
        } else {
            $error = 'No plant identified.';
        }
    } else {
        $error = 'File upload error.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['wiki']) && isset($_GET['name'])) {
    // AJAX handler for Wikipedia summary
    header('Content-Type: application/json');
    $summary = fetchWikipediaSummary($_GET['name']);
    echo json_encode(['summary' => $summary]);
    exit;
} else {
    header('Location: index.php?error=No file uploaded');
    exit;
}

// After saving identification, get its ID for comments
if (isset($stmt) && $stmt instanceof PDOStatement && empty($error)) {
    $identification_id = $pdo->lastInsertId();
} elseif (isset($row['id'])) {
    $identification_id = $row['id'];
} else {
    $identification_id = null;
}
// Handle new comment
$comment_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $comment_message = 'You must be logged in to comment.';
    } else {
        $comment = trim($_POST['comment']);
        if ($comment && $identification_id) {
            $stmt = $pdo->prepare('INSERT INTO comments (identification_id, user_id, comment) VALUES (?, ?, ?)');
            $stmt->execute([$identification_id, $_SESSION['user_id'], $comment]);
        } else {
            $comment_message = 'Comment cannot be empty.';
        }
    }
}
// Fetch comments for this identification
$comments = [];
if ($identification_id) {
    $stmt = $pdo->prepare('SELECT c.*, u.username FROM comments c JOIN user u ON c.user_id = u.id WHERE c.identification_id = ? ORDER BY c.created_at DESC');
    $stmt->execute([$identification_id]);
    $comments = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Identification Result</title>
    <link rel="stylesheet" href="style.css">
    <script>
    function fetchWikiDescription(scientificName, btn) {
        btn.disabled = true;
        btn.textContent = 'Loading...';
        fetch('identify.php?wiki=1&name=' + encodeURIComponent(scientificName))
            .then(response => response.json())
            .then(data => {
                if (data.summary) {
                    btn.parentElement.querySelector('.wiki-desc').textContent = data.summary;
                } else {
                    btn.parentElement.querySelector('.wiki-desc').textContent = 'No Wikipedia description found.';
                }
                btn.style.display = 'none';
            })
            .catch(() => {
                btn.parentElement.querySelector('.wiki-desc').textContent = 'Error fetching Wikipedia description.';
                btn.disabled = false;
                btn.textContent = 'Get Wikipedia Description';
            });
    }
    function loadWikiGallery(scientificName, btn) {
        btn.disabled = true;
        btn.textContent = 'Loading...';
        fetch('identify.php?wiki_images=1&name=' + encodeURIComponent(scientificName))
            .then(response => response.json())
            .then(data => {
                if (data.images && data.images.length > 0) {
                    let gallery = btn.parentElement.querySelector('.wiki-gallery');
                    gallery.innerHTML = '<h4>Image Gallery from Wikipedia</h4>' +
                        '<div style="display:flex;flex-wrap:wrap;gap:0.7rem;justify-content:center;">' +
                        data.images.map(url => `<img src="${url}" alt="Plant image" style="max-width:110px;max-height:110px;border-radius:0.5rem;object-fit:cover;" />`).join('') +
                        '</div>';
                } else {
                    btn.parentElement.querySelector('.wiki-gallery').textContent = 'No images found.';
                }
                btn.style.display = 'none';
            })
            .catch(() => {
                btn.parentElement.querySelector('.wiki-gallery').textContent = 'Error loading images.';
                btn.disabled = false;
                btn.textContent = 'Show Image Gallery';
            });
    }
    function shareResult(plantName) {
        const shareText = plantName ? `I just identified a plant: ${plantName}! Check it out:` : 'Check out this plant I identified!';
        const shareData = {
            title: document.title,
            text: shareText,
            url: window.location.href
        };
        if (navigator.share) {
            navigator.share(shareData).catch(() => {});
        } else {
            // Fallback: copy link to clipboard
            navigator.clipboard.writeText(`${shareText} ${window.location.href}`).then(function() {
                showToast('Link copied to clipboard!');
            }, function() {
                showToast('Could not copy link.');
            });
        }
    }
    function showToast(msg) {
        let toast = document.createElement('div');
        toast.textContent = msg;
        toast.style.position = 'fixed';
        toast.style.bottom = '2rem';
        toast.style.left = '50%';
        toast.style.transform = 'translateX(-50%)';
        toast.style.background = '#2193b0';
        toast.style.color = '#fff';
        toast.style.padding = '0.8rem 1.5rem';
        toast.style.borderRadius = '0.5rem';
        toast.style.fontSize = '1rem';
        toast.style.zIndex = 9999;
        document.body.appendChild(toast);
        setTimeout(() => { toast.remove(); }, 2200);
    }
    function shareTo(platform, plantName) {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(plantName ? `I just identified a plant: ${plantName}!` : 'Check out this plant I identified!');
        let shareUrl = '';
        if (platform === 'whatsapp') {
            shareUrl = `https://wa.me/?text=${text}%20${url}`;
        } else if (platform === 'facebook') {
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        } else if (platform === 'twitter') {
            shareUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}`;
        }
        window.open(shareUrl, '_blank');
    }
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">Plant AI</div>
            <div class="navbar-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="gallery.php" class="gallery-link">History</a>
                <a href="disease_detect.php" class="nav-link">Disease Detection</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">
        <h2>Plant Identification Results</h2>
        <?php if (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
            <a href="index.php">Try Again</a>
        <?php elseif (!empty($plant)): ?>
            <div class="plant-result" style="margin-bottom:2rem;">
                <h3><?= htmlspecialchars($plant['plant_name'] ?? 'Unknown') ?></h3>
                <?php if (!empty($plant['plant_details']['common_names'])): ?>
                    <p><strong>Common Name(s):</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['common_names'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['scientific_name'])): ?>
                    <p><strong>Scientific Name:</strong> <?= htmlspecialchars($plant['plant_details']['scientific_name']) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['structured_name']['genus'])): ?>
                    <p><strong>Genus:</strong> <?= htmlspecialchars($plant['plant_details']['structured_name']['genus']) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['structured_name']['species'])): ?>
                    <p><strong>Species:</strong> <?= htmlspecialchars($plant['plant_details']['structured_name']['species']) ?></p>
                <?php endif; ?>
                <p><strong>Probability:</strong> <?= isset($plant['probability']) ? round($plant['probability'] * 100, 2) . '%' : 'N/A' ?></p>
                <?php if (!empty($plant['plant_details']['wiki_description']['value'])): ?>
                    <p><?= htmlspecialchars($plant['plant_details']['wiki_description']['value']) ?></p>
                <?php else: ?>
                    <p class="wiki-desc"><em>No description available.</em></p>
                    <?php if (!empty($plant['plant_details']['scientific_name'])): ?>
                        <button onclick="fetchWikiDescription('<?= htmlspecialchars($plant['plant_details']['scientific_name'], ENT_QUOTES) ?>', this)">Get Wikipedia Description</button>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['watering'])): ?>
                    <p><strong>Watering:</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['watering'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['sunlight'])): ?>
                    <p><strong>Sunlight:</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['sunlight'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['toxicity'])): ?>
                    <p><strong>Toxicity:</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['toxicity'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['propagation_methods'])): ?>
                    <p><strong>Propagation Methods:</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['propagation_methods'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['edible_parts'])): ?>
                    <p><strong>Edible Parts:</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['edible_parts'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($plant['plant_details']['url'])): ?>
                    <p><a href="<?= htmlspecialchars($plant['plant_details']['url']) ?>" target="_blank">More Info</a></p>
                <?php endif; ?>
                <?php if (!empty($plant['similar_images'][0]['url'])): ?>
                    <img src="<?= htmlspecialchars($plant['similar_images'][0]['url']) ?>" alt="Plant image" style="max-width:100%;border-radius:0.5rem;margin-top:1rem;" />
                <?php elseif (!empty($plant['plant_details']['image_url'])): ?>
                    <img src="<?= htmlspecialchars($plant['plant_details']['image_url']) ?>" alt="Plant image" style="max-width:100%;border-radius:0.5rem;margin-top:1rem;" />
                <?php endif; ?>
                <?php if (empty($plant['plant_details']['common_names']) && empty($plant['plant_details']['wiki_description']['value']) && empty($plant['plant_details']['watering']) && empty($plant['plant_details']['sunlight']) && empty($plant['plant_details']['toxicity']) && empty($plant['plant_details']['propagation_methods']) && empty($plant['plant_details']['edible_parts'])): ?>
                    <p><em>No additional information available for this plant.</em></p>
                <?php endif; ?>
                <?php if (!empty($identification_id)): ?>
                    <div style="margin:1.2rem 0;">
                        <a href="identification.php?id=<?= htmlspecialchars($identification_id) ?>" style="background:#6dd5ed;color:#fff;padding:0.7rem 1.3rem;border:none;border-radius:0.5rem;font-size:1rem;cursor:pointer;text-decoration:none;display:inline-block;text-align:center;">View Full Identification Details & Comments</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            // Show Wikipedia image gallery if scientific name is available
            $wikiImages = [];
            if (!empty($plant['plant_details']['scientific_name'])) {
                $wikiImages = fetchWikipediaImages($plant['plant_details']['scientific_name']);
            }
            ?>
            <?php if (!empty($wikiImages)): ?>
                <div style="margin-top:1.5rem;">
                    <h4>Image Gallery from Wikipedia</h4>
                    <div style="display:flex;flex-wrap:wrap;gap:0.7rem;justify-content:center;">
                        <?php foreach ($wikiImages as $imgUrl): ?>
                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Plant image" style="max-width:110px;max-height:110px;border-radius:0.5rem;object-fit:cover;" />
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div style="margin:1.5rem 0;display:flex;flex-direction:column;align-items:center;gap:0.7rem;">
                <button onclick="shareResult('<?= htmlspecialchars($plant['plant_name'] ?? '', ENT_QUOTES) ?>')" style="background:#2193b0;color:#fff;padding:0.7rem 1.3rem;border:none;border-radius:0.5rem;font-size:1rem;cursor:pointer;">Share</button>
                <a href="identify.php?download_pdf=1&id=<?= htmlspecialchars($row['id'] ?? '') ?>" style="background:#4caf50;color:#fff;padding:0.7rem 1.3rem;border:none;border-radius:0.5rem;font-size:1rem;cursor:pointer;text-decoration:none;display:inline-block;text-align:center;">Download PDF</a>
                <div style="display:flex;gap:0.7rem;justify-content:center;">
                    <button onclick="shareTo('whatsapp', '<?= htmlspecialchars($plant['plant_name'] ?? '', ENT_QUOTES) ?>')" style="background:#25d366;color:#fff;padding:0.5rem 1rem;border:none;border-radius:0.5rem;font-size:1rem;cursor:pointer;">WhatsApp</button>
                    <button onclick="shareTo('facebook', '<?= htmlspecialchars($plant['plant_name'] ?? '', ENT_QUOTES) ?>')" style="background:#4267B2;color:#fff;padding:0.5rem 1rem;border:none;border-radius:0.5rem;font-size:1rem;cursor:pointer;">Facebook</button>
                    <button onclick="shareTo('twitter', '<?= htmlspecialchars($plant['plant_name'] ?? '', ENT_QUOTES) ?>')" style="background:#1da1f2;color:#fff;padding:0.5rem 1rem;border:none;border-radius:0.5rem;font-size:1rem;cursor:pointer;">Twitter</button>
                </div>
            </div>
            <a href="index.php">Identify Another Plant</a>
        <?php endif; ?>
    </div>
</body>
</html> 