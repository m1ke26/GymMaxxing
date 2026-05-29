<?php
declare(strict_types = 1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../utils/session.php';
if (function_exists('startSession')) {
    startSession();
} else if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/user.class.php';

$msgSuccess = null;
$msgError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($nome === '' || $username === '' || $email === '' || $password === '') {
        $msgError = "Please fill in all mandatory fields marked with (*).";
    } else {
        try {
            $db = getDatabaseConnection();

            User::createUser(
                $db,
                $nome,
                $username,
                $email,
                $password,
                $phone !== '' ? $phone : null,
                $role
            );

            $msgSuccess = "User @{$username} created successfully using the official model!";
            $nome = $username = $email = $phone = '';

        } catch (Throwable $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $msgError = "Error: That Username or Email address is already being used by another account.";
            } else {
                $msgError = "Error creating user: " . $e->getMessage();
            }
        }
    }
}
unset($db);

$pageTitle = 'Create New User';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-page admin-page--form">

    <div class="admin-back">
        <a href="/pages/admin_users.php">&larr; Back to User List</a>
    </div>

    <h1 class="admin-page-title">Create New User</h1>

    <?php if ($msgSuccess): ?>
        <div class="admin-msg-success"><?= htmlspecialchars($msgSuccess) ?></div>
    <?php endif; ?>

    <?php if ($msgError): ?>
        <div class="admin-msg-error"><?= htmlspecialchars($msgError) ?></div>
    <?php endif; ?>

    <form method="POST" action="/pages/admin_create_user.php" class="admin-form-card">

        <div class="admin-field">
            <label>Full Name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($nome ?? '') ?>" required placeholder="e.g., Alexander the Great">
        </div>

        <div class="admin-field">
            <label>Username *</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required placeholder="e.g., alexander10">
        </div>

        <div class="admin-field">
            <label>Email Address *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required placeholder="e.g., alex@gymmaxxing.com">
        </div>

        <div class="admin-field">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="e.g., 910000000">
        </div>

        <div class="admin-field--md">
            <label>Temporary Password *</label>
            <input type="password" name="password" required placeholder="Minimum 6 characters">
        </div>

        <div class="admin-field--lg">
            <label>Initial Gym Role *</label>
            <select name="role" class="select-lg">
                <option value="member" selected>Member</option>
                <option value="trainer">Trainer</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="admin-submit-btn">Register User</button>
    </form>

</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
