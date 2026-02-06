<?php
require_once __DIR__ . '/../config/config.php';
require_permission('admin.access');

$page_title = "Admin Dashboard";
require INCLUDES_PATH . "/header.php";
?>

<section class="container">
    <div id="header">
        <h4>Admin Dashboard</h4>
    </div>

    <div class="admin-dashboard">
        <div class="dashboard-grid">
            <!-- Roles Management Card -->
            <div class="dashboard-card">
                <h5>Roles Management</h5>
                <p>Manage user roles and their permissions</p>
                <div class="card-actions">
                    <a href="<?= htmlspecialchars(url('admin/roles.php')) ?>" class="btn btn-primary">
                        Manage Roles
                    </a>
                </div>
            </div>

            <!-- Permissions Management Card -->
            <div class="dashboard-card">
                <h5>Permissions Management</h5>
                <p>Manage system permissions</p>
                <div class="card-actions">
                    <a href="<?= htmlspecialchars(url('admin/permissions.php')) ?>" class="btn btn-primary">
                        Manage Permissions
                    </a>
                </div>
            </div>

            <!-- User Roles Assignment Card -->
            <div class="dashboard-card">
                <h5>User Roles</h5>
                <p>Assign roles to users</p>
                <div class="card-actions">
                    <a href="<?= htmlspecialchars(url('admin/user_roles.php')) ?>" class="btn btn-primary">
                        Manage User Roles
                    </a>
                </div>
            </div>

            <!-- Role Permissions Assignment Card -->
            <div class="dashboard-card">
                <h5>Role Permissions</h5>
                <p>Assign permissions to roles</p>
                <div class="card-actions">
                    <a href="<?= htmlspecialchars(url('admin/role_permissions.php')) ?>" class="btn btn-primary">
                        Manage Role Permissions
                    </a>
                </div>
            </div>

            <!-- Categories Management Card -->
            <?php if (current_user_has_permission('categories.view')): ?>
            <div class="dashboard-card">
                <h5>Categories Management</h5>
                <p>Manage article categories</p>
                <div class="card-actions">
                    <a href="<?= htmlspecialchars(url('pages/categories.php')) ?>" class="btn btn-primary">
                        Manage Categories
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="admin-info">
            <h5>Your Current Roles:</h5>
            <ul>
                <?php foreach ($_SESSION['roles'] ?? [] as $role): ?>
                    <li><strong><?= htmlspecialchars($role['name']) ?></strong> - <?= htmlspecialchars($role['description'] ?? '') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>
