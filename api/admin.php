<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/class.class.php';
require_once __DIR__ . '/../database/equipment.class.php';
require_once __DIR__ . '/../database/memberprofile.class.php';
require_once __DIR__ . '/../database/trainerprofile.class.php';

startSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['csrf_token']) || !validateCsrfToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit();
}

$db     = getDatabaseConnection();
$entity = $input['entity'] ?? '';
$action = $input['action'] ?? '';

// user management
if ($entity === 'user') {

    if ($action === 'toggle') {
        $userId = (int) ($input['userId'] ?? 0);
        $user = User::getUserById($db, $userId);
        if (!$user) {
            echo json_encode(['error' => 'User not found']);
            exit();
        }
        if ($user->active) {
            User::deactivateUser($db, $userId);
        } else {
            User::activateUser($db, $userId);
        }
        echo json_encode(['success' => true, 'message' => 'User status updated.', 'active' => !$user->active]);
        exit();
    }

    if ($action === 'role') {
        $userId = (int) ($input['userId'] ?? 0);
        $role   = $input['role'] ?? '';
        if (!in_array($role, ['member', 'trainer', 'admin'])) {
            echo json_encode(['error' => 'Invalid role']);
            exit();
        }
        User::updateRole($db, $userId, $role);

        // Create profile if needed
        if ($role === 'member' && !MemberProfile::getByUserId($db, $userId)) {
            MemberProfile::create($db, $userId, 'citizen');
        }
        if ($role === 'trainer' && !TrainerProfile::getByUserId($db, $userId)) {
            TrainerProfile::create($db, $userId, '', '', '');
        }

        echo json_encode(['success' => true, 'message' => 'Role updated to ' . $role . '.']);
        exit();
    }
}

// class management
if ($entity === 'class') {

    if ($action === 'create') {
        $id = GymClass::createClass($db,
            $input['title'] ?? '',
            $input['type'] ?? '',
            $input['description'] ?? null,
            $input['image'] ?? null,
            $input['schedule'] ?? '',
            (int) ($input['capacity'] ?? 10),
            (int) ($input['trainerId'] ?? 0)
        );
        echo json_encode(['success' => true, 'message' => 'Class created.', 'id' => $id]);
        exit();
    }

    if ($action === 'update') {
        GymClass::updateClass($db,
            (int) ($input['id'] ?? 0),
            $input['title'] ?? '',
            $input['type'] ?? '',
            $input['description'] ?? null,
            $input['image'] ?? null,
            $input['schedule'] ?? '',
            (int) ($input['capacity'] ?? 10),
            (int) ($input['trainerId'] ?? 0)
        );
        echo json_encode(['success' => true, 'message' => 'Class updated.']);
        exit();
    }

    if ($action === 'delete') {
        GymClass::deleteClass($db, (int) ($input['id'] ?? 0));
        echo json_encode(['success' => true, 'message' => 'Class deleted.']);
        exit();
    }
}

// equipment management
if ($entity === 'equipment') {

    if ($action === 'create') {
        $id = Equipment::create($db,
            $input['name'] ?? '',
            $input['type'] ?? '',
            $input['status'] ?? 'available',
            $input['description'] ?? null
        );
        echo json_encode(['success' => true, 'message' => 'Equipment added.', 'id' => $id]);
        exit();
    }

    if ($action === 'update') {
        Equipment::update($db,
            (int) ($input['id'] ?? 0),
            $input['name'] ?? '',
            $input['type'] ?? '',
            $input['status'] ?? 'available',
            $input['description'] ?? null
        );
        echo json_encode(['success' => true, 'message' => 'Equipment updated.']);
        exit();
    }

    if ($action === 'delete') {
        Equipment::delete($db, (int) ($input['id'] ?? 0));
        echo json_encode(['success' => true, 'message' => 'Equipment deleted.']);
        exit();
    }
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
