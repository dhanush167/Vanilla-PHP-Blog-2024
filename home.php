<?php
require_once __DIR__ . '/config/config.php';

$page_title = "Homepage";
require INCLUDES_PATH . "/header.php";
?>

<section class="container">
    <div id="header">
        <h4>Welcome to my page!</h4>
    </div>

    <?php if (is_authenticated()): ?>
        <div class="welcome-message">
            <p>Hello, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></strong>! 👋</p>

            <div class="action-links">
                <a href="<?= htmlspecialchars(url('pages/article_view_form.php')) ?>" class="btn btn-primary">
                    View and Add Articles
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="auth-prompt">
            <p>You must be logged in to view this page.</p>
            <div class="auth-links">
                <a href="<?= htmlspecialchars(url('auth/login.php')) ?>" class="btn btn-primary">Login</a>
                <a href="<?= htmlspecialchars(url('auth/register.php')) ?>" class="btn btn-secondary">Register</a>
            </div>
        </div>
    <?php endif; ?>

    <div id="footer">
        <p>Created by Dhanushka</p>
    </div>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>
