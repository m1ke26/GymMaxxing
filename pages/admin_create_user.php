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

// Trava de segurança: Apenas administradores
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.db.php';

$msgSucesso = null;
$msgErro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    
    if ($nome === '' || $username === '' || $email === '' || $password === '') {
        $msgErro = "Por favor, preencha todos os campos obrigatórios (*).";
    } else {
        try {
            $db = getDatabaseConnection();

            // 1. Encriptar a password usando o padrão seguro do PHP
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // 2. Inserir na tabela User usando as colunas padrão do vosso grupo
            $stmt = $db->prepare('INSERT INTO User (name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $nome, 
                $username, 
                $email, 
                $phone !== '' ? $phone : null, 
                $passwordHash, 
                $role
            ]);

            $msgSucesso = "Utilizador @{$username} criado com sucesso!";
            
            // Limpa os campos para o formulário ficar vazio novamente
            $nome = $username = $email = $phone = '';

        } catch (Throwable $e) {
            // Trata erros de duplicação (ex: username ou email já existentes)
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $msgErro = "Erro: O Username ou o Email já estão a ser utilizados por outra conta.";
            } else {
                $msgErro = "Erro ao criar utilizador: " . $e->getMessage();
            }
        }
    }
}
unset($db);

$pageTitle = 'Criar Novo Utilizador';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_users.php" style="text-decoration: none; color: #666; font-weight: bold;">← Voltar para a Lista</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        Criar Novo Utilizador
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

    <form method="POST" action="/pages/admin_create_user.php" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Nome Completo *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($nome ?? '') ?>" required placeholder="Ex: Alexandre Magno"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Username (Nome de Utilizador) *</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required placeholder="Ex: alexandre10"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Endereço de Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required placeholder="Ex: alex@gymmaxxing.com"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Número de Telemóvel</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="Ex: 910000000"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Palavra-passe provisória *</label>
            <input type="password" name="password" required placeholder="Mínimo 6 caracteres"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Cargo Inicial (Role) *</label>
            <select name="role" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.95rem;">
                <option value="member" selected>👤 Member (Aluno)</option>
                <option value="trainer">🏋️‍♂️ Trainer (Treinador)</option>
                <option value="admin">🛡️ Admin (Administrador)</option>
            </select>
        </div>

        <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;">
            Registar Utilizador
        </button>
    </form>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>