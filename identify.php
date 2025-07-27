<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'config.php';

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 90);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$error = '';
$plant = null;

// Function to make API request to Plant.id
function identifyPlant($imagePath) {
    global $config;
    $apiKey = $config['plant_id_api_key'] ?? '';
    if (empty($apiKey)) {
        return ['error' => 'Plant.id API key not configured. Please contact the administrator.'];
    }

    // Check if file exists
    if (!file_exists($imagePath)) {
        return ['error' => 'Image file not found'];
    }

    // Prepare image data
    $imageData = base64_encode(file_get_contents($imagePath));
    
    $url = 'https://api.plant.id/v2/identify';
    $data = [
        'images' => [$imageData],
        'organs' => ['leaf', 'flower', 'fruit', 'bark'],
        'details' => [
            'common_names',
            'url',
            'wiki_description',
            'taxonomy',
            'watering',
            'sunlight',
            'toxicity'
        ],
    ];
    
    $headers = [
        'Content-Type: application/json',
        'Api-Key: ' . $apiKey
    ];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'API request failed: ' . $error];
    }
    
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid API response'];
    }
    
    if ($httpCode !== 200) {
        return ['error' => 'API error: ' . ($result['message'] ?? 'Unknown error')];
    }
    
    return $result;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['plant_image']) && $_FILES['plant_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = mime_content_type($_FILES['plant_image']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Only JPG and PNG files are allowed.';
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $fileName = uniqid('plant_') . '_' . basename($_FILES['plant_image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['plant_image']['tmp_name'], $targetPath)) {
                // Call Plant.id API
                $result = identifyPlant($targetPath);
                
                if (isset($result['error'])) {
                    $error = $result['error'];
                    @unlink($targetPath); // Remove uploaded file on error
                } else {
                    // Save to database
                    try {
                        // Use the existing PDO connection from config.php
                        global $pdo;
                        
                        $plantName = $result['suggestions'][0]['plant_name'] ?? 'Unknown Plant';
                        
                        // First, make sure the plant exists in the plants table or insert it
                        $stmt = $pdo->prepare("INSERT IGNORE INTO plants (common_name, scientific_name) VALUES (?, ?)");
                        $stmt->execute([$plantName, $result['suggestions'][0]['scientific_name'] ?? '']);
                        
                        // Get the plant ID
                        $plantId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM plants WHERE common_name = " . $pdo->quote($plantName) . " LIMIT 1")->fetchColumn();
                        
                        // Insert into plant_identifications
                        $stmt = $pdo->prepare("INSERT INTO plant_identifications (user_id, plant_id, image_path, confidence, identified_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([
                            $_SESSION['user_id'],
                            $plantId ?: null,
                            $targetPath,
                            $result['suggestions'][0]['probability'] ?? null
                        ]);
                        
                        $identificationId = $pdo->lastInsertId();
                        
                        // Redirect to identification result page
                        header("Location: identification.php?id=" . $identificationId);
                        exit();
                        
                    } catch (PDOException $e) {
                        $error = 'Database error: ' . $e->getMessage();
                        @unlink($targetPath); // Remove uploaded file on error
                    }
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    } else {
        $error = 'Please select an image file to upload.';
    }
}

// Include header and sidebar
$pageTitle = 'Identify Plant';
include 'includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-search"></i> Identify a Plant</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="upload-container">
                        <div class="text-center mb-4">
                            <i class="fas fa-leaf fa-5x text-success mb-3"></i>
                            <h3>Upload Plant Image</h3>
                            <p class="text-muted">Take a photo or upload an image of a plant to identify it</p>
                        </div>
                        
                        <form id="plantForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="form-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="plantImage" name="plant_image" accept="image/*" capture="camera" required>
                                    <label class="custom-file-label" for="plantImage">Choose file or take a photo</label>
                                    <div class="invalid-feedback">
                                        Please select an image file.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search"></i> Identify Plant
                                </button>
                            </div>
                        </form>
                        
                        <div class="image-preview mt-4 text-center" id="imagePreview" style="display: none;">
                            <img src="#" alt="Preview" class="img-thumbnail" id="previewImage" style="max-height: 300px;">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="tips-container p-4">
                        <h4><i class="fas fa-lightbulb text-warning"></i> Tips for Better Results</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Take clear, well-lit photos of leaves, flowers, or fruits</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Ensure the plant fills most of the frame</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Avoid blurry or distant shots</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Take multiple photos from different angles</li>
                        </ul>
                        
                        <div class="mt-4">
                            <h5>Recently Identified Plants</h5>
                            <div class="recent-plants">
                                <?php
                                try {
                                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    
                                    $stmt = $pdo->prepare("SELECT * FROM identifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $recentPlants = $stmt->fetchAll();
                                    
                                    if (count($recentPlants) > 0) {
                                        echo '<div class="row">';
                                        foreach ($recentPlants as $plant) {
                                            $plantData = json_decode($plant['result_json'], true);
                                            $plantName = $plantData['suggestions'][0]['plant_name'] ?? 'Unknown Plant';
                                            echo '<div class="col-4 mb-3">';
                                            echo '<a href="identification.php?id=' . $plant['id'] . '" class="text-decoration-none">';
                                            echo '<img src="' . htmlspecialchars($plant['image_path']) . '" class="img-fluid rounded" style="height: 80px; width: 100%; object-fit: cover;">';
                                            echo '<small class="d-block text-center text-dark mt-1">' . htmlspecialchars(substr($plantName, 0, 15)) . (strlen($plantName) > 15 ? '...' : '') . '</small>';
                                            echo '</a></div>';
                                        }
                                        echo '</div>';
                                    } else {
                                        echo '<p class="text-muted">No recent identifications found.</p>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<p class="text-muted">Unable to load recent plants.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-container {
    padding: 2rem;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    height: 100%;
}

.upload-container:hover {
    border-color: #28a745;
    background-color: #f1f8ff;
}

.tips-container {
    background-color: #f8f9fa;
    border-radius: 10px;
    height: 100%;
}

.custom-file-label::after {
    content: "Browse";
}

.card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    margin-bottom: 2rem;
}

.alert {
    border-radius: 8px;
    padding: 1rem 1.25rem;
}
</style>

<script>
// Show image preview when file is selected
document.getElementById('plantImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('previewImage');
            preview.src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
        document.querySelector('.custom-file-label').textContent = file.name;
    }
});

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
   
<?php
// Include header
include 'includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-search"></i> Identify a Plant</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($plant)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h2>Identification Results</h2>
            </div>
            <div class="card-body">
                <?php if (isset($plant['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($plant['error']) ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php if (!empty($plant['images'][0])): ?>
                                <img src="<?= htmlspecialchars($plant['images'][0]) ?>" class="img-fluid rounded" alt="Uploaded plant">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h3><?= htmlspecialchars($plant['plant_name'] ?? 'Unknown Plant') ?></h3>
                            <?php if (!empty($plant['plant_details']['common_names'])): ?>
                                <p><strong>Common Names:</strong> <?= htmlspecialchars(implode(', ', $plant['plant_details']['common_names'])) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($plant['plant_details']['scientific_name'])): ?>
                                <p><strong>Scientific Name:</strong> <em><?= htmlspecialchars($plant['plant_details']['scientific_name']) ?></em></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($plant['plant_details']['wiki_description'])): ?>
                                <div class="mt-3">
                                    <h5>Description:</h5>
                                    <p><?= nl2br(htmlspecialchars($plant['plant_details']['wiki_description'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($plant['plant_details']['watering'])): ?>
                                <div class="mt-3">
                                    <h5>Watering:</h5>
                                    <p><?= nl2br(htmlspecialchars($plant['plant_details']['watering'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($plant['plant_details']['sunlight'])): ?>
                                <div class="mt-3">
                                    <h5>Sunlight:</h5>
                                    <p><?= nl2br(htmlspecialchars($plant['plant_details']['sunlight'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="card mt-4">
        <div class="card-body">
            <h2>Identify a New Plant</h2>
            <form action="identify.php" method="post" enctype="multipart/form-data" class="mt-4">
                <div class="form-group">
                    <label for="plant_image">Upload Plant Image:</label>
                    <input type="file" class="form-control-file" id="plant_image" name="plant_image" accept="image/*" required>
                    <small class="form-text text-muted">Supported formats: JPG, PNG, JPEG</small>
                </div>
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-search"></i> Identify Plant
                </button>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
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