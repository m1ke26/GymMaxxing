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

<div class="admin-page">

    <div class="admin-back">
        <a href="/pages/admin.php">&larr; Back to Admin Panel</a>
    </div>

    <div class="admin-page-header">
        <h1>Equipment Inventory</h1>
        <a href="/pages/admin_create_equipment.php" class="admin-create-link">+ Register Equipment</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="/pages/admin_equipment.php">
            <label>Filter by Status:</label>

            <select name="status">
                <option value="">All Equipment</option>
                <option value="available" <?= $filterStatus === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="in_use" <?= $filterStatus === 'in_use' ? 'selected' : '' ?>>In Use</option>
                <option value="maintenance" <?= $filterStatus === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>

            <button type="submit" class="admin-filter-btn">Filter</button>

            <?php if ($filterStatus !== ''): ?>
                <a href="/pages/admin_equipment.php" class="admin-clear-filter">Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="admin-msg-success">
            <?= htmlspecialchars($_GET['sucesso']) ?>
        </div>
    <?php endif; ?>

    <?php if ($erroMsg || isset($_GET['erro'])): ?>
        <div class="admin-msg-error admin-msg-error--alt">
            <?= htmlspecialchars($erroMsg ?? $_GET['erro']) ?>
        </div>
    <?php endif; ?>

    <div class="admin-table-wrap">
        <table class="admin-pg-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Equipment Name</th>
                    <th>Category / Type</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($equipmentList)): ?>
                    <tr>
                        <td colspan="6" class="admin-empty-row">No equipment found matching this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipmentList as $item): ?>
                        <?php
                            $statusBadge = 'badge';
                            $statusText = $item->status;
                            if ($item->status === 'available') { $statusBadge .= ' badge--available'; $statusText = 'Available'; }
                            elseif ($item->status === 'in_use') { $statusBadge .= ' badge--in-use'; $statusText = 'In Use'; }
                            elseif ($item->status === 'maintenance') { $statusBadge .= ' badge--maintenance'; $statusText = 'Maintenance'; }
                            else { $statusBadge .= ' badge--default'; }
                        ?>
                        <tr>
                            <td class="text-id">#<?= $item->id ?></td>
                            <td class="text-bold"><?= htmlspecialchars($item->name) ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($item->type) ?></td>
                            <td>
                                <span class="<?= $statusBadge ?>"><?= $statusText ?></span>
                            </td>
                            <td class="text-small">
                                <?= $item->description ? htmlspecialchars($item->description) : '<span class="admin-no-desc">No description provided</span>' ?>
                            </td>
                            <td class="text-center">
                                <a href="/pages/admin_edit_equipment.php?id=<?= $item->id ?>" class="admin-act-edit">Edit</a>
                                <a href="/pages/admin_delete_equipment.php?id=<?= $item->id ?>" onclick="return confirm('Are you sure you want to remove this item from the inventory?');" class="admin-act-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
