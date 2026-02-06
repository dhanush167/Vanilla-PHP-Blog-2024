<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (is_authenticated()) {
    header('Location: ' . url('home.php'));
    exit;
}

$page_title = "Register";
require INCLUDES_PATH . '/header.php';

// Flash messages
$error   = flash('error');
$success = flash('success');

// Old inputs after redirect
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

$nameOld     = (string)($old['name'] ?? '');
$emailOld    = (string)($old['email'] ?? '');
$usernameOld = (string)($old['username'] ?? '');
?>

<?php if ($success): ?>
    <p class="alert alert-success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(url('action/register_action.php')) ?>" class="login-form" autocomplete="on" novalidate>
    <h2>Register</h2>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($nameOld) ?>" required autocomplete="name">
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($emailOld) ?>" required autocomplete="email">
    </div>

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($usernameOld) ?>" required autocomplete="username">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
        <small style="display:block;margin-top:6px;">Use at least 8 characters (recommended).</small>
    </div>

    <button type="submit" class="login-btn">Register</button>
</form>

<?php require INCLUDES_PATH . '/footer.php'; ?>
