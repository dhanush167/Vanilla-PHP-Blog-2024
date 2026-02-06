<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once CONFIG_PATH . '/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('auth/register.php'));
    exit;
}

/* -------------------------
   CSRF VALIDATION
-------------------------- */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf_token($token)) {
    set_flash('error', 'Invalid request. Please try again.');
    header('Location: ' . url('auth/register.php'));
    exit;
}
csrf_rotate();

/* -------------------------
   INPUT
-------------------------- */
$name     = trim((string)($_POST['name'] ?? ''));
$email    = trim((string)($_POST['email'] ?? ''));
$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$old = ['name' => $name, 'email' => $email, 'username' => $username];

if ($name === '' || $email === '' || $username === '' || $password === '') {
    set_flash('error', 'All fields are required.');
    set_old($old);
    header('Location: ' . url('auth/register.php'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please enter a valid email address.');
    set_old($old);
    header('Location: ' . url('auth/register.php'));
    exit;
}

if (mb_strlen($password) < 8) {
    set_flash('error', 'Password must be at least 8 characters.');
    set_old($old);
    header('Location: ' . url('auth/register.php'));
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
    set_flash('error', 'Username must be 3–30 characters and contain only letters, numbers, and underscore.');
    set_old($old);
    header('Location: ' . url('auth/register.php'));
    exit;
}

/* -------------------------
   CHECK DUPLICATES
-------------------------- */
$check = $mysqli->prepare("SELECT id FROM login WHERE username = ? OR email = ? LIMIT 1");
if (!$check) {
    set_flash('error', 'Server error. Please try again.');
    set_old($old);
    header('Location: ' . url('auth/register.php'));
    exit;
}

$check->bind_param("ss", $username, $email);
$check->execute();
$res = $check->get_result();
$exists = $res ? $res->fetch_assoc() : null;
$check->close();

if ($exists) {
    set_flash('error', 'Username or email already exists. Please use another one.');
    set_old($old);
    header('Location: ' . url('auth/register.php'));
    exit;
}

/* -------------------------
   INSERT USER
-------------------------- */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare(
    "INSERT INTO login (name, email, username, password, is_active)
     VALUES (?, ?, ?, ?, 1)"
);

if (!$stmt) {
    set_flash('error', 'Server error. Please try again.');
    set_old($old);
    header('Location: ' . url('auth/register.php'));
    exit;
}

$stmt->bind_param("ssss", $name, $email, $username, $hashedPassword);

if ($stmt->execute()) {
    $stmt->close();

    // New csrf after successful registration
    csrf_rotate();

    set_flash('success', 'Registration successful. You can login now.');
    header('Location: ' . url('auth/login.php'));
    exit;
}

$stmt->close();
set_flash('error', 'Registration failed. Please try again.');
set_old($old);
header('Location: ' . url('auth/register.php'));
exit;
