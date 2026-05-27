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

<div class="admin-container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to Main Dashboard</a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
        <h1 style="margin: 0; font-size: 2rem; color: #111;">Class Catalogue</h1>
        <a href="/pages/admin_create_class.php" style="background: #000; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">+ Create New Class</a>
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
                    <th style="padding: 15px; color: #444; width: 80px;">Image</th>
                    <th style="padding: 15px; color: #444;">Class Title</th>
                    <th style="padding: 15px; color: #444;">Type</th>
                    <th style="padding: 15px; color: #444;">Schedule</th>
                    <th style="padding: 15px; color: #444;">Trainer</th>
                    <th style="padding: 15px; color: #444; text-align: center;">Enrolled / Capacity</th>
                    <th style="padding: 15px; color: #444; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: #888;">No classes configured in the system.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">
                                <img src="/images/<?= htmlspecialchars($class['image'] ?? 'homepage_outdoor.png') ?>" 
                                     alt="Preview" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                            </td>
                            <td style="padding: 15px; font-weight: bold; color: #111;">
                                <?= htmlspecialchars($class['title']) ?>
                            </td>
                            <td style="padding: 15px;">
                                <?php 
                                    $bg = '#e2e3e5'; $color = '#383d41';
                                    if ($class['type'] === 'outdoor') { $bg = '#fff3cd'; $color = '#856404'; }
                                    if ($class['type'] === 'indoor') { $bg = '#cce5ff'; $color = '#004085'; }
                                    if ($class['type'] === 'wellness') { $bg = '#d4edda'; $color = '#155724'; }
                                    if ($class['type'] === 'nutrition') { $bg = '#f8d7da'; $color = '#721c24'; }
                                ?>
                                <span style="background: <?= $bg ?>; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                    <?= htmlspecialchars($class['type']) ?>
                                </span>
                            </td>
                            <td style="padding: 15px; color: #555;">
                                <?= htmlspecialchars($class['schedule']) ?>
                            </td>
                            <td style="padding: 15px; color: #555;">
                                <?= htmlspecialchars($class['trainerName'] ?? 'Unknown') ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: bold;">
                                <span style="color: <?= ((int)$class['currentEnrollments'] >= (int)$class['capacity']) ? '#cc0000' : '#155724' ?>;">
                                    <?= $class['currentEnrollments'] ?>
                                </span> 
                                <span style="color: #888; font-weight: normal;">/ <?= $class['capacity'] ?></span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="/pages/admin_edit_class.php?id=<?= (int)$class['id'] ?>" style="text-decoration: none; color: #0066cc; font-weight: bold; margin-right: 15px; font-size: 0.9rem;">Edit</a>
                                <a href="/pages/admin_delete_class.php?id=<?= (int)$class['id'] ?>" onclick="return confirm('Are you sure you want to delete this class?');" style="text-decoration: none; color: #cc0000; font-weight: bold; font-size: 0.9rem;">Delete</a>
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