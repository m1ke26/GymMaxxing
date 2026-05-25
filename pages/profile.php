<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/memberprofile.class.php';
require_once __DIR__ . '/../database/trainerprofile.class.php';
require_once __DIR__ . '/../database/enrollment.class.php';
require_once __DIR__ . '/../database/class.class.php';

startSession();
requireLogin();

$pageTitle = 'Profile';

$db     = getDatabaseConnection();
$userId = getSessionUserId();
$user   = User::getUserById($db, $userId);

if (!$user) {
    logoutUser();
    header('Location: /pages/login.php');
    exit();
}

$errorMsg   = getFlash('error');
$successMsg = getFlash('success');

// Get role-specific data
$memberProfile  = null;
$trainerProfile = null;
$enrollments    = [];

if ($user->role === 'member') {
    $memberProfile = MemberProfile::getByUserId($db, $userId);
    $enrollmentRecords = Enrollment::getUserEnrollments($db, $userId);
    foreach ($enrollmentRecords as $enrollment) {
        $class = GymClass::getClassById($db, $enrollment->classId);
        if ($class) {
            $enrollments[] = $class;
        }
    }
} elseif ($user->role === 'trainer') {
    $trainerProfile = TrainerProfile::getByUserId($db, $userId);
    $trainerClasses = GymClass::getClassesByTrainer($db, $userId);

    // Build roster: for each class, get enrolled students
    $roster = [];
    foreach ($trainerClasses as $class) {
        $classEnrollments = Enrollment::getClassEnrollments($db, $class->id);
        $students = [];
        foreach ($classEnrollments as $enrollment) {
            $student = User::getUserById($db, $enrollment->userId);
            if ($student) $students[] = $student;
        }
        $roster[$class->id] = $students;
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

        <section class="page-hero">
            <h1>MY PROFILE</h1>
            <p><?= htmlspecialchars(ucfirst($user->role)) ?> Account</p>
        </section>

        <?php if ($errorMsg): ?>
            <div class="flash-error" style="max-width:1100px;margin:20px auto 0;padding:14px 20px;"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>
        <?php if ($successMsg): ?>
            <div class="flash-success" style="max-width:1100px;margin:20px auto 0;padding:14px 20px;"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>

        <section class="profile-content">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php if ($user->photo): ?>
                        <img src="/uploads/<?= htmlspecialchars($user->photo) ?>" alt="Profile Photo">
                    <?php else: ?>
                        <div class="avatar-placeholder"><?= strtoupper(substr($user->name, 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <h2><?= htmlspecialchars($user->name) ?></h2>
                <p class="profile-role"><?= htmlspecialchars(strtoupper($user->role)) ?></p>
                <?php if ($memberProfile):
                    $tierLimits = ['citizen' => 2, 'olympian' => 5, 'zeus' => 0];
                    $maxEnroll  = $tierLimits[$memberProfile->tier] ?? 2;
                    $currentEnrollCount = count($enrollments);
                ?>
                    <p class="profile-tier"><?= htmlspecialchars(strtoupper($memberProfile->tier)) ?> TIER</p>
                    <p class="profile-tier-info">Enrollments: <?= $currentEnrollCount ?> / <?= $maxEnroll > 0 ? $maxEnroll : '∞' ?></p>
                    <a href="/pages/joinus.php" class="svc-btn" style="margin-top:10px;display:inline-block;text-align:center;">CHANGE PLAN</a>
                <?php endif; ?>
                <a href="/actions/logout.php" class="svc-btn logout-btn">LOGOUT</a>
            </div>

            <div class="profile-main">
                <!-- Edit Profile Form -->
                <div class="profile-section">
                    <h2>Edit Profile</h2>
                    <form class="profile-form" action="/actions/update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <label for="prof-name">Name</label>
                        <input type="text" id="prof-name" name="name" value="<?= htmlspecialchars($user->name) ?>" required>

                        <label for="prof-username">Username</label>
                        <input type="text" id="prof-username" name="username" value="<?= htmlspecialchars($user->username) ?>" required>

                        <label for="prof-email">Email</label>
                        <input type="email" id="prof-email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>

                        <label for="prof-phone">Phone</label>
                        <input type="tel" id="prof-phone" name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>">

                        <label for="prof-photo">Profile Photo</label>
                        <input type="file" id="prof-photo" name="photo" accept="image/*">

                        <?php if ($user->role === 'trainer' && $trainerProfile): ?>
                            <label for="prof-bio">Bio</label>
                            <textarea id="prof-bio" name="bio"><?= htmlspecialchars($trainerProfile->bio ?? '') ?></textarea>

                            <label for="prof-spec">Specialization</label>
                            <input type="text" id="prof-spec" name="specialization" value="<?= htmlspecialchars($trainerProfile->specialization ?? '') ?>">

                            <label for="prof-cert">Certifications (comma-separated)</label>
                            <input type="text" id="prof-cert" name="certifications" value="<?= htmlspecialchars($trainerProfile->certifications ?? '') ?>">
                        <?php endif; ?>

                        <button type="submit">SAVE CHANGES</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="profile-section">
                    <h2>Change Password</h2>
                    <form class="profile-form" action="/actions/update_password.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <label for="pw-current">Current Password</label>
                        <input type="password" id="pw-current" name="current_password" required>

                        <label for="pw-new">New Password</label>
                        <input type="password" id="pw-new" name="new_password" required>

                        <label for="pw-confirm">Confirm New Password</label>
                        <input type="password" id="pw-confirm" name="confirm_password" required>

                        <button type="submit">CHANGE PASSWORD</button>
                    </form>
                </div>

                <!-- Member: Enrolled Classes -->
                <?php if ($user->role === 'member' && !empty($enrollments)): ?>
                <div class="profile-section">
                    <h2>My Enrollments</h2>
                    <div class="profile-classes">
                        <?php foreach ($enrollments as $class): ?>
                        <div class="profile-class-item">
                            <div>
                                <strong><?= htmlspecialchars($class->title) ?></strong>
                                <span><?= htmlspecialchars($class->schedule) ?></span>
                            </div>
                            <button class="svc-btn unenroll-profile-btn" data-class-id="<?= $class->id ?>">UNENROLL</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Trainer: My Classes + Roster -->
                <?php if ($user->role === 'trainer' && !empty($trainerClasses)): ?>
                <div class="profile-section">
                    <h2>My Classes</h2>
                    <?php foreach ($trainerClasses as $class):
                        $students = $roster[$class->id] ?? [];
                        $count    = count($students);
                    ?>
                    <div class="roster-class">
                        <div class="roster-class-header">
                            <div>
                                <strong><?= htmlspecialchars($class->title) ?></strong>
                                <span><?= htmlspecialchars($class->schedule) ?></span>
                            </div>
                            <span class="roster-count"><?= $count ?> / <?= $class->capacity ?> enrolled</span>
                        </div>
                        <?php if ($count > 0): ?>
                        <ul class="roster-list">
                            <?php foreach ($students as $student): ?>
                            <li>
                                <span class="roster-name"><?= htmlspecialchars($student->name) ?></span>
                                <span class="roster-email"><?= htmlspecialchars($student->email) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="roster-empty">No students enrolled yet.</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
