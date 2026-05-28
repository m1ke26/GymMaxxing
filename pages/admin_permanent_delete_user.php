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

$idToDelete = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idToDelete <= 0) {
    header('Location: /pages/admin_users.php');
    exit();
}

try {
    $db = getDatabaseConnection();
    
    $stmtUser = $db->prepare('DELETE FROM User WHERE id = ?');
    $stmtUser->execute([$idToDelete]);

    header('Location: /pages/admin_users.php?sucesso=' . urlencode('User record permanently deleted from the database.'));
    exit();

} catch (Throwable $e) {
    header('Location: /pages/admin_users.php?erro=' . urlencode("Unable to permanently delete user: " . $e->getMessage()));
    exit();
}