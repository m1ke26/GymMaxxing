<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/trainerprofile.class.php';

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

$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '') ?: null;

if ($name === '' || $username === '' || $email === '') {
    setFlash('error', 'Name, username and email are required.');
    header('Location: /pages/profile.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    header('Location: /pages/profile.php');
    exit();
}

$existingEmail = User::getUserByEmail($db, $email);
if ($existingEmail && $existingEmail->id !== $userId) {
    setFlash('error', 'This email is already in use.');
    header('Location: /pages/profile.php');
    exit();
}

$existingUsername = User::getUserByUsername($db, $username);
if ($existingUsername && $existingUsername->id !== $userId) {
    setFlash('error', 'This username is already taken.');
    header('Location: /pages/profile.php');
    exit();
}

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($_FILES['photo']['tmp_name']);

    if (!in_array($mime, $allowed)) {
        setFlash('error', 'Only JPEG, PNG, GIF, and WebP images are allowed.');
        header('Location: /pages/profile.php');
        exit();
    }

    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        setFlash('error', 'Image must be smaller than 2 MB.');
        header('Location: /pages/profile.php');
        exit();
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
    $dest     = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
        // Delete old photo if exists
        if ($user->photo && file_exists($uploadDir . $user->photo)) {
            unlink($uploadDir . $user->photo);
        }
        User::updatePhoto($db, $userId, $filename);
    }
}

if ($user->role === 'trainer') {
    $bio            = trim($_POST['bio'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $certifications = trim($_POST['certifications'] ?? '');

    $profile = TrainerProfile::getByUserId($db, $userId);
    if ($profile) {
        TrainerProfile::update($db, $userId, $bio ?: null, $specialization ?: null, $certifications ?: null);
    }
}

User::updateUser($db, $userId, $name, $username, $email, $phone);

setFlash('success', 'Profile updated successfully!');
header('Location: /pages/profile.php');
exit();
