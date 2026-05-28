<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/class.class.php';
require_once __DIR__ . '/../database/enrollment.class.php';
require_once __DIR__ . '/../database/memberprofile.class.php';

startSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to enroll.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['csrf_token'] ?? '';

if (!validateCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid request token.']);
    exit();
}

$classId = isset($input['classId']) ? (int) $input['classId'] : 0;
$action  = $input['action'] ?? '';

if ($classId <= 0 || !in_array($action, ['enroll', 'unenroll'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters.']);
    exit();
}

$db     = getDatabaseConnection();
$userId = getSessionUserId();

$class = GymClass::getClassById($db, $classId);
if (!$class) {
    http_response_code(404);
    echo json_encode(['error' => 'Class not found.']);
    exit();
}

$isEnrolled = Enrollment::isEnrolled($db, $userId, $classId);

if ($action === 'enroll') {
    if ($isEnrolled) {
        http_response_code(400);
        echo json_encode(['error' => 'You are already enrolled in this class.']);
        exit();
    }

    // Tier-based enrollment limits
    $memberProfile = MemberProfile::getByUserId($db, $userId);
    if ($memberProfile) {
        $tierLimits = ['citizen' => 2, 'olympian' => 5, 'zeus' => 0]; // 0 = unlimited
        $maxEnroll  = $tierLimits[$memberProfile->tier] ?? 2;
        if ($maxEnroll > 0) {
            $userEnrollments = Enrollment::getUserEnrollments($db, $userId);
            if (count($userEnrollments) >= $maxEnroll) {
                http_response_code(400);
                $tierName = strtoupper($memberProfile->tier);
                echo json_encode(['error' => "Your $tierName tier allows a maximum of $maxEnroll enrollments. Upgrade your plan to enroll in more classes."]);
                exit();
            }
        }
    }

    $count = Enrollment::getEnrollmentCount($db, $classId);
    if ($count >= $class->capacity) {
        http_response_code(400);
        echo json_encode(['error' => 'This class is full.']);
        exit();
    }

    Enrollment::enroll($db, $userId, $classId);
    $newCount = Enrollment::getEnrollmentCount($db, $classId);

    echo json_encode([
        'success'         => true,
        'enrolled'        => true,
        'enrollmentCount' => $newCount,
        'spotsLeft'       => $class->capacity - $newCount,
        'message'         => 'Successfully enrolled!',
    ]);

} elseif ($action === 'unenroll') {
    if (!$isEnrolled) {
        http_response_code(400);
        echo json_encode(['error' => 'You are not enrolled in this class.']);
        exit();
    }

    Enrollment::unenroll($db, $userId, $classId);
    $newCount = Enrollment::getEnrollmentCount($db, $classId);

    echo json_encode([
        'success'         => true,
        'enrolled'        => false,
        'enrollmentCount' => $newCount,
        'spotsLeft'       => $class->capacity - $newCount,
        'message'         => 'Successfully unenrolled.',
    ]);
}
