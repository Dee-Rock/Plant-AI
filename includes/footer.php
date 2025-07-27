    </main>
    
    <footer style="background-color: #2e7d32; color: white; padding: 2rem 0; margin-top: 3rem;">
        <div class="container" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
            <div class="footer-section">
                <h3 style="color: #fff; margin-bottom: 1rem;">Plant AI</h3>
                <p>Your smart plant care assistant powered by AI.</p>
                <div class="social-links" style="margin-top: 1rem; display: flex; gap: 1rem;">
                    <a href="#" style="color: white;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: white;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: white;"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3 style="color: #fff; margin-bottom: 1rem;">Quick Links</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><a href="/Plant-AI/about.php" style="color: #e0e0e0; text-decoration: none;">About Us</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/Plant-AI/contact.php" style="color: #e0e0e0; text-decoration: none;">Contact</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/Plant-AI/privacy.php" style="color: #e0e0e0; text-decoration: none;">Privacy Policy</a></li>
                    <li><a href="/Plant-AI/terms.php" style="color: #e0e0e0; text-decoration: none;">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3 style="color: #fff; margin-bottom: 1rem;">Newsletter</h3>
                <p>Subscribe for plant care tips and updates.</p>
                <form action="/Plant-AI/subscribe.php" method="POST" style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                    <input type="email" name="email" placeholder="Your email" required style="padding: 0.5rem; border: 1px solid #e0e0e0; border-radius: 4px; flex-grow: 1;">
                    <button type="submit" class="btn" style="white-space: nowrap;">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="container" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
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
