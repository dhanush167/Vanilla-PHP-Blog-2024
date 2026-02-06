<?php
require_once __DIR__ . '/../config/config.php';
require_permission('roles.edit');

require_once CONFIG_PATH . '/connection.php';

$roleId = (int)($_GET['id'] ?? 0);

if ($roleId === 0) {
    set_flash('error', 'Invalid role ID.');
    header('Location: ' . url('admin/roles.php'));
    exit;
}

// Fetch role
$stmt = $mysqli->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->bind_param("i", $roleId);
$stmt->execute();
$result = $stmt->get_result();
$role = $result->fetch_assoc();
$stmt->close();

if (!$role) {
    set_flash('error', 'Role not found.');
    header('Location: ' . url('admin/roles.php'));
    exit;
}

$page_title = "Edit Role";
require INCLUDES_PATH . "/header.php";

$errors = [];
$name = $role['name'];
$slug = $role['slug'];
$description = $role['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    
    // Validate CSRF
    $token = (string)($_POST['csrf_token'] ?? '');
    if ($token === '' || !verify_csrf_token($token)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        csrf_rotate();
    }
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Role name is required.';
    }
    
    if (empty($slug)) {
        $errors[] = 'Role slug is required.';
    } elseif (!preg_match('/^[a-z0-9_]+$/', $slug)) {
        $errors[] = 'Slug must contain only lowercase letters, numbers, and underscores.';
    } else {
        // Check if slug exists (excluding current role)
        $stmt = $mysqli->prepare("SELECT id FROM roles WHERE slug = ? AND id != ?");
        $stmt->bind_param("si", $slug, $roleId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'This slug is already taken.';
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE roles SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $description, $roleId);
        
        if ($stmt->execute()) {
            set_flash('success', 'Role updated successfully.');
            header('Location: ' . url('admin/roles.php'));
            exit;
        } else {
            $errors[] = 'Failed to update role.';
        }
        $stmt->close();
    }
}
?>

<section class="container">
    <div id="header">
        <h4>Edit Role</h4>
        <a href="<?= htmlspecialchars(url('admin/roles.php')) ?>" class="btn btn-secondary">Back to Roles</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <div class="form-group">
            <label for="name">Role Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>" 
                   pattern="[a-z0-9_]+" required>
            <small>Lowercase letters, numbers, and underscores only</small>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Role</button>
            <a href="<?= htmlspecialchars(url('admin/roles.php')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>
