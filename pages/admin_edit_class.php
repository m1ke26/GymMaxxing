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
require_once __DIR__ . '/../database/user.class.php';

$msgError = null;
$gymClass = null;
$trainers = [];

try {
    $db = getDatabaseConnection();
    

    $trainers = User::getUsersByRole($db, 'trainer');


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id          = (int)$_POST['id'];
        $title       = trim($_POST['title']);
        $type        = $_POST['type'];
        $description = trim($_POST['description']);
        $schedule    = trim($_POST['schedule']);
        $capacity    = (int)$_POST['capacity'];
        $trainerId   = (int)$_POST['trainerId'];
        $image       = trim($_POST['image']);


        if ($id <= 0 || $title === '' || $schedule === '' || $capacity <= 0 || $trainerId <= 0) {
            $msgError = "Please fill in all mandatory fields marked with (*).";
        } else {

            GymClass::updateClass(
                $db,
                $id,
                $title,
                $type,
                $description !== '' ? $description : null,
                $image !== '' ? $image : 'homepage_outdoor.png',
                $schedule,
                $capacity,
                $trainerId
            );


            header('Location: /pages/admin_classes.php?sucesso=' . urlencode("Class updated successfully!"));
            exit();
        }
    }


    $classId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    

    $gymClass = GymClass::getClassById($db, $classId);

    if (!$gymClass) {
        $msgError = "Class record not found in the system database.";
    }

} catch (Throwable $e) {
    $msgError = "System Error: " . $e->getMessage();
}

$pageTitle = 'Edit Gym Class';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-container" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/pages/admin_classes.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to Class Catalogue</a>
    </div>

    <h1 style="font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
          Edit Class: <?= htmlspecialchars($gymClass ? $gymClass->title : '') ?>
    </h1>

    <?php if ($msgError): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
             <?= htmlspecialchars($msgError) ?>
        </div>
    <?php endif; ?>

    <?php if ($gymClass): ?>
        <form method="POST" action="/pages/admin_edit_class.php" style="background: #fff; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <input type="hidden" name="id" value="<?= $gymClass->id ?>">

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Class Title *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($gymClass->title) ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Class Type / Modality *</label>
                <select name="type" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff; text-transform: uppercase;">
                    <option value="indoor" <?= $gymClass->type === 'indoor' ? 'selected' : '' ?>>Indoor</option>
                    <option value="outdoor" <?= $gymClass->type === 'outdoor' ? 'selected' : '' ?>>Outdoor</option>
                    <option value="wellness" <?= $gymClass->type === 'wellness' ? 'selected' : '' ?>>Wellness</option>
                    <option value="nutrition" <?= $gymClass->type === 'nutrition' ? 'selected' : '' ?>>Nutrition</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Assigned Trainer *</label>
                <select name="trainerId" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?= $trainer->id ?>" <?= $gymClass->trainerId === $trainer->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($trainer->name) ?> (@<?= htmlspecialchars($trainer->username) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Schedule / Timeslot *</label>
                <input type="text" name="schedule" value="<?= htmlspecialchars($gymClass->schedule) ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Maximum Capacity *</label>
                <input type="number" name="capacity" value="<?= $gymClass->capacity ?>" min="1" required
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Image Filename</label>
                <input type="text" name="image" value="<?= htmlspecialchars($gymClass->image ?? '') ?>" placeholder="e.g., cardio_kickbox.png"
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">Class Description</label>
                <textarea name="description" rows="4" placeholder="Provide details about the dynamic intensity levels, gear layout requirements..."
                          style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"><?= htmlspecialchars($gymClass->description ?? '') ?></textarea>
            </div>

            <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;">
                Save Changes
            </button>
        </form>
    <?php endif; ?>

</div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>