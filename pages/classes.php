<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/class.class.php';
require_once __DIR__ . '/../database/enrollment.class.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/review.class.php';

startSession();
$pageTitle = 'Classes';

$db       = getDatabaseConnection();
$classes  = GymClass::getAllClasses($db);
$trainers = User::getUsersByRole($db, 'trainer');

require_once __DIR__ . '/../templates/header.php';
?>

        <section class="page-hero">
            <h1>OUR CLASSES</h1>
            <p>Train like the gods of Olympus</p>
        </section>

        <section class="class-filters">
            <select id="filter-type" aria-label="Filter by type">
                <option value="">ALL TYPES</option>
                <option value="outdoor">OUTDOOR</option>
                <option value="indoor">INDOOR</option>
                <option value="wellness">WELLNESS</option>
                <option value="nutrition">NUTRITION</option>
            </select>
            <select id="filter-trainer" aria-label="Filter by trainer">
                <option value="">ALL TRAINERS</option>
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?= $trainer->id ?>"><?= htmlspecialchars(strtoupper($trainer->name)) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filter-day" aria-label="Filter by day">
                <option value="">ALL DAYS</option>
                <option value="Monday">MONDAY</option>
                <option value="Tuesday">TUESDAY</option>
                <option value="Wednesday">WEDNESDAY</option>
                <option value="Thursday">THURSDAY</option>
                <option value="Friday">FRIDAY</option>
            </select>
            <select id="filter-time" aria-label="Filter by time">
                <option value="">ALL TIMES</option>
                <option value="08:00">08:00</option>
                <option value="09:00">09:00</option>
                <option value="10:00">10:00</option>
                <option value="17:00">17:00</option>
                <option value="18:00">18:00</option>
            </select>
        </section>

        <section class="class-grid" id="class-grid">
            <?php foreach ($classes as $class):
                $trainer         = User::getUserById($db, $class->trainerId);
                $enrollmentCount = Enrollment::getEnrollmentCount($db, $class->id);
                $enrolled        = isLoggedIn() ? Enrollment::isEnrolled($db, getSessionUserId(), $class->id) : false;
                $avgRating       = Review::getAverageRating($db, $class->id);
            ?>
            <article class="class-card" data-class-id="<?= $class->id ?>">
                <img src="/images/<?= htmlspecialchars($class->image ?? 'homepage_outdoor.png') ?>" alt="<?= htmlspecialchars($class->title) ?>">
                <div class="class-card-content">
                    <span class="class-type-badge"><?= htmlspecialchars(ucfirst($class->type)) ?></span>
                    <h2><?= htmlspecialchars($class->title) ?></h2>
                    <p class="class-description"><?= htmlspecialchars($class->description ?? '') ?></p>
                    <div class="class-meta">
                        <p><strong>Schedule:</strong> <?= htmlspecialchars($class->schedule) ?></p>
                        <p><strong>Trainer:</strong> <?= htmlspecialchars($trainer ? $trainer->name : 'Unknown') ?></p>
                        <p class="spots-info"><strong>Spots:</strong> <span class="spots-count"><?= $enrollmentCount ?></span> / <?= $class->capacity ?></p>
                        <?php if ($avgRating): ?>
                            <p><strong>Rating:</strong> <?= $avgRating ?> / 5</p>
                        <?php endif; ?>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('member')): ?>
                            <button class="svc-btn enroll-btn"
                                    data-class-id="<?= $class->id ?>"
                                    data-enrolled="<?= $enrolled ? '1' : '0' ?>">
                                <?= $enrolled ? 'UNENROLL' : 'ENROLL NOW' ?>
                            </button>
                            <?php if ($enrolled): ?>
                                <p class="review-hint">Click card to leave a review</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/pages/login.php" class="svc-btn">LOGIN TO ENROLL</a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </section>

        <!-- Review modal -->
        <?php if (isLoggedIn() && hasRole('member')): ?>
        <div id="review-modal" class="modal hidden">
            <div class="modal-content">
                <button class="modal-close" id="modal-close">&times;</button>
                <h2>Leave a Review</h2>
                <form id="review-form">
                    <input type="hidden" id="review-class-id" name="classId" value="">
                    <div class="rating-input">
                        <label>Rating:</label>
                        <div class="stars" id="star-rating">
                            <span class="star" data-value="1">&#9733;</span>
                            <span class="star" data-value="2">&#9733;</span>
                            <span class="star" data-value="3">&#9733;</span>
                            <span class="star" data-value="4">&#9733;</span>
                            <span class="star" data-value="5">&#9733;</span>
                        </div>
                        <input type="hidden" id="review-rating" name="rating" value="0">
                    </div>
                    <textarea id="review-comment" name="comment" placeholder="Your review (optional)"></textarea>
                    <button type="submit" class="svc-btn">SUBMIT REVIEW</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" id="is-logged-in" value="<?= isLoggedIn() ? '1' : '0' ?>">

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
