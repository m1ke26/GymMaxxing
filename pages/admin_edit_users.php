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

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_users.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to User List</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
         Edit Profile of @<?= htmlspecialchars($user ? $user->username : '') ?>
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

    <?php if ($user): ?>
        <form method="POST" action="/pages/admin_edit_users.php" enctype="multipart/form-data" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <input type="hidden" name="id" value="<?= (int)$user->id ?>">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Full Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user->name) ?>" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Profile Image</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <input type="file" name="photo" id="photo-import" style="display: none;">
                    <label sprintf="photo-import" for="photo-import" style="display: inline-block; text-align: center; background: #f4f4f5; border: 1px solid #e4e4e7; color: #18181b; padding: 10px 20px; border-radius: 4px; font-weight: bold; font-size: 0.9rem; cursor: pointer; transition: background 0.2s;">
                        Import Profile Photo
                    </label>
                    <small style="color: #666; display: block;">Select a new JPG, PNG or WEBP image to replace the current system file.</small>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Account Status *</label>
                <select name="active" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.95rem;">
                    <option value="1" <?= (int)$user->active === 1 ? 'selected' : '' ?>>Active Profile</option>
                    <option value="0" <?= (int)$user->active === 0 ? 'selected' : '' ?>>Deactivated Profile</option>
                </select>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Gym Role *</label>
                <select name="role" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.95rem; font-weight: bold;">
                    <option value="member" <?= $user->role === 'member' ? 'selected' : '' ?>>Member</option>
                    <option value="trainer" <?= $user->role === 'trainer' ? 'selected' : '' ?>>Trainer</option>
                    <option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <small style="color: #666; display: block; margin-top: 5px;">Switching a profile to "Admin" provides full infrastructure control permissions.</small>
            </div>

            <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;">
                Save Changes
            </button>
        </form>
    <?php endif; ?>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>