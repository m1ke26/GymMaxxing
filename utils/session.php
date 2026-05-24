<?php
declare(strict_types=1);

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn(): bool {
    return isset($_SESSION['userId']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /pages/login.php');
        exit();
    }
}

function requireRole(string $role): void {
    requireLogin();
    if (getSessionRole() !== $role) {
        header('Location: /pages/homepage.php');
        exit();
    }
}

function getSessionUserId(): ?int {
    return isset($_SESSION['userId']) ? (int) $_SESSION['userId'] : null;
}

function getSessionRole(): ?string {
    return $_SESSION['role'] ?? null;
}

function hasRole(string $role): bool {
    return getSessionRole() === $role;
}

function loginUser(int $userId, string $role): void {
    session_regenerate_id(true);
    $_SESSION['userId'] = $userId;
    $_SESSION['role'] = $role;
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function setFlash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string {
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}
