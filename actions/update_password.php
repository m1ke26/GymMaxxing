<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/user.class.php';

startSession();
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/profile.php');
    exit();
}

$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    setFlash('error', 'Invalid request. Please try again.');
    header('Location: /pages/profile.php');
    exit();
}

$db     = getDatabaseConnection();
$userId = getSessionUserId();
$user   = User::getUserById($db, $userId);

if (!$user) {
    logoutUser();
    header('Location: /pages/login.php');
    exit();
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    setFlash('error', 'All password fields are required.');
    header('Location: /pages/profile.php');
    exit();
}

if (!password_verify($currentPassword, $user->password)) {
    setFlash('error', 'Current password is incorrect.');
    header('Location: /pages/profile.php');
    exit();
}

if ($newPassword !== $confirmPassword) {
    setFlash('error', 'New passwords do not match.');
    header('Location: /pages/profile.php');
    exit();
}

if (strlen($newPassword) < 4) {
    setFlash('error', 'New password must be at least 4 characters.');
    header('Location: /pages/profile.php');
    exit();
}

User::updatePassword($db, $userId, $newPassword);

setFlash('success', 'Password changed successfully!');
header('Location: /pages/profile.php');
exit();
