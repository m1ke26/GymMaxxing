<?php
require_once __DIR__ . '/../utils/session.php';
startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /pages/homepage.php');
    exit();
}

$pageTitle = 'Login';

$errorMsg   = getFlash('error');
$successMsg = getFlash('success');

require_once __DIR__ . '/../templates/header.php';
?>

        <?php if ($errorMsg): ?>
            <div class="flash-error" style="max-width:900px;margin:20px auto 0;"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>
        <?php if ($successMsg): ?>
            <div class="flash-success" style="max-width:900px;margin:20px auto 0;"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>

        <div class="login-page-wrapper">
            <section class="auth-section">
                <h2>Login</h2>
                <form class="auth-form" action="/actions/login.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="email"    name="email"    placeholder="Email *"    autocomplete="email" required>
                    <div class="password-wrapper">
                        <input type="password" name="password" placeholder="Password *" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" aria-label="Show password"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                    </div>
                    <button type="submit">Login</button>
                </form>
                <p class="field-legend"><span>*</span> mandatory field</p>
            </section>

            <div class="auth-separator" aria-hidden="true"></div>
            <div class="auth-divider"   aria-hidden="true"></div>

            <section class="auth-section">
                <h2>Register</h2>
                <form class="auth-form" action="/actions/login.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="text"     name="name"     placeholder="Username *"     autocomplete="name" required>
                    <input type="email"    name="email"    placeholder="Email *"        autocomplete="email" required>
                    <div class="password-wrapper">
                        <input type="password" name="password" placeholder="Password *" autocomplete="new-password" required>
                        <button type="button" class="toggle-password" aria-label="Show password"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                    </div>
                    <input type="tel"      name="phone"    placeholder="Phone Number"   autocomplete="tel">
                    <button type="submit">Register</button>
                </form>
                <p class="field-legend"><span>*</span> mandatory field</p>
            </section>
        </div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
