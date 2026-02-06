<?php
require_once __DIR__ . '/../config/config.php';
require_permission('users.manage_roles');

require_once CONFIG_PATH . '/connection.php';

$page_title = "Manage User Roles";
require INCLUDES_PATH . "/header.php";

// Handle role assignment/removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $roleId = (int)($_POST['role_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    
    if ($userId > 0 && $roleId > 0) {
        if ($action === 'assign') {
            // Check if already assigned
            $stmt = $mysqli->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?");
            $stmt->bind_param("ii", $userId, $roleId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                $stmt = $mysqli->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $userId, $roleId);
                if ($stmt->execute()) {
                    set_flash('success', 'Role assigned successfully.');
                } else {
                    set_flash('error', 'Failed to assign role.');
                }
            } else {
                set_flash('info', 'User already has this role.');
            }
            $stmt->close();
        } elseif ($action === 'remove') {
            $stmt = $mysqli->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
            $stmt->bind_param("ii", $userId, $roleId);
            if ($stmt->execute()) {
                set_flash('success', 'Role removed successfully.');
            } else {
                set_flash('error', 'Failed to remove role.');
            }
            $stmt->close();
        }
    }
    header('Location: ' . url('admin/user_roles.php'));
    exit;
}

// Fetch all users with their roles
$usersResult = $mysqli->query("SELECT id, name, username, email FROM login ORDER BY name");
$users = $usersResult ? $usersResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch all roles
$rolesResult = $mysqli->query("SELECT id, name, slug FROM roles ORDER BY name");
$roles = $rolesResult ? $rolesResult->fetch_all(MYSQLI_ASSOC) : [];

// Get user roles mapping
$userRolesMap = [];
$userRolesResult = $mysqli->query("SELECT user_id, role_id FROM user_roles");
if ($userRolesResult) {
    while ($row = $userRolesResult->fetch_assoc()) {
        $userId = (int)$row['user_id'];
        if (!isset($userRolesMap[$userId])) {
            $userRolesMap[$userId] = [];
        }
        $userRolesMap[$userId][] = (int)$row['role_id'];
    }
}
?>

<section class="container">
    <div id="header">
        <h4>Manage User Roles</h4>
    </div>

    <?php if (!empty($users)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Current Roles</th>
                    <th>Assign Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php
                            $userRoles = $userRolesMap[(int)$user['id']] ?? [];
                            if (empty($userRoles)):
                                echo '<span class="text-muted">No roles assigned</span>';
                            else:
                                foreach ($userRoles as $roleId):
                                    $role = array_filter($roles, function($r) use ($roleId) { return (int)$r['id'] === $roleId; });
                                    $role = reset($role);
                                    if ($role):
                            ?>
                                        <span class="badge">
                                            <?= htmlspecialchars($role['name']) ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this role?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                                <input type="hidden" name="role_id" value="<?= htmlspecialchars($roleId) ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button type="submit" class="btn-link" style="color:red; padding:0; margin-left:5px;">×</button>
                                            </form>
                                        </span>
                            <?php
                                    endif;
                                endforeach;
                            endif;
                            ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline-flex; gap:5px;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <input type="hidden" name="action" value="assign">
                                <select name="role_id" required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                        <?php if (!in_array((int)$role['id'], $userRoles)): ?>
                                            <option value="<?= htmlspecialchars($role['id']) ?>">
                                                <?= htmlspecialchars($role['name']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>
