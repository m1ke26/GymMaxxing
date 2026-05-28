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

$idToDeactivate = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idToDeactivate <= 0) {
    header('Location: /pages/admin_users.php');
    exit();
}

if (isset($_SESSION['user_id']) && $idToDeactivate === (int)$_SESSION['user_id']) {
    header('Location: /pages/admin_users.php?erro=' . urlencode('You cannot deactivate your own active administrator account.'));
    exit();
}

try {
    $db = getDatabaseConnection();
    
    $stmtUser = $db->prepare('UPDATE User SET active = 0 WHERE id = ?');
    $stmtUser->execute([$idToDeactivate]);

    header('Location: /pages/admin_users.php?sucesso=' . urlencode('User account has been successfully deactivated.'));
    exit();

} catch (Throwable $e) {
    header('Location: /pages/admin_users.php?erro=' . urlencode("Unable to deactivate user: " . $e->getMessage()));
    exit();
}

