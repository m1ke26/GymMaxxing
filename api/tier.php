<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/memberprofile.class.php';

startSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

if (!isLoggedIn() || !hasRole('member')) {
    http_response_code(403);
    echo json_encode(['error' => 'Only members can change their tier.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['csrf_token'] ?? '';

if (!validateCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid request token.']);
    exit();
}

$tier = $input['tier'] ?? '';

if (!in_array($tier, ['citizen', 'olympian', 'zeus'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid tier.']);
    exit();
}

$db     = getDatabaseConnection();
$userId = getSessionUserId();

$profile = MemberProfile::getByUserId($db, $userId);
if (!$profile) {
    http_response_code(404);
    echo json_encode(['error' => 'Member profile not found.']);
    exit();
}

if ($profile->tier === $tier) {
    echo json_encode(['success' => true, 'message' => 'You are already on the ' . strtoupper($tier) . ' tier.', 'tier' => $tier]);
    exit();
}

MemberProfile::updateTier($db, $userId, $tier);

$tierNames = ['citizen' => 'CITIZEN', 'olympian' => 'OLYMPIAN', 'zeus' => 'ZEUS'];
$limits    = ['citizen' => '2 classes', 'olympian' => '5 classes', 'zeus' => 'unlimited classes'];

echo json_encode([
    'success' => true,
    'tier'    => $tier,
    'message' => 'Upgraded to ' . $tierNames[$tier] . ' tier! You can now enroll in ' . $limits[$tier] . '.',
]);
