<nav class="navbar">
    <a href="<?= htmlspecialchars(url('home.php')) ?>" class="logo">MySite</a>
    <ul class="nav-links">
        <li><a href="<?= htmlspecialchars(url('home.php')) ?>" class="btn">Home</a></li>
        <?php if (!isset($_SESSION['valid'])): ?>
            <li><a href="<?= htmlspecialchars(url('auth/login.php')) ?>">Login</a></li>
            <li><a href="<?= htmlspecialchars(url('auth/register.php')) ?>">Register</a></li>
        <?php else: ?>

        <?php if (current_user_has_permission('articles.create')): ?>
            <li><a href="<?= htmlspecialchars(url('pages/article_add_form.php')) ?>">Add New Data</a></li>
        <?php endif; ?>
        <?php if (current_user_has_permission('articles.view')): ?>
            <li><a href="<?= htmlspecialchars(url('pages/article_view_form.php')) ?>">View Articles</a></li>
        <?php endif; ?>

            <?php if (current_user_has_permission('admin.access')): ?>
                <li><a href="<?= htmlspecialchars(url('admin/index.php')) ?>">Admin Panel</a></li>
            <?php endif; ?>
            <!-- Logout button (opens modal) -->
            <li><a href="#" id="logoutBtn" data-modal-open="logoutModal">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>

<?php if (isset($_SESSION['valid'])): ?>
    <div id="logoutModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">
            <h3 id="logoutTitle">Confirm Logout</h3>
            <p>Are you sure you want to log out?</p>
            <div class="modal-actions">
                <button type="button" data-modal-close="logoutModal" class="btn-cancel">
                    Cancel
                </button>
                <form method="post" action="<?= htmlspecialchars(url('auth/logout.php')) ?>" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <button type="submit" class="btn-danger">Yes, Logout</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
