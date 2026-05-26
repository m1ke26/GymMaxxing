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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $db = getDatabaseConnection();
        
        GymClass::deleteClass($db, $id);
        
        header('Location: /pages/admin_classes.php?sucesso=' . urlencode('Aula eliminada com sucesso.'));
        exit();
    } catch (Throwable $e) {
        header('Location: /pages/admin_classes.php?erro=' . urlencode('Erro ao eliminar a aula: ' . $e->getMessage()));
        exit();
    }
} else {
    header('Location: /pages/admin_classes.php');
    exit();
}