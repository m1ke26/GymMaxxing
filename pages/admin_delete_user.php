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
    header('Location: /pages/admin_users.php?erro=Não pode apagar a sua própria conta de administrador.');
    exit();
}

try {
    $db = getDatabaseConnection();
    
    $db->beginTransaction();

    // 2. LIMPEZA PREVENTIVA DE PERFIS RELACIONADOS
    // Remove registros órfãos nas tabelas de extensão do teu grupo antes de apagar o utilizador principal
    $stmtProfile1 = $db->prepare('DELETE FROM MemberProfile WHERE userId = ?');
    $stmtProfile1->execute([$idParaApagar]);

    $stmtProfile2 = $db->prepare('DELETE FROM TrainerProfile WHERE userId = ?');
    $stmtProfile2->execute([$idParaApagar]);

    // Remove também as inscrições que este utilizador possa ter feito em aulas
    $stmtEnrollment = $db->prepare('DELETE FROM Enrollment WHERE userId = ?');
    $stmtEnrollment->execute([$idParaApagar]);

    // 3. REMOÇÃO DO UTILIZADOR PRINCIPAL
    $stmtUser = $db->prepare('DELETE FROM User WHERE id = ?');
    $stmtUser->execute([$idParaApagar]);

    // Confirmar todas as remoções no banco
    $db->commit();

    // Sucesso! Redireciona de volta para a tua tabela linda
    header('Location: /pages/admin_users.php?sucesso=Utilizador removido com sucesso.');
    exit();

} catch (Throwable $e) {
    // Se algo falhar (ex: restrição de chave estrangeira com aulas ativas), desfaz as alterações
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    // Redireciona para a tabela mostrando o motivo da falha
    header('Location: /pages/admin_users.php?erro=' . urlencode("Não foi possível apagar: " . $e->getMessage()));
    exit();
}