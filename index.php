<?php
require_once __DIR__ . '/utils/session.php';
startSession();

if (isLoggedIn()) {
    header('Location: /pages/homepage.php');
} else {
    header('Location: /pages/homepage.php');
}
exit();
