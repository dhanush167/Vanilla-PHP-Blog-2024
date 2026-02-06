<?php
require_once __DIR__ . '/../config/config.php';
require_permission('permissions.manage_role_permissions');

require_once CONFIG_PATH . '/connection.php';

$page_title = "Manage Role Permissions";
require INCLUDES_PATH . "/header.php";

// Handle permission assignment/removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleId = (int)($_POST['role_id'] ?? 0);
    $permissionId = (int)($_POST['permission_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    
    if ($roleId > 0 && $permissionId > 0) {
        if ($action === 'assign') {
            // Check if already assigned
            $stmt = $mysqli->prepare("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?");
            $stmt->bind_param("ii", $roleId, $permissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                $stmt = $mysqli->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $roleId, $permissionId);
                if ($stmt->execute()) {
                    set_flash('success', 'Permission assigned successfully.');
                } else {
                    set_flash('error', 'Failed to assign permission.');
                }
            } else {
                set_flash('info', 'Role already has this permission.');
            }
            $stmt->close();
        } elseif ($action === 'remove') {
            $stmt = $mysqli->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?");
            $stmt->bind_param("ii", $roleId, $permissionId);
            if ($stmt->execute()) {
                set_flash('success', 'Permission removed successfully.');
            } else {
                set_flash('error', 'Failed to remove permission.');
            }
            $stmt->close();
        }
    }
    header('Location: ' . url('admin/role_permissions.php'));
    exit;
}

// Fetch all roles
$rolesResult = $mysqli->query("SELECT id, name, slug FROM roles ORDER BY name");
$roles = $rolesResult ? $rolesResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch all permissions grouped by module
$permissionsResult = $mysqli->query("SELECT * FROM permissions ORDER BY module, name");
$permissions = $permissionsResult ? $permissionsResult->fetch_all(MYSQLI_ASSOC) : [];

// Group permissions by module
$permissionsByModule = [];
foreach ($permissions as $permission) {
    $module = $permission['module'] ?? 'Other';
    if (!isset($permissionsByModule[$module])) {
        $permissionsByModule[$module] = [];
    }
    $permissionsByModule[$module][] = $permission;
}

// Get role permissions mapping
$rolePermissionsMap = [];
$rolePermissionsResult = $mysqli->query("SELECT role_id, permission_id FROM role_permissions");
if ($rolePermissionsResult) {
    while ($row = $rolePermissionsResult->fetch_assoc()) {
        $roleId = (int)$row['role_id'];
        if (!isset($rolePermissionsMap[$roleId])) {
            $rolePermissionsMap[$roleId] = [];
        }
        $rolePermissionsMap[$roleId][] = (int)$row['permission_id'];
    }
}
?>

<section class="container">
    <div id="header">
        <h4>Manage Role Permissions</h4>
    </div>

    <?php if (!empty($roles)): ?>
        <?php foreach ($roles as $role): ?>
            <div class="role-permissions-section">
                <h5><?= htmlspecialchars($role['name']) ?> (<?= htmlspecialchars($role['slug']) ?>)</h5>
                
                <?php
                $rolePermissions = $rolePermissionsMap[(int)$role['id']] ?? [];
                ?>
                
                <?php foreach ($permissionsByModule as $module => $modulePermissions): ?>
                    <h6><?= htmlspecialchars($module) ?></h6>
                    <div class="permissions-grid">
                        <?php foreach ($modulePermissions as $permission): ?>
                            <?php
                            $hasPermission = in_array((int)$permission['id'], $rolePermissions);
                            ?>
                            <div class="permission-item">
                                <label>
                                    <input type="checkbox" 
                                           data-role-id="<?= htmlspecialchars($role['id']) ?>"
                                           data-permission-id="<?= htmlspecialchars($permission['id']) ?>"
                                           <?= $hasPermission ? 'checked' : '' ?>
                                           onchange="togglePermission(this)">
                                    <strong><?= htmlspecialchars($permission['name']) ?></strong>
                                    <small>(<?= htmlspecialchars($permission['slug']) ?>)</small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No roles found.</p>
    <?php endif; ?>
</section>

<script>
function togglePermission(checkbox) {
    const roleId = checkbox.dataset.roleId;
    const permissionId = checkbox.dataset.permissionId;
    const action = checkbox.checked ? 'assign' : 'remove';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = 'csrf_token';
    csrfToken.value = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
    
    const roleIdInput = document.createElement('input');
    roleIdInput.type = 'hidden';
    roleIdInput.name = 'role_id';
    roleIdInput.value = roleId;
    
    const permissionIdInput = document.createElement('input');
    permissionIdInput.type = 'hidden';
    permissionIdInput.name = 'permission_id';
    permissionIdInput.value = permissionId;
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    
    form.appendChild(csrfToken);
    form.appendChild(roleIdInput);
    form.appendChild(permissionIdInput);
    form.appendChild(actionInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<style>
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.permission-item {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.permission-item label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.permission-item small {
    color: #666;
    font-size: 0.9em;
}

.role-permissions-section {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
}
</style>

<?php require INCLUDES_PATH . "/footer.php"; ?>
