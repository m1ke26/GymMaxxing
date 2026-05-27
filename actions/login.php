<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.db.php';
require_once __DIR__ . '/../database/user.class.php';
require_once __DIR__ . '/../database/memberprofile.class.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/login.php');
    exit();
}

$action = $_POST['action'] ?? '';
$token  = $_POST['csrf_token'] ?? '';

if (!validateCsrfToken($token)) {
    setFlash('error', 'Invalid request. Please try again.');
    header('Location: /pages/login.php');
    exit();
}

$db = getDatabaseConnection();

if ($action === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        setFlash('error', 'Please fill in all required fields.');
        header('Location: /pages/login.php');
        exit();
    }

    $user = User::getUserByEmail($db, $email);

    if (!$user || !password_verify($password, $user->password)) {
        setFlash('error', 'Invalid email or password.');
        header('Location: /pages/login.php');
        exit();
    }

    if (!$user->active) {
        setFlash('error', 'Your account has been deactivated.');
        header('Location: /pages/login.php');
        exit();
    }

    loginUser($user->id, $user->role);
    setFlash('success', 'Welcome back, ' . $user->name . '!');
    header('Location: /pages/homepage.php');
    exit();

} elseif ($action === 'register') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '') ?: null;

    if ($name === '' || $email === '' || $password === '') {
        setFlash('error', 'Please fill in all required fields.');
        header('Location: /pages/login.php');
        exit();
    }

    if (strlen($password) < 4) {
        setFlash('error', 'Password must be at least 4 characters.');
        header('Location: /pages/login.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please enter a valid email address.');
        header('Location: /pages/login.php');
        exit();
    }

    // Check if email already exists
    if (User::getUserByEmail($db, $email)) {
        setFlash('error', 'An account with this email already exists.');
        header('Location: /pages/login.php');
        exit();
    }

    // Check if username already exists
    if (User::getUserByUsername($db, $name)) {
        setFlash('error', 'This username is already taken.');
        header('Location: /pages/login.php');
        exit();
    }

    // Create user as member
    $userId = User::createUser($db, $name, $name, $email, $password, $phone, 'member');

    // Create default member profile (citizen tier)
    MemberProfile::create($db, $userId, 'citizen');

    loginUser($userId, 'member');
    setFlash('success', 'Account created successfully! Welcome to Gymmaxxing!');
    header('Location: /pages/homepage.php');
    exit();

} else {
    header('Location: /pages/login.php');
    exit();
}
