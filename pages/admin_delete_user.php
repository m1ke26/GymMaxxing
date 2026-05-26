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

$idParaApagar = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idParaApagar <= 0) {
    header('Location: /pages/admin_users.php');
    exit();
}

if ($idParaApagar === (int)$_SESSION['user_id']) {
    header('Location: /pages/admin_users.php?erro=' . urlencode('Não pode apagar a sua própria conta de administrador.'));
    exit();
}

try {
    $db = getDatabaseConnection();
    
    $stmtUser = $db->prepare('DELETE FROM User WHERE id = ?');
    $stmtUser->execute([$idParaApagar]);

    header('Location: /pages/admin_users.php?sucesso=' . urlencode('Utilizador e todas as suas dependências removidos com sucesso.'));
    exit();

} catch (Throwable $e) {
    header('Location: /pages/admin_users.php?erro=' . urlencode("Não foi possível apagar: " . $e->getMessage()));
    exit();
}