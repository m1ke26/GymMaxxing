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
require_once __DIR__ . '/../database/equipment.class.php';

$msgError = null;
$item = null;

try {
    $db = getDatabaseConnection();


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id          = (int)$_POST['id'];
        $name        = trim($_POST['name']);
        $type        = trim($_POST['type']);
        $status      = $_POST['status'];
        $description = trim($_POST['description']);


        if ($id <= 0 || $name === '' || $type === '') {
            $msgError = "Please fill in all mandatory fields marked with (*).";
        } else {

            Equipment::update($db, $id, $name, $type, $status, $description !== '' ? $description : null);
            

            header('Location: /pages/admin_equipment.php?sucesso=' . urlencode("Equipment updated successfully!"));
            exit();
        }
    }


    $itemId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    
    $item = Equipment::getById($db, $itemId);

    if (!$item) {
        $msgError = "Equipment record not found in the system.";
    }

} catch (Throwable $e) {
    $msgError = "System Error: " . $e->getMessage();
}

$pageTitle = 'Edit Equipment';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_equipment.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to Equipment Inventory</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        ⚙️ Edit Equipment: <?= htmlspecialchars($item ? $item->name : '') ?>
    </h1>

    <?php if ($msgError): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
             <?= htmlspecialchars($msgError) ?>
        </div>
    <?php endif; ?>

    <?php if ($item): ?>
        <form method="POST" action="/pages/admin_edit_equipment.php" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <input type="hidden" name="id" value="<?= $item->id ?>">

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Equipment Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($item->name) ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Category / Type *</label>
                <input type="text" name="type" value="<?= htmlspecialchars($item->type) ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Current Status *</label>
                <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                    <option value="available" <?= $item->status === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="in_use" <?= $item->status === 'in_use' ? 'selected' : '' ?>>In Use</option>
                    <option value="maintenance" <?= $item->status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Description / Details</label>
                <textarea name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"><?= htmlspecialchars($item->description ?? '') ?></textarea>
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