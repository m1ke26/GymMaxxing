    </main>

    <footer>
        <ul class="social-links">
            <li><a href="#"><img src="/images/facebook.png" alt="Facebook"></a></li>
            <li><a href="#"><img src="/images/instagram.png" alt="Instagram"></a></li>
            <li><a href="#"><img src="/images/linkedin.png" alt="LinkedIn"></a></li>
            <li><a href="#"><img src="/images/youtube.png" alt="YouTube"></a></li>
        </ul>
        <ul class="nav-links">
            <li><a href="/pages/classes.php">CLASSES</a></li>
            <li><a href="/pages/services.php">SERVICES</a></li>
            <li><a href="/pages/trainers.php">TRAINERS</a></li>
            <li><a href="/pages/joinus.php">JOIN US</a></li>
            <li><a href="/pages/contact.php">CONTACT US</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="/pages/equipment.php">EQUIPMENT</a></li>
                <?php if (hasRole('admin')): ?>
                    <li><a href="/pages/admin.php">ADMIN</a></li>
                <?php endif; ?>
                <li><a href="/pages/profile.php">PROFILE</a></li>
            <?php else: ?>
                <li><a href="/pages/login.php">LOGIN</a></li>
            <?php endif; ?>
        </ul>
        <p>&copy; 2026 Gymmaxxing</p>
    </footer>

    <script src="/javascript/script.js"></script>
</body>

</html>
