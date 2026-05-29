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

<div class="admin-page admin-page--form">

    <div class="admin-back">
        <a href="/pages/admin_classes.php">&larr; Back to Class Catalogue</a>
    </div>

    <h1 class="admin-page-title">
          Edit Class: <?= htmlspecialchars($gymClass ? $gymClass->title : '') ?>
    </h1>

    <?php if ($msgError): ?>
        <div class="admin-msg-error"><?= htmlspecialchars($msgError) ?></div>
    <?php endif; ?>

    <?php if ($gymClass): ?>
        <form method="POST" action="/pages/admin_edit_class.php" class="admin-form-card">
            <input type="hidden" name="id" value="<?= $gymClass->id ?>">

            <div class="admin-field">
                <label>Class Title *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($gymClass->title) ?>" required>
            </div>

            <div class="admin-field">
                <label>Class Type / Modality *</label>
                <select name="type" class="select-upper">
                    <option value="indoor" <?= $gymClass->type === 'indoor' ? 'selected' : '' ?>>Indoor</option>
                    <option value="outdoor" <?= $gymClass->type === 'outdoor' ? 'selected' : '' ?>>Outdoor</option>
                    <option value="wellness" <?= $gymClass->type === 'wellness' ? 'selected' : '' ?>>Wellness</option>
                    <option value="nutrition" <?= $gymClass->type === 'nutrition' ? 'selected' : '' ?>>Nutrition</option>
                </select>
            </div>

            <div class="admin-field">
                <label>Assigned Trainer *</label>
                <select name="trainerId" required>
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?= $trainer->id ?>" <?= $gymClass->trainerId === $trainer->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($trainer->name) ?> (@<?= htmlspecialchars($trainer->username) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-field">
                <label>Schedule / Timeslot *</label>
                <input type="text" name="schedule" value="<?= htmlspecialchars($gymClass->schedule) ?>" required>
            </div>

            <div class="admin-field">
                <label>Maximum Capacity *</label>
                <input type="number" name="capacity" value="<?= $gymClass->capacity ?>" min="1" required>
            </div>

            <div class="admin-field">
                <label>Image Filename</label>
                <input type="text" name="image" value="<?= htmlspecialchars($gymClass->image ?? '') ?>" placeholder="e.g., cardio_kickbox.png">
            </div>

            <div class="admin-field--lg">
                <label>Class Description</label>
                <textarea name="description" rows="4" placeholder="Provide details about the dynamic intensity levels, gear layout requirements..."><?= htmlspecialchars($gymClass->description ?? '') ?></textarea>
            </div>

            <button type="submit" class="admin-submit-btn">Save Changes</button>
        </form>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
