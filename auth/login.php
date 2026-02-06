<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (is_authenticated()) {
    header('Location: ' . url('home.php'));
    exit;
}

$page_title = "Login";
require INCLUDES_PATH . '/header.php';

// Flash messages
$success = flash('success');
$error   = flash('error');

// Keep username after redirect
$oldUsername = (string)($_SESSION['old_username'] ?? '');
unset($_SESSION['old_username']);
?>

<?php if ($success): ?>
    <p class="alert alert-success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($_GET['timeout'])): ?>
    <p class="alert alert-warning">Your session has expired. Please login again.</p>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(url('action/login_action.php')) ?>" class="login-form" autocomplete="on" novalidate>
    <h2>Login</h2>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <div class="form-group">
        <label for="username">Username</label>
        <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars($oldUsername) ?>"
                required
                autocomplete="username"
        >
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
        >
    </div>

    <button type="submit" class="login-btn">Login</button>
</form>

<?php require INCLUDES_PATH . '/footer.php'; ?>
