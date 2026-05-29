<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
startSession();
logoutUser();

startSession();
setFlash('success', 'You have been logged out.');
header('Location: /pages/login.php');
exit();
