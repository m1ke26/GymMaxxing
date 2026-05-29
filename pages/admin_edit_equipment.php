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

<div class="admin-page admin-page--form">

    <div class="admin-back">
        <a href="/pages/admin_equipment.php">&larr; Back to Equipment Inventory</a>
    </div>

    <h1 class="admin-page-title">
        Edit Equipment: <?= htmlspecialchars($item ? $item->name : '') ?>
    </h1>

    <?php if ($msgError): ?>
        <div class="admin-msg-error"><?= htmlspecialchars($msgError) ?></div>
    <?php endif; ?>

    <?php if ($item): ?>
        <form method="POST" action="/pages/admin_edit_equipment.php" class="admin-form-card">
            <input type="hidden" name="id" value="<?= $item->id ?>">

            <div class="admin-field">
                <label>Equipment Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($item->name) ?>" required>
            </div>

            <div class="admin-field">
                <label>Category / Type *</label>
                <input type="text" name="type" value="<?= htmlspecialchars($item->type) ?>" required>
            </div>

            <div class="admin-field">
                <label>Current Status *</label>
                <select name="status">
                    <option value="available" <?= $item->status === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="in_use" <?= $item->status === 'in_use' ? 'selected' : '' ?>>In Use</option>
                    <option value="maintenance" <?= $item->status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>

            <div class="admin-field--lg">
                <label>Description / Details</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($item->description ?? '') ?></textarea>
            </div>

            <button type="submit" class="admin-submit-btn">Save Changes</button>
        </form>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
