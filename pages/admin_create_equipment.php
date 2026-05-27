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

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_equipment.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to Inventory</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        Add New Equipment
    </h1>

    <?php if ($msgError): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
             <?= htmlspecialchars($msgError) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/pages/admin_create_equipment.php" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Equipment Name *</label>
            <input type="text" name="name" required placeholder="e.g., Olympic Barbell, Treadmill X9"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Type / Category *</label>
            <input type="text" name="type" required placeholder="e.g., Cardio, Free Weights, Accessories"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Operational Status *</label>
            <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                <option value="available" selected>Available</option>
                <option value="maintenance">Under Maintenance</option>
                <option value="broken">Out of Order</option>
            </select>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Description / Specifications</label>
            <textarea name="description" rows="4" placeholder="e.g., 20kg standard bar, maximum load 450kg, brand Gymco..."
                      style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
        </div>

        <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;">
            Add Equipment
        </button>
    </form>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>