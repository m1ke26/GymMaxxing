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
require_once __DIR__ . '/../database/user.class.php';

$users = [];
$erroMsg = null;

// Captura o filtro selecionado (via método GET)
$filtroRole = isset($_GET['role']) ? trim($_GET['role']) : '';

try {
    $db = getDatabaseConnection();
    
    
    if (in_array($filtroRole, ['member', 'trainer', 'admin'])) {
        $users = User::getUsersByRole($db, $filtroRole);
    } else {
        
        $stmt = $db->query('SELECT * FROM User ORDER BY role, name');
        while ($row = $stmt->fetch()) {
            $users[] = new User(
                (int)$row['id'],
                $row['name'],
                $row['username'],
                $row['email'],
                $row['password'],
                $row['phone'],
                $row['photo'],
                $row['role'],
                (int)$row['active']
            );
        }
    }
} catch (Throwable $e) {
    $erroMsg = "Erro ao carregar lista de utilizadores: " . $e->getMessage();
}

$pageTitle = 'Gestão de Usuários';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin.php" style="text-decoration: none; color: #666; font-weight: bold;">← Voltar ao Painel Central</a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
        <h1 style="margin: 0; font-size: 2rem; color: #111;">Gestão de Utilizadores</h1>
        <a href="/pages/admin_create_user.php" style="background: #000; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">+ Criar Novo Utilizador</a>
    </div>

    <div style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
        <form method="GET" action="/pages/admin_users.php" style="display: flex; align-items: center; gap: 12px; width: 100%; flex-wrap: wrap;">
            <label style="font-weight: bold; color: #333; font-size: 0.95rem;">Filtrar por Cargo:</label>
            
            <select name="role" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.9rem; min-width: 200px;">
                <option value=""> Todos os Utilizadores</option>
                <option value="member" <?= $filtroRole === 'member' ? 'selected' : '' ?>> Member (Alunos)</option>
                <option value="trainer" <?= $filtroRole === 'trainer' ? 'selected' : '' ?>> Trainer (Treinadores)</option>
                <option value="admin" <?= $filtroRole === 'admin' ? 'selected' : '' ?>> Admin (Administradores)</option>
            </select>

            <button type="submit" style="background: #000; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 0.9rem; cursor: pointer;">
                Filtrar
            </button>

            <?php if ($filtroRole !== ''): ?>
                <a href="/pages/admin_users.php" style="color: #666; font-size: 0.9rem; font-weight: bold; text-decoration: none; margin-left: 5px;"> Limpar Filtro</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
             <?= htmlspecialchars($_GET['sucesso']) ?>
        </div>
    <?php endif; ?>

    <?php if ($erroMsg || isset($_GET['erro'])): ?>
        <div style="background: #ffcccc; color: #cc0000; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
             <?= htmlspecialchars($erroMsg ?? $_GET['erro']) ?>
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                    <th style="padding: 15px; color: #444;">Nome</th>
                    <th style="padding: 15px; color: #444;">Username</th>
                    <th style="padding: 15px; color: #444;">Email</th>
                    <th style="padding: 15px; color: #444;">Telemóvel</th>
                    <th style="padding: 15px; color: #444;">Cargo</th>
                    <th style="padding: 15px; color: #444; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" style="padding: 30px; text-align: center; color: #888;">Nenhum utilizador encontrado para este filtro.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px; font-weight: bold; color: #111;">
                                <?= htmlspecialchars($user->name) ?>
                            </td>
                            <td style="padding: 15px; color: #555;">
                                @<?= htmlspecialchars($user->username) ?>
                            </td>
                            <td style="padding: 15px; color: #555;">
                                <?= htmlspecialchars($user->email) ?>
                            </td>
                            <td style="padding: 15px; color: #555;">
                                <?= htmlspecialchars($user->phone ?? '---') ?>
                            </td>
                            <td style="padding: 15px;">
                                <?php 
                                    $bg = '#e2e3e5'; $color = '#383d41';
                                    if ($user->role === 'admin') { $bg = '#f8d7da'; $color = '#721c24'; }
                                    if ($user->role === 'trainer') { $bg = '#cce5ff'; $color = '#004085'; }
                                    if ($user->role === 'member') { $bg = '#d4edda'; $color = '#155724'; }
                                ?>
                                <span style="background: <?= $bg ?>; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                    <?= htmlspecialchars($user->role) ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="/pages/admin_edit_users.php?id=<?= $user->id ?>" style="text-decoration: none; color: #0066cc; font-weight: bold; margin-right: 15px; font-size: 0.9rem;">Editar</a>
                                <a href="/pages/admin_delete_user.php?id=<?= $user->id ?>" onclick="return confirm('Tem a certeza que deseja remover este utilizador?');" style="text-decoration: none; color: #cc0000; font-weight: bold; font-size: 0.9rem;">Remover</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>