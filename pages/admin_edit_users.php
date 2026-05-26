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

// Trava de segurança
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.db.php';

$user = null;
$msgSucesso = null;
$msgErro = null;

try {
    $db = getDatabaseConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idAlterar = (int)$_POST['id'];
        $novoNome = trim($_POST['name']);
        $novoEmail = trim($_POST['email']);
        $novoPhone = trim($_POST['phone']);
        $novoCargo = $_POST['role'];

        if ($novoNome === '' || $novoEmail === '') {
            $msgErro = "Nome e Email são campos obrigatórios.";
        } else {
            // Atualiza o utilizador na tabela User
            $stmtUpdate = $db->prepare('UPDATE User SET name = ?, email = ?, phone = ?, role = ? WHERE id = ?');
            $stmtUpdate->execute([$novoNome, $novoEmail, $novoPhone !== '' ? $novoPhone : null, $novoCargo, $idAlterar]);
            
            $msgSucesso = "Utilizador atualizado com sucesso!";
        }
    }

    $userId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    
    $stmt = $db->prepare('SELECT id, name, username, email, phone, role FROM User WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $msgErro = "Utilizador não encontrado.";
    }

} catch (Throwable $e) {
    $msgErro = "Erro no sistema: " . $e->getMessage();
}

$pageTitle = 'Editar Utilizador';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_users.php" style="text-decoration: none; color: #666; font-weight: bold;">← Voltar para a Lista</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
         Editar Perfil de @<?= htmlspecialchars((string)($user['username'] ?? '')) ?>
    </h1>

    <?php if ($msgSucesso): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
             <?= htmlspecialchars($msgSucesso) ?>
        </div>
    <?php endif; ?>

    <?php if ($msgErro): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
             <?= htmlspecialchars($msgErro) ?>
        </div>
    <?php endif; ?>

    <?php if ($user): ?>
        <form method="POST" action="/pages/admin_edit_users.php" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Nome Completo *</label>
                <input type="text" name="name" value="<?= htmlspecialchars((string)$user['name']) ?>" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Endereço de Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars((string)$user['email']) ?>" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Número de Telemóvel</label>
                <input type="text" name="phone" value="<?= htmlspecialchars((string)($user['phone'] ?? '')) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Cargo no Ginásio (Role) *</label>
                <select name="role" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.95rem; font-weight: bold;">
                    <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>> Member (Aluno)</option>
                    <option value="trainer" <?= $user['role'] === 'trainer' ? 'selected' : '' ?>> Trainer (Treinador)</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>> Admin (Administrador Total)</option>
                </select>
                <small style="color: #666; display: block; margin-top: 5px;">Mudar para "Admin" dará a este utilizador acesso total a este painel.</small>
            </div>

            <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;">
                Salvar Alterações
            </button>
        </form>
    <?php endif; ?>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>