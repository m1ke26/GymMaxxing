<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/review.class.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/enrollment.class.php';

startSession();
header('Content-Type: application/json');

$db = getDatabaseConnection();

// GET — fetch reviews for a class
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $classId = isset($_GET['classId']) ? (int) $_GET['classId'] : 0;

    if ($classId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid class ID.']);
        exit();
    }

    $reviews = Review::getByClass($db, $classId);
    $result  = [];

    foreach ($reviews as $review) {
        $user = User::getUserById($db, $review->userId);
        $result[] = [
            'id'        => $review->id,
            'userId'    => $review->userId,
            'userName'  => $user ? htmlspecialchars($user->name) : 'Unknown',
            'rating'    => $review->rating,
            'comment'   => htmlspecialchars($review->comment ?? ''),
            'createdAt' => $review->createdAt,
        ];
    }

    $avgRating = Review::getAverageRating($db, $classId);

    // Include the logged-in user's own review if it exists
    $userReview = null;
    if (isLoggedIn()) {
        $uid = getSessionUserId();
        foreach ($result as $r) {
            if ($r['userId'] === $uid) {
                $userReview = $r;
                break;
            }
        }
    }

    echo json_encode([
        'reviews'   => $result,
        'review'    => $userReview,
        'avgRating' => $avgRating ? round($avgRating, 1) : null,
        'count'     => count($result),
    ]);
    exit();
}

// POST — create or update a review
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'You must be logged in to review.']);
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
    $rating  = isset($input['rating']) ? (int) $input['rating'] : 0;
    $comment = trim($input['comment'] ?? '');

    if ($classId <= 0 || $rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid parameters. Rating must be 1-5.']);
        exit();
    }

    $userId = getSessionUserId();

    // Must be enrolled to review
    if (!Enrollment::isEnrolled($db, $userId, $classId)) {
        http_response_code(403);
        echo json_encode(['error' => 'You must be enrolled in this class to leave a review.']);
        exit();
    }

    // Check if user already has a review — update instead
    $existingReviews = Review::getByUser($db, $userId);
    $hasReview = false;
    foreach ($existingReviews as $r) {
        if ($r->classId === $classId) {
            $hasReview = true;
            break;
        }
    }

    if ($hasReview) {
        Review::update($db, $userId, $classId, $rating, $comment ?: null);
        $message = 'Review updated!';
    } else {
        Review::create($db, $userId, $classId, $rating, $comment ?: null);
        $message = 'Review submitted!';
    }

    $avgRating = Review::getAverageRating($db, $classId);

    echo json_encode([
        'success'  => true,
        'message'  => $message,
        'avgRating' => $avgRating ? round($avgRating, 1) : null,
    ]);
    exit();
}

// DELETE — remove a review
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'You must be logged in.']);
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
    $userId  = getSessionUserId();

    Review::delete($db, $userId, $classId);

    echo json_encode(['success' => true, 'message' => 'Review deleted.']);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
