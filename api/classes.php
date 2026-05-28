<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/class.class.php';
require_once __DIR__ . '/../database/enrollment.class.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/review.class.php';

startSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$db = getDatabaseConnection();

$type      = !empty($_GET['type']) ? $_GET['type'] : null;
$trainerId = !empty($_GET['trainerId']) ? (int) $_GET['trainerId'] : null;
$day       = !empty($_GET['day']) ? $_GET['day'] : null;
$time      = !empty($_GET['time']) ? $_GET['time'] : null;

$classes = GymClass::searchClasses($db, $type, $trainerId, $day, $time);

$result = [];
foreach ($classes as $class) {
    $trainer        = User::getUserById($db, $class->trainerId);
    $enrollmentCount = Enrollment::getEnrollmentCount($db, $class->id);
    $avgRating      = Review::getAverageRating($db, $class->id);
    $enrolled       = isLoggedIn() ? Enrollment::isEnrolled($db, getSessionUserId(), $class->id) : false;

    $result[] = [
        'id'              => $class->id,
        'title'           => $class->title,
        'type'            => $class->type,
        'description'     => $class->description,
        'image'           => $class->image,
        'schedule'        => $class->schedule,
        'capacity'        => $class->capacity,
        'trainerId'       => $class->trainerId,
        'trainerName'     => $trainer ? $trainer->name : 'Unknown',
        'enrollmentCount' => $enrollmentCount,
        'avgRating'       => $avgRating ? round($avgRating, 1) : null,
        'enrolled'        => $enrolled,
        'spotsLeft'       => $class->capacity - $enrollmentCount,
    ];
}

echo json_encode($result);
