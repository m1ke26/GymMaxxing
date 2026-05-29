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
require_once __DIR__ . '/../database/class.class.php';
require_once __DIR__ . '/../database/user.class.php';

$classes = [];
$erroMsg = null;

try {
    $db = getDatabaseConnection();

    $sql = 'SELECT c.*, u.name as trainerName,
            (SELECT COUNT(*) FROM Enrollment e WHERE e.classId = c.id) as currentEnrollments
            FROM Class c
            LEFT JOIN User u ON c.trainerId = u.id
            ORDER BY c.schedule, c.title';

    $stmt = $db->query($sql);
    $classes = $stmt->fetchAll();
} catch (Throwable $e) {
    $erroMsg = "Error loading class catalogue: " . $e->getMessage();
}

$pageTitle = 'Class Catalogue (Admin)';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-page">

    <div class="admin-back">
        <a href="/pages/admin.php">&larr; Back to Main Dashboard</a>
    </div>

    <div class="admin-page-header admin-page-header--spaced">
        <h1>Class Catalogue</h1>
        <a href="/pages/admin_create_class.php" class="admin-create-link">+ Create New Class</a>
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
                    <th>Image</th>
                    <th>Class Title</th>
                    <th>Type</th>
                    <th>Schedule</th>
                    <th>Trainer</th>
                    <th class="text-center">Enrolled / Capacity</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr>
                        <td colspan="7" class="admin-empty-row">No classes configured in the system.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <?php
                            $typeBadge = 'badge badge--' . htmlspecialchars($class['type']);
                            if (!in_array($class['type'], ['outdoor', 'indoor', 'wellness', 'nutrition'])) {
                                $typeBadge = 'badge badge--default';
                            }
                        ?>
                        <tr>
                            <td>
                                <img src="/images/<?= htmlspecialchars($class['image'] ?? 'homepage_outdoor.png') ?>"
                                     alt="Preview" class="admin-class-thumb">
                            </td>
                            <td class="text-bold"><?= htmlspecialchars($class['title']) ?></td>
                            <td>
                                <span class="<?= $typeBadge ?>"><?= htmlspecialchars($class['type']) ?></span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($class['schedule']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($class['trainerName'] ?? 'Unknown') ?></td>
                            <td class="text-center text-bold">
                                <span class="<?= ((int)$class['currentEnrollments'] >= (int)$class['capacity']) ? 'enroll-full' : 'enroll-ok' ?>">
                                    <?= $class['currentEnrollments'] ?>
                                </span>
                                <span class="enroll-cap">/ <?= $class['capacity'] ?></span>
                            </td>
                            <td class="text-center">
                                <a href="/pages/admin_edit_class.php?id=<?= (int)$class['id'] ?>" class="admin-act-edit">Edit</a>
                                <a href="/pages/admin_delete_class.php?id=<?= (int)$class['id'] ?>" onclick="return confirm('Are you sure you want to delete this class?');" class="admin-act-delete">Delete</a>
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
