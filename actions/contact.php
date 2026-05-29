<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/contact.php');
    exit();
}

$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    setFlash('error', 'Invalid request. Please try again.');
    header('Location: /pages/contact.php');
    exit();
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    setFlash('error', 'Please fill in all required fields.');
    header('Location: /pages/contact.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    header('Location: /pages/contact.php');
    exit();
}

setFlash('success', 'Thank you for your message! We will get back to you soon.');
header('Location: /pages/contact.php');
exit();
