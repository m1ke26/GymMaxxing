<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/trainerprofile.class.php';
require_once __DIR__ . '/../database/class.class.php';

startSession();
$pageTitle = 'Trainers';

$db       = getDatabaseConnection();
$trainers = User::getUsersByRole($db, 'trainer');

require_once __DIR__ . '/../templates/header.php';
?>

        <section class="page-hero">
            <h1>OUR TRAINERS</h1>
            <p>Guided by the finest of Olympus</p>
        </section>

        <section class="trainers-list">
            <?php foreach ($trainers as $trainer):
                $profile = TrainerProfile::getByUserId($db, $trainer->id);
                $classes = GymClass::getClassesByTrainer($db, $trainer->id);
                $initial = strtoupper(substr($trainer->name, 0, 1));
                $certList = $profile && $profile->certifications ? explode(', ', $profile->certifications) : [];
            ?>
            <article class="trainer-card">
                <?php if ($trainer->photo): ?>
                    <img class="trainer-avatar-img" src="/uploads/<?= htmlspecialchars($trainer->photo) ?>" alt="<?= htmlspecialchars($trainer->name) ?>">
                <?php else: ?>
                    <div class="trainer-avatar"><?= $initial ?></div>
                <?php endif; ?>
                <div class="trainer-info">
                    <h2><?= htmlspecialchars($trainer->name) ?></h2>
                    <?php if ($profile && $profile->specialization): ?>
                        <p class="trainer-specialization"><?= htmlspecialchars($profile->specialization) ?></p>
                    <?php endif; ?>
                    <?php if ($profile && $profile->bio): ?>
                        <p class="trainer-bio"><?= htmlspecialchars($profile->bio) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($certList)): ?>
                        <h3>Certifications</h3>
                        <ul>
                            <?php foreach ($certList as $cert): ?>
                                <li><?= htmlspecialchars(trim($cert)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($classes)): ?>
                        <h3>Classes</h3>
                        <ul>
                            <?php foreach ($classes as $class): ?>
                                <li><?= htmlspecialchars($class->title) ?> &mdash; <?= htmlspecialchars($class->schedule) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
