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

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_users.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to User List</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        Create New User
    </h1>

    <?php if ($msgSuccess): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= htmlspecialchars($msgSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if ($msgError): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
             <?= htmlspecialchars($msgError) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/pages/admin_create_user.php" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Full Name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($nome ?? '') ?>" required placeholder="e.g., Alexander the Great"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Username *</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required placeholder="e.g., alexander10"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Email Address *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required placeholder="e.g., alex@gymmaxxing.com"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="e.g., 910000000"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Temporary Password *</label>
            <input type="password" name="password" required placeholder="Minimum 6 characters"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Initial Gym Role *</label>
            <select name="role" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.95rem;">
                <option value="member" selected>Member</option>
                <option value="trainer">Trainer</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;">
            Register User
        </button>
    </form>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>