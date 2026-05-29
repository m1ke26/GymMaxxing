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

$users = [];
$errorMsg = null;

$filterRole = isset($_GET['role']) ? trim($_GET['role']) : '';

try {
    $db = getDatabaseConnection();

    if ($filterRole === 'deactivated') {
        $stmt = $db->query('SELECT * FROM User WHERE active = 0 ORDER BY role, name');
        while ($row = $stmt->fetch()) {
            $users[] = new User(
                (int)$row['id'],
                $row['name'],
                $row['username'],
                $row['email'],
                $row['password'],
                $row['phone'],
                $row['photo'],
                $row['role'],
                (int)$row['active']
            );
        }
    } elseif (in_array($filterRole, ['member', 'trainer', 'admin'])) {
        $allRoles = User::getUsersByRole($db, $filterRole);
        foreach ($allRoles as $u) {
            if ((int)$u->active === 1) {
                $users[] = $u;
            }
        }
    } else {
        $stmt = $db->query('SELECT * FROM User WHERE active = 1 ORDER BY role, name');
        while ($row = $stmt->fetch()) {
            $users[] = new User(
                (int)$row['id'],
                $row['name'],
                $row['username'],
                $row['email'],
                $row['password'],
                $row['phone'],
                $row['photo'],
                $row['role'],
                (int)$row['active']
            );
        }
    }
} catch (Throwable $e) {
    $errorMsg = "Error loading user list: " . $e->getMessage();
}

$pageTitle = 'User Management';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-page">

    <div class="admin-back">
        <a href="/pages/admin.php">&larr; Back to Main Dashboard</a>
    </div>

    <div class="admin-page-header">
        <h1>User Management</h1>
        <a href="/pages/admin_create_user.php" class="admin-create-link">+ Create New User</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="/pages/admin_users.php">
            <label>Filter View:</label>

            <select name="role">
                <option value="">Active Users (All Roles)</option>
                <option value="member" <?= $filterRole === 'member' ? 'selected' : '' ?>>Active Members</option>
                <option value="trainer" <?= $filterRole === 'trainer' ? 'selected' : '' ?>>Active Trainers</option>
                <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Active Admins</option>
                <option value="deactivated" <?= $filterRole === 'deactivated' ? 'selected' : '' ?>>Deactivated Accounts</option>
            </select>

            <button type="submit" class="admin-filter-btn">Filter</button>

            <?php if ($filterRole !== ''): ?>
                <a href="/pages/admin_users.php" class="admin-clear-filter">Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="admin-msg-success">
              <?= htmlspecialchars($_GET['sucesso']) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg || isset($_GET['erro'])): ?>
        <div class="admin-msg-error admin-msg-error--alt">
              <?= htmlspecialchars($errorMsg ?? $_GET['erro']) ?>
        </div>
    <?php endif; ?>

    <div class="admin-table-wrap">
        <table class="admin-pg-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="admin-empty-row">No users found matching this filter criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                            $rowClass = (int)$user->active === 0 ? 'admin-row-deactivated' : '';
                            $badgeClass = 'badge';
                            if ((int)$user->active === 0) {
                                $badgeClass .= ' badge--deactivated';
                            } elseif ($user->role === 'admin') {
                                $badgeClass .= ' badge--admin';
                            } elseif ($user->role === 'trainer') {
                                $badgeClass .= ' badge--trainer';
                            } elseif ($user->role === 'member') {
                                $badgeClass .= ' badge--member';
                            } else {
                                $badgeClass .= ' badge--default';
                            }
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-bold"><?= htmlspecialchars($user->name) ?></td>
                            <td class="text-muted">@<?= htmlspecialchars($user->username) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($user->email) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($user->phone ?? '---') ?></td>
                            <td class="admin-role-cell">
                                <span class="<?= $badgeClass ?>"><?= htmlspecialchars($user->role) ?></span>
                                <?php if ((int)$user->active === 0): ?>
                                    <span class="badge badge--inactive">Deactivated</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ((int)$user->active === 0): ?>
                                    <a href="/pages/admin_edit_users.php?id=<?= $user->id ?>" class="admin-act-reactivate">Reactivate</a>
                                    <a href="/pages/admin_permanent_delete_user.php?id=<?= $user->id ?>" onclick="return confirm('Are you absolutely sure you want to permanently delete this user from the database? This action cannot be undone.');" class="admin-act-perm-delete">Delete Permanently</a>
                                <?php else: ?>
                                    <a href="/pages/admin_edit_users.php?id=<?= $user->id ?>" class="admin-act-edit">Edit</a>
                                    <a href="/pages/admin_delete_user.php?id=<?= $user->id ?>" onclick="return confirm('Are you sure you want to deactivate this user account? They will lose access immediately.');" class="admin-act-delete">Deactivate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
