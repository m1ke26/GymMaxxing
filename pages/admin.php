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

// Local testing fallback mock setup (Admin Zeus)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
    $_SESSION['user_id'] = 1;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.db.php';

if (file_exists(__DIR__ . '/../database/user.class.php')) {
    require_once __DIR__ . '/../database/user.class.php';
}

$adminName = 'Admin Zeus'; 
$dbWarning = null; 

try {
    $db = getDatabaseConnection();
    if ($db && class_exists('User')) {
        $adminInfo = User::getUserById($db, (int)$_SESSION['user_id']);
        if ($adminInfo) {
            $adminName = $adminInfo->name;
        }
    }
} catch (Throwable $e) {
    $dbWarning = "Note: Error fetching database metadata (" . $e->getMessage() . ").";
}

$pageTitle = 'Administration Dashboard';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-dashboard-main" style="padding: 50px 20px; max-width: 1200px; margin: 0 auto; font-family: sans-serif;">
    
    <?php if ($dbWarning !== null): ?>
        <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; margin-bottom: 30px; border: 1px solid #ffeeba; font-size: 0.95rem;">
             <strong>System Warning:</strong> <?= htmlspecialchars($dbWarning) ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px;">
        <h1 style="font-size: 2.4rem; color: #111; margin: 0 0 10px 0; font-weight: bold;">Central Control Panel</h1>
        <p style="font-size: 1.1rem; color: #666; margin: 0;">Logged in as: <strong style="color: #000;"><?= htmlspecialchars($adminName) ?></strong></p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
        
        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span style="font-size: 2rem; display: block; margin-bottom: 15px;"></span>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">User Management</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Complete control over gym members and trainers. Register new profiles, change security permissions, deactivate accounts, or promote members to administration tier level privileges.</p>
            </div>
            <a href="/pages/admin_users.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Access Module</a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span style="font-size: 2rem; display: block; margin-bottom: 15px;"></span>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">Class Schedule Catalogue</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Organize the activity schedules. Create new group training class modules (Indoor, Outdoor, Wellness, Nutrition), remove deprecated sessions, and allocate trainers to classes.</p>
            </div>
            <a href="/pages/admin_classes.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Access Module</a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span style="font-size: 2rem; display: block; margin-bottom: 15px;"></span>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">Equipment Inventory</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Monitor physical weights, machines, and fitness equipment on the gym floor. Add new materials or toggle item operational statuses (Available, In Use, Maintenance) in real time.</p>
            </div>
            <a href="/pages/admin_equipment.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Access Module</a>
        </div>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>