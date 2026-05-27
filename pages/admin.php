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

$pageTitle = 'Administration Dashboard';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-dashboard-main" style="padding: 50px 20px; max-width: 1200px; margin: 0 auto; font-family: sans-serif;">

    <div style="margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px;">
        <h1 style="font-size: 2.4rem; color: #111; margin: 0; font-weight: bold;">Central Control Panel</h1>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
        
        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">User Management</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Complete control over gym members and trainers. Register new profiles, change security privileges, deactivate accounts, or promote members to administration tier level privileges.</p>
            </div>
            <a href="/pages/admin_users.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Access Module</a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">Class Schedule Catalogue</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Organize the activity schedules. Create new group training class modules (Indoor, Outdoor, Wellness, Nutrition), remove deprecated sessions, and allocate trainers to classes.</p>
            </div>
            <a href="/pages/admin_classes.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Access Module</a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
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