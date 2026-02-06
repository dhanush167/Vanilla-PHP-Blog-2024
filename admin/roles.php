<?php
require_once __DIR__ . '/../config/config.php';
require_permission('roles.view');

require_once CONFIG_PATH . '/connection.php';

$page_title = "Manage Roles";
require INCLUDES_PATH . "/header.php";

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    require_permission('roles.delete');
    
    $roleId = (int)($_POST['role_id'] ?? 0);
    
    if ($roleId > 0) {
        // Check if it's a system role
        $stmt = $mysqli->prepare("SELECT is_system FROM roles WHERE id = ?");
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        
        if ($role && (int)$role['is_system'] === 0) {
            $stmt = $mysqli->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->bind_param("i", $roleId);
            if ($stmt->execute()) {
                set_flash('success', 'Role deleted successfully.');
            } else {
                set_flash('error', 'Failed to delete role.');
            }
            $stmt->close();
        } else {
            set_flash('error', 'Cannot delete system roles.');
        }
    }
    header('Location: ' . url('admin/roles.php'));
    exit;
}

// Fetch all roles
$stmt = $mysqli->prepare("SELECT r.*, COUNT(ur.user_id) as user_count
                          FROM roles r
                          LEFT JOIN user_roles ur ON r.id = ur.role_id
                          GROUP BY r.id
                          ORDER BY r.is_system DESC, r.name");
$stmt->execute();
$result = $stmt->get_result();
$roles = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<section class="container">
    <div id="header">
        <h4>Manage Roles</h4>
        <?php if (current_user_has_permission('roles.create')): ?>
            <a href="<?= htmlspecialchars(url('admin/role_add.php')) ?>" class="btn btn-primary">Add New Role</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($roles)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Users</th>
                    <th>System</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= htmlspecialchars($role['id']) ?></td>
                        <td><?= htmlspecialchars($role['name']) ?></td>
                        <td><code><?= htmlspecialchars($role['slug']) ?></code></td>
                        <td><?= htmlspecialchars($role['description'] ?? '') ?></td>
                        <td><?= htmlspecialchars($role['user_count']) ?></td>
                        <td><?= (int)$role['is_system'] === 1 ? '<span class="badge">System</span>' : '' ?></td>
                        <td>
                            <?php if (current_user_has_permission('roles.edit')): ?>
                                <a href="<?= htmlspecialchars(url('admin/role_edit.php?id=' . $role['id'])) ?>" class="btn btn-sm">Edit</a>
                            <?php endif; ?>
                            <?php if (current_user_has_permission('roles.delete') && (int)$role['is_system'] === 0): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="role_id" value="<?= htmlspecialchars($role['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No roles found.</p>
    <?php endif; ?>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>
