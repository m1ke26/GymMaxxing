<?php
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../utils/session.php';
}
startSession();
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gymmaxxing<?= isset($pageTitle) ? ' — ' . htmlspecialchars($pageTitle) : '' ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/css/specific.css">
    <link rel="icon" href="/images/gym_logo.png" type="image/png">
</head>

<body>
    <header>
        <nav>
            <ul class="nav-links">
                <li><a href="/pages/classes.php">CLASSES</a></li>
                <li><a href="/pages/services.php">SERVICES</a></li>
                <li><a href="/pages/trainers.php">TRAINERS</a></li>
                <li><a href="/pages/homepage.php"><img class="nav-logo" src="/images/gym_logo.png" alt="Gym Logo"></a></li>
                <li><a href="/pages/joinus.php">JOIN US</a></li>
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
            <ul class="social-links">
                <li><a href="#"><img src="/images/facebook.png" alt="Facebook"></a></li>
                <li><a href="#"><img src="/images/instagram.png" alt="Instagram"></a></li>
                <li><a href="#"><img src="/images/linkedin.png" alt="LinkedIn"></a></li>
                <li><a href="#"><img src="/images/youtube.png" alt="YouTube"></a></li>
            </ul>
        </nav>
    </header>

    <main>
