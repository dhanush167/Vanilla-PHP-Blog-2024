<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('home.php'));
    exit;
}

/* -------------------------
   CSRF CHECK
-------------------------- */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf_token($token)) {
    // Just redirect (don’t show details)
    header('Location: ' . url('home.php'));
    exit;
}

// Prevent replay
csrf_rotate();

/* -------------------------
   LOGOUT
-------------------------- */
ensure_session();
$_SESSION = [];

// Remove session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'] ?? '/',
        $params['domain'] ?? '',
        (bool)($params['secure'] ?? false),
        (bool)($params['httponly'] ?? true)
    );
}

session_destroy();

set_flash('success', 'You have been logged out.');
header('Location: ' . url('home.php'));
exit;
