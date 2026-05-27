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

// Bloqueio de Segurança: Apenas Administradores
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/class.class.php';
require_once __DIR__ . '/../database/user.class.php';

$msgErro = null;
$trainers = [];

try {
    $db = getDatabaseConnection();

    
    $trainers = User::getUsersByRole($db, 'trainer');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title       = trim($_POST['title']);
        $type        = $_POST['type'];
        $description = trim($_POST['description']);
        $schedule    = trim($_POST['schedule']);
        $capacity    = (int)$_POST['capacity'];
        $trainerId   = (int)$_POST['trainerId'];
        $image       = trim($_POST['image']);

    
        if ($title === '' || $schedule === '' || $capacity <= 0 || $trainerId <= 0) {
            $msgErro = "Por favor, preencha todos os campos obrigatórios marcados com (*).";
        } else {
    
            GymClass::createClass(
                $db,
                $title,
                $type,
                $description !== '' ? $description : null,
                $image !== '' ? $image : 'homepage_outdoor.png', 
                $schedule,
                $capacity,
                $trainerId
            );

    
            header('Location: /pages/admin_classes.php?sucesso=' . urlencode("Aula '{$title}' criada com sucesso!"));
            exit();
        }
    }
} catch (Throwable $e) {
    $msgErro = "Erro ao processar a criação da aula: " . $e->getMessage();
}

$pageTitle = 'Criar Nova Aula';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_classes.php" style="text-decoration: none; color: #666; font-weight: bold;">← Voltar para o Catálogo</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
         Criar Nova Aula
    </h1>

    <?php if ($msgErro): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
             <?= htmlspecialchars($msgErro) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/pages/admin_create_class.php" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Título da Aula *</label>
            <input type="text" name="title" required placeholder="Ex: CrossFit Intensivo, Pilates Clínico"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Tipo de Aula *</label>
            <select name="type" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                <option value="indoor">INDOOR</option>
                <option value="outdoor">OUTDOOR</option>
                <option value="wellness">WELLNESS</option>
                <option value="nutrition">NUTRITION</option>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Treinador Responsável *</label>
            <select name="trainerId" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                <option value="">-- Selecione um Treinador --</option>
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?= $trainer->id ?>">
                        <?= htmlspecialchars($trainer->name) ?> (@<?= htmlspecialchars($trainer->username) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Horário / Agenda *</label>
            <input type="text" name="schedule" required placeholder="Ex: Monday 19:00, Saturday 10:00"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Capacidade Máxima (Vagas) *</label>
            <input type="number" name="capacity" min="1" required placeholder="Ex: 15"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Nome do Ficheiro de Imagem</label>
            <input type="text" name="image" placeholder="Ex: homepage_indoor.png (Opcional)"
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Descrição da Aula</label>
            <textarea name="description" rows="4" placeholder="Breve explicação sobre a dinâmica e objetivos da aula..."
                      style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
        </div>

        <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;">
            Criar Aula
        </button>
    </form>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>