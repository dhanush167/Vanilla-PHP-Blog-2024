<?php
require_once __DIR__ . '/../config/config.php';
require_permission('permissions.view');

require_once CONFIG_PATH . '/connection.php';

$page_title = "Manage Permissions";
require INCLUDES_PATH . "/header.php";

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    require_permission('permissions.delete');
    
    $permissionId = (int)($_POST['permission_id'] ?? 0);
    
    if ($permissionId > 0) {
        $stmt = $mysqli->prepare("DELETE FROM permissions WHERE id = ?");
        $stmt->bind_param("i", $permissionId);
        if ($stmt->execute()) {
            set_flash('success', 'Permission deleted successfully.');
        } else {
            set_flash('error', 'Failed to delete permission.');
        }
        $stmt->close();
    }
    header('Location: ' . url('admin/permissions.php'));
    exit;
}

// Fetch all permissions grouped by module
$stmt = $mysqli->prepare("SELECT * FROM permissions ORDER BY module, name");
$stmt->execute();
$result = $stmt->get_result();
$permissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group by module
$permissionsByModule = [];
foreach ($permissions as $permission) {
    $module = $permission['module'] ?? 'Other';
    if (!isset($permissionsByModule[$module])) {
        $permissionsByModule[$module] = [];
    }
    $permissionsByModule[$module][] = $permission;
}
?>

<section class="container">
    <div id="header">
        <h4>Manage Permissions</h4>
        <?php if (current_user_has_permission('permissions.create')): ?>
            <a href="<?= htmlspecialchars(url('admin/permission_add.php')) ?>" class="btn btn-primary">Add New Permission</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($permissionsByModule)): ?>
        <?php foreach ($permissionsByModule as $module => $modulePermissions): ?>
            <h5><?= htmlspecialchars($module) ?></h5>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modulePermissions as $permission): ?>
                        <tr>
                            <td><?= htmlspecialchars($permission['id']) ?></td>
                            <td><?= htmlspecialchars($permission['name']) ?></td>
                            <td><code><?= htmlspecialchars($permission['slug']) ?></code></td>
                            <td><?= htmlspecialchars($permission['description'] ?? '') ?></td>
                            <td>
                                <?php if (current_user_has_permission('permissions.edit')): ?>
                                    <a href="<?= htmlspecialchars(url('admin/permission_edit.php?id=' . $permission['id'])) ?>" class="btn btn-sm">Edit</a>
                                <?php endif; ?>
                                <?php if (current_user_has_permission('permissions.delete')): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this permission?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="permission_id" value="<?= htmlspecialchars($permission['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No permissions found.</p>
    <?php endif; ?>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>
