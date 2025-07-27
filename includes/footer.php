    </main>
    
    <!-- Include Footer CSS -->
    <link rel="stylesheet" href="/Plant-AI/assets/css/footer.css">
    
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Plant AI</h3>
                <p>Your smart plant care assistant powered by AI. Helping you keep your plants healthy and thriving.</p>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="Pinterest"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="/Plant-AI/about.php">About Us</a></li>
                    <li><a href="/Plant-AI/contact.php">Contact</a></li>
                    <li><a href="/Plant-AI/privacy.php">Privacy Policy</a></li>
                    <li><a href="/Plant-AI/terms.php">Terms of Service</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe to get the latest plant care tips and updates.</p>
                <form class="newsletter-form" action="/Plant-AI/subscribe.php" method="POST">
                    <input type="email" name="email" placeholder="Your email address" required>
                    <button type="submit" class="btn">Subscribe</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Plant AI. All rights reserved.</p>
        </div>
    </footer>
    <script>
        // Add active class to current nav item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = '<?php echo basename($_SERVER['PHP_SELF']); ?>';
            document.querySelectorAll('nav a').forEach(link => {
                if (link.getAttribute('href').includes(currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</div> <!-- Close .main-content -->
</body>
</html>
