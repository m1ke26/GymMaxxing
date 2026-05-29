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
            $msgError = "Please fill in all mandatory fields marked with (*).";
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

            header('Location: /pages/admin_classes.php?sucesso=' . urlencode("Class '{$title}' created successfully!"));
            exit();
        }
    }
} catch (Throwable $e) {
    $msgError = "Error processing class creation: " . $e->getMessage();
}

$pageTitle = 'Create New Class';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="admin-page admin-page--form">

    <div class="admin-back">
        <a href="/pages/admin_classes.php">&larr; Back to Catalogue</a>
    </div>

    <h1 class="admin-page-title">Create New Class</h1>

    <?php if ($msgError): ?>
        <div class="admin-msg-error"><?= htmlspecialchars($msgError) ?></div>
    <?php endif; ?>

    <form method="POST" action="/pages/admin_create_class.php" class="admin-form-card">

        <div class="admin-field">
            <label>Class Title *</label>
            <input type="text" name="title" required placeholder="e.g., Intensive CrossFit, Clinical Pilates">
        </div>

        <div class="admin-field">
            <label>Class Type *</label>
            <select name="type">
                <option value="indoor">INDOOR</option>
                <option value="outdoor">OUTDOOR</option>
                <option value="wellness">WELLNESS</option>
                <option value="nutrition">NUTRITION</option>
            </select>
        </div>

        <div class="admin-field">
            <label>Assigned Trainer *</label>
            <select name="trainerId" required>
                <option value="">-- Select a Trainer --</option>
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?= $trainer->id ?>">
                        <?= htmlspecialchars($trainer->name) ?> (@<?= htmlspecialchars($trainer->username) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-field">
            <label>Schedule / Timeslot *</label>
            <input type="text" name="schedule" required placeholder="e.g., Monday 19:00, Saturday 10:00">
        </div>

        <div class="admin-field">
            <label>Maximum Capacity (Spots) *</label>
            <input type="number" name="capacity" min="1" required placeholder="e.g., 15">
        </div>

        <div class="admin-field">
            <label>Image Filename</label>
            <input type="text" name="image" placeholder="e.g., homepage_indoor.png (Optional)">
        </div>

        <div class="admin-field--lg">
            <label>Class Description</label>
            <textarea name="description" rows="4" placeholder="Brief details about the dynamic focus and goals of this class..."></textarea>
        </div>

        <button type="submit" class="admin-submit-btn">Create Class</button>
    </form>

</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
