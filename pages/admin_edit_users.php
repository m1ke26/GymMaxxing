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

$user = null;
$msgSuccess = null;
$msgError = null;

try {
    $db = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idToChange = (int)$_POST['id'];
        $newName = trim($_POST['name']);
        $newEmail = trim($_POST['email']);
        $newPhone = trim($_POST['phone']);
        $newRole = $_POST['role'];
        $newActive = (int)$_POST['active'];

        $currentUser = User::getUserById($db, $idToChange);

        if (!$currentUser) {
            $msgError = "User not found.";
        } elseif ($newName === '' || $newEmail === '') {
            $msgError = "Name and Email are mandatory fields.";
        } else {
            User::updateUser(
                $db,
                $idToChange,
                $newName,
                $currentUser->username,
                $newEmail,
                $newPhone !== '' ? $newPhone : null
            );

            User::updateRole($db, $idToChange, $newRole);

            $stmtActive = $db->prepare('UPDATE User SET active = ? WHERE id = ?');
            $stmtActive->execute([$newActive, $idToChange]);

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['photo']['tmp_path'];
                $fileName = $_FILES['photo']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = 'user_' . $idToChange . '_' . time() . '.' . $fileExtension;
                    $uploadFileDir = __DIR__ . '/../uploads/';

                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }

                    $destPath = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $stmtPhoto = $db->prepare('UPDATE User SET photo = ? WHERE id = ?');
                        $stmtPhoto->execute([$newFileName, $idToChange]);
                    }
                } else {
                    $msgError = "Invalid file extension. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
                }
            }

            if (!$msgError) {
                $msgSuccess = "User profile updated successfully using official methods!";
            }
        }
    }

    $userId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    $user = User::getUserById($db, $userId);

    if (!$user) {
        $msgError = "User not found.";
    }

} catch (Throwable $e) {
    if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
        $msgError = "Error: That email address is already being used by another account.";
    } else {
        $msgError = "System Error: " . $e->getMessage();
    }
}

$pageTitle = 'Edit User Profile';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-page admin-page--form">

    <div class="admin-back">
        <a href="/pages/admin_users.php">&larr; Back to User List</a>
    </div>

    <h1 class="admin-page-title">
         Edit Profile of @<?= htmlspecialchars($user ? $user->username : '') ?>
    </h1>

    <?php if ($msgSuccess): ?>
        <div class="admin-msg-success"><?= htmlspecialchars($msgSuccess) ?></div>
    <?php endif; ?>

    <?php if ($msgError): ?>
        <div class="admin-msg-error"><?= htmlspecialchars($msgError) ?></div>
    <?php endif; ?>

    <?php if ($user): ?>
        <form method="POST" action="/pages/admin_edit_users.php" enctype="multipart/form-data" class="admin-form-card">
            <input type="hidden" name="id" value="<?= (int)$user->id ?>">

            <div class="admin-field--md">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user->name) ?>" required>
            </div>

            <div class="admin-field--md">
                <label>Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>
            </div>

            <div class="admin-field--md">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>">
            </div>

            <div class="admin-field--md">
                <label>Profile Image</label>
                <div class="admin-file-wrap">
                    <input type="file" name="photo" id="photo-import" class="admin-file-hidden">
                    <label for="photo-import" class="admin-file-label">Import Profile Photo</label>
                    <small>Select a new JPG, PNG or WEBP image to replace the current system file.</small>
                </div>
            </div>

            <div class="admin-field--md">
                <label>Account Status *</label>
                <select name="active" class="select-lg">
                    <option value="1" <?= (int)$user->active === 1 ? 'selected' : '' ?>>Active Profile</option>
                    <option value="0" <?= (int)$user->active === 0 ? 'selected' : '' ?>>Deactivated Profile</option>
                </select>
            </div>

            <div class="admin-field--xl">
                <label>Gym Role *</label>
                <select name="role" class="select-lg select-bold">
                    <option value="member" <?= $user->role === 'member' ? 'selected' : '' ?>>Member</option>
                    <option value="trainer" <?= $user->role === 'trainer' ? 'selected' : '' ?>>Trainer</option>
                    <option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <small>Switching a profile to "Admin" provides full infrastructure control permissions.</small>
            </div>

            <button type="submit" class="admin-submit-btn">Save Changes</button>
        </form>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
