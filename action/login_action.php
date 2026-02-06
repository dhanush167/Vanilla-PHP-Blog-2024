<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once CONFIG_PATH . '/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('auth/login.php'));
    exit;
}

/* -------------------------
   CSRF VALIDATION
-------------------------- */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf_token($token)) {
    set_flash('error', 'Invalid request. Please try again.');
    header('Location: ' . url('auth/login.php'));
    exit;
}
csrf_rotate();

/* -------------------------
   INPUT
-------------------------- */
$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    set_flash('error', 'Username and password are required.');
    $_SESSION['old_username'] = $username;
    header('Location: ' . url('auth/login.php'));
    exit;
}

/* -------------------------
   FETCH USER (Prepared)
-------------------------- */
$stmt = $mysqli->prepare(
    "SELECT id, name, username, password, is_active
     FROM login
     WHERE username = ?
     LIMIT 1"
);

if (!$stmt) {
    set_flash('error', 'Server error. Please try again.');
    $_SESSION['old_username'] = $username;
    header('Location: ' . url('auth/login.php'));
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

/* -------------------------
   AUTH CHECK
-------------------------- */
$validPassword = $user && password_verify($password, (string)$user['password']);
$activeUser    = $user && (int)$user['is_active'] === 1;

// Same message (don’t leak which part failed)
if (!$validPassword || !$activeUser) {
    set_flash('error', 'Invalid username or password.');
    $_SESSION['old_username'] = $username;
    header('Location: ' . url('auth/login.php'));
    exit;
}

/* -------------------------
   SUCCESS LOGIN
-------------------------- */
session_regenerate_id(true);

$_SESSION['valid']    = true;
$_SESSION['id']       = (int)$user['id'];
$_SESSION['name']     = (string)$user['name'];
$_SESSION['username'] = (string)$user['username'];

// Load user roles and permissions into session
require_once __DIR__ . '/../src/Helpers/permissions.php';
load_user_permissions_into_session((int)$user['id']);

// new csrf after login
csrf_rotate();

set_flash('success', 'Login successful.');
header('Location: ' . url('home.php'));
exit;
