<?php
declare(strict_types = 1);

// 1. Diagnóstico ativo para desenvolvimento local
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 2. Integração segura de sessão do grupo
require_once __DIR__ . '/../utils/session.php';
if (function_exists('startSession')) {
    startSession();
} else if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulação para testes locais (Admin Zeus)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
    $_SESSION['user_id'] = 1;
}

// Trava de segurança principal
if ($_SESSION['role'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

// =========================================================================
// 3. CONEXÃO COM A BASE DE DADOS (PASTA DATABASE)
// =========================================================================
require_once __DIR__ . '/../database/connection.db.php';

// Verificação defensiva para a classe User
if (file_exists(__DIR__ . '/../database/user.class.php')) {
    require_once __DIR__ . '/../database/user.class.php';
}

$adminName = 'Admin Zeus'; 
$avisoBanco = null; // Inicializamos aqui para evitar o "Undefined variable"

try {
    $db = getDatabaseConnection();
    if ($db && class_exists('User')) {
        $adminInfo = User::getUserById($db, (int)$_SESSION['user_id']);
        if ($adminInfo) {
            $adminName = $adminInfo->name;
        }
    }
} catch (Throwable $e) {
    $avisoBanco = "Nota: Erro ao ler a base de dados (" . $e->getMessage() . ").";
}

$pageTitle = 'Painel Administrativo';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-dashboard-main" style="padding: 50px 20px; max-width: 1200px; margin: 0 auto; font-family: sans-serif;">
    
    <?php if ($avisoBanco !== null): ?>
        <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; margin-bottom: 30px; border: 1px solid #ffeeba; font-size: 0.95rem;">
            ⚠️ <strong>Aviso do Sistema:</strong> <?= htmlspecialchars($avisoBanco) ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px;">
        <h1 style="font-size: 2.4rem; color: #111; margin: 0 0 10px 0; font-weight: bold;">Painel de Controle Central</h1>
        <p style="font-size: 1.1rem; color: #666; margin: 0;">Logado como: <strong style="color: #000;"><?= htmlspecialchars($adminName) ?></strong></p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
        
        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span style="font-size: 2rem; display: block; margin-bottom: 15px;"></span>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">Gestão de Utilizadores</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Controle completo sobre membros e treinadores. Cadastre perfis, altere permissões, desative contas ou promova usuários para o nível de administrador.</p>
            </div>
            <a href="/pages/admin_users.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Acessar Módulo</a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span style="font-size: 2rem; display: block; margin-bottom: 15px;"></span>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">Catálogo de Aulas</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Organize a grade horária de atividades. Crie novas modalidades de treino (Indoor, Outdoor, Wellness), remova sessões e aloque os instrutores.</p>
            </div>
            <a href="/pages/admin_classes.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Acessar Módulo</a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span style="font-size: 2rem; display: block; margin-bottom: 15px;"></span>
                <h3 style="font-size: 1.3rem; margin: 0 0 10px 0; color: #111;">Inventário de Equipamentos</h3>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0 0 20px 0;">Controle as máquinas e materiais da área principal de treino. Insira novos equipamentos e alterne as condições de uso em tempo real.</p>
            </div>
            <a href="/pages/admin_equipment.php" style="display: block; text-align: center; background: #000; color: #fff; padding: 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">Acessar Módulo</a>
        </div>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>