<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant AI - Smart Plant Identification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">Plant<span>AI</span></div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#testimonials">Testimonials</a>
                <?php if ($isLoggedIn): ?>
                    <a href="home.php" class="btn btn-primary">Go to App</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Identify Any Plant in Seconds</h1>
                <p>Discover the power of AI to identify plants, diagnose diseases, and get expert care tips instantly.</p>
                <div class="cta-buttons">
                    <a href="<?= $isLoggedIn ? 'identify.php' : 'register.php' ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-camera"></i> Identify Now
                    </a>
                    <a href="#how-it-works" class="btn btn-outline btn-lg">
                        <i class="fas fa-play-circle"></i> Watch Demo
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://via.placeholder.com/600x400/81c784/ffffff?text=Plant+AI" alt="Plant Identification" loading="lazy">
            </div>
        </div>
        <div class="wave-shape">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0H1440V100C1193 100 1023 100 720 100C417 100 247 100 0 100V0Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">Powerful Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Plant Identification</h3>
                    <p>Identify thousands of plant species with our advanced AI technology.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3>Disease Detection</h3>
                    <p>Get instant diagnosis for plant diseases and treatment recommendations.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Care Guide</h3>
                    <p>Access detailed care instructions for each identified plant.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Take a Photo</h3>
                    <p>Snap a picture of any plant or upload an existing photo from your device.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>AI Analysis</h3>
                    <p>Our advanced AI will analyze the image and identify the plant species.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Get Results</h3>
                    <p>Receive detailed information and care instructions instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Users Say</h2>
            <div class="testimonial-slider">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"This app is a game-changer for plant lovers! I've identified over 50 plants in my garden."</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h4>Sarah Johnson</h4>
                                <span>Plant Enthusiast</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Identify Your Plants?</h2>
            <p>Join thousands of plant lovers who trust Plant AI for accurate plant identification and care.</p>
            <a href="<?= $isLoggedIn ? 'home.php' : 'register.php' ?>" class="btn btn-primary btn-lg">
                Get Started Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <h3>Plant AI</h3>
                    <p>Your smart plant care assistant powered by AI technology.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-newsletter">
                    <h4>Newsletter</h4>
                    <p>Subscribe for plant care tips and updates.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Plant AI. All rights reserved.</p>
                <div class="legal-links">
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn')?.addEventListener('click', function() {
            document.querySelector('.nav-links')?.classList.toggle('active');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
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