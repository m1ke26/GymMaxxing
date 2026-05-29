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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $type        = trim($_POST['type']);
    $status      = $_POST['status'];
    $description = trim($_POST['description']);

    if ($name === '' || $type === '' || $status === '') {
        $msgError = "Please fill in all mandatory fields marked with (*).";
    } else {
        try {
            $db = getDatabaseConnection();

            Equipment::create(
                $db,
                $name,
                $type,
                $status,
                $description !== '' ? $description : null
            );

            header('Location: /pages/admin_equipment.php?sucesso=' . urlencode("Equipment '{$name}' registered successfully!"));
            exit();

        } catch (Throwable $e) {
            $msgError = "Error registering equipment: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Add New Equipment';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-page admin-page--form">

    <div class="admin-back">
        <a href="/pages/admin_equipment.php">&larr; Back to Inventory</a>
    </div>

    <h1 class="admin-page-title">Add New Equipment</h1>

    <?php if ($msgError): ?>
        <div class="admin-msg-error"><?= htmlspecialchars($msgError) ?></div>
    <?php endif; ?>

    <form method="POST" action="/pages/admin_create_equipment.php" class="admin-form-card">

        <div class="admin-field">
            <label>Equipment Name *</label>
            <input type="text" name="name" required placeholder="e.g., Olympic Barbell, Treadmill X9">
        </div>

        <div class="admin-field">
            <label>Type / Category *</label>
            <input type="text" name="type" required placeholder="e.g., Cardio, Free Weights, Accessories">
        </div>

        <div class="admin-field">
            <label>Operational Status *</label>
            <select name="status">
                <option value="available" selected>Available</option>
                <option value="maintenance">Under Maintenance</option>
                <option value="broken">Out of Order</option>
            </select>
        </div>

        <div class="admin-field--lg">
            <label>Description / Specifications</label>
            <textarea name="description" rows="4" placeholder="e.g., 20kg standard bar, maximum load 450kg, brand Gymco..."></textarea>
        </div>

        <button type="submit" class="admin-submit-btn">Add Equipment</button>
    </form>

</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
