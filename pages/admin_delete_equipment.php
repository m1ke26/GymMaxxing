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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $db = getDatabaseConnection();
        
        Equipment::delete($db, $id);
        
        header('Location: /pages/admin_equipment.php?sucesso=' . urlencode('Equipment removed from inventory successfully.'));
        exit();
    } catch (Throwable $e) {
        header('Location: /pages/admin_equipment.php?erro=' . urlencode('Error removing equipment: ' . $e->getMessage()));
        exit();
    }
} else {
    header('Location: /pages/admin_equipment.php');
    exit();
}
