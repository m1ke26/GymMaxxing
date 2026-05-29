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

<div class="admin-page admin-page--dashboard">

    <div class="admin-dash-title">
        <h1>Central Control Panel</h1>
    </div>

    <div class="admin-dash-grid">

        <div class="admin-dash-card">
            <div>
                <h3>User Management</h3>
                <p>Complete control over gym members and trainers. Register new profiles, change security privileges, deactivate accounts, or promote members to administration tier level privileges.</p>
            </div>
            <a href="/pages/admin_users.php" class="admin-card-link">Access Module</a>
        </div>

        <div class="admin-dash-card">
            <div>
                <h3>Class Schedule Catalogue</h3>
                <p>Organize the activity schedules. Create new group training class modules (Indoor, Outdoor, Wellness, Nutrition), remove deprecated sessions, and allocate trainers to classes.</p>
            </div>
            <a href="/pages/admin_classes.php" class="admin-card-link">Access Module</a>
        </div>

        <div class="admin-dash-card">
            <div>
                <h3>Equipment Inventory</h3>
                <p>Monitor physical weights, machines, and fitness equipment on the gym floor. Add new materials or toggle item operational statuses (Available, In Use, Maintenance) in real time.</p>
            </div>
            <a href="/pages/admin_equipment.php" class="admin-card-link">Access Module</a>
        </div>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>