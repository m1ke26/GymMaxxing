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

$equipmentList = [];
$erroMsg = null;

$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

try {
    $db = getDatabaseConnection();
    

    if ($filterStatus === 'available') {
        $equipmentList = Equipment::getAvailable($db);
    } elseif (in_array($filterStatus, ['in_use', 'maintenance'])) {

        $stmt = $db->prepare('SELECT * FROM Equipment WHERE status = ?');
        $stmt->execute([$filterStatus]);
        $equipmentList = [];
        while ($e = $stmt->fetch()) {

            $equipmentList[] = new Equipment($e['id'], $e['name'], $e['type'], $e['status'], $e['description']);
        }
    } else {

        $equipmentList = Equipment::getAll($db);
    }
} catch (Throwable $e) {
    $erroMsg = "Error loading inventory: " . $e->getMessage();
}

$pageTitle = 'Equipment Management';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to Admin Panel</a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
        <h1 style="margin: 0; font-size: 2rem; color: #111;"> Equipment Inventory</h1>
        <a href="/pages/admin_create_equipment.php" style="background: #000; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">+ Register Equipment</a>
    </div>

    <div style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
        <form method="GET" action="/pages/admin_equipment.php" style="display: flex; align-items: center; gap: 12px; width: 100%; flex-wrap: wrap;">
            <label style="font-weight: bold; color: #333; font-size: 0.95rem;">Filter by Status:</label>
            
            <select name="status" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.9rem; min-width: 200px;">
                <option value=""> All Equipment</option>
                <option value="available" <?= $filterStatus === 'available' ? 'selected' : '' ?>> Available</option>
                <option value="in_use" <?= $filterStatus === 'in_use' ? 'selected' : '' ?>> In Use</option>
                <option value="maintenance" <?= $filterStatus === 'maintenance' ? 'selected' : '' ?>> Maintenance</option>
            </select>

            <button type="submit" style="background: #000; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 0.9rem; cursor: pointer;">
                Filter
            </button>

            <?php if ($filterStatus !== ''): ?>
                <a href="/pages/admin_equipment.php" style="color: #666; font-size: 0.9rem; font-weight: bold; text-decoration: none; margin-left: 5px;"> Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['sucesso']) ?>
        </div>
    <?php endif; ?>

    <?php if ($erroMsg || isset($_GET['erro'])): ?>
        <div style="background: #ffcccc; color: #cc0000; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($erroMsg ?? $_GET['erro']) ?>
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                    <th style="padding: 15px; color: #444;">ID</th>
                    <th style="padding: 15px; color: #444;">Equipment Name</th>
                    <th style="padding: 15px; color: #444;">Category / Type</th>
                    <th style="padding: 15px; color: #444;">Status</th>
                    <th style="padding: 15px; color: #444;">Description</th>
                    <th style="padding: 15px; color: #444; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($equipmentList)): ?>
                    <tr>
                        <td colspan="6" style="padding: 30px; text-align: center; color: #888;">No equipment found matching this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipmentList as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px; color: #888;">#<?= $item->id ?></td>
                            <td style="padding: 15px; font-weight: bold; color: #111;"><?= htmlspecialchars($item->name) ?></td>
                            <td style="padding: 15px; color: #555; text-transform: capitalize;"><?= htmlspecialchars($item->type) ?></td>
                            <td style="padding: 15px;">
                                <?php 
                                    $bg = '#e2e3e5'; $color = '#383d41'; $text = $item->status;
                                    if ($item->status === 'available') { $bg = '#d4edda'; $color = '#155724'; $text = 'Available'; }
                                    if ($item->status === 'in_use') { $bg = '#fff3cd'; $color = '#856404'; $text = 'In Use'; }
                                    if ($item->status === 'maintenance') { $bg = '#f8d7da'; $color = '#721c24'; $text = 'Maintenance'; }
                                ?>
                                <span style="background: <?= $bg ?>; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                    <?= $text ?>
                                </span>
                            </td>
                            <td style="padding: 15px; color: #666; font-size: 0.9rem;">
                                <?= $item->description ? htmlspecialchars($item->description) : '<span style="color:#ccc;">No description provided</span>' ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="/pages/admin_edit_equipment.php?id=<?= $item->id ?>" style="text-decoration: none; color: #0066cc; font-weight: bold; margin-right: 15px; font-size: 0.9rem;">Edit</a>
                                <a href="/pages/admin_delete_equipment.php?id=<?= $item->id ?>" onclick="return confirm('Are you sure you want to remove this item from the inventory?');" style="text-decoration: none; color: #cc0000; font-weight: bold; font-size: 0.9rem;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>