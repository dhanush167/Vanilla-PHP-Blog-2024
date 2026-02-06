<?php
require_once __DIR__ . '/../config/config.php';
require_permission('permissions.edit');

require_once CONFIG_PATH . '/connection.php';

$permissionId = (int)($_GET['id'] ?? 0);

if ($permissionId === 0) {
    set_flash('error', 'Invalid permission ID.');
    header('Location: ' . url('admin/permissions.php'));
    exit;
}

// Fetch permission
$stmt = $mysqli->prepare("SELECT * FROM permissions WHERE id = ?");
$stmt->bind_param("i", $permissionId);
$stmt->execute();
$result = $stmt->get_result();
$permission = $result->fetch_assoc();
$stmt->close();

if (!$permission) {
    set_flash('error', 'Permission not found.');
    header('Location: ' . url('admin/permissions.php'));
    exit;
}

$page_title = "Edit Permission";
require INCLUDES_PATH . "/header.php";

$errors = [];
$name = $permission['name'];
$slug = $permission['slug'];
$description = $permission['description'] ?? '';
$module = $permission['module'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $module = trim((string)($_POST['module'] ?? ''));
    
    // Validate CSRF
    $token = (string)($_POST['csrf_token'] ?? '');
    if ($token === '' || !verify_csrf_token($token)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        csrf_rotate();
    }
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Permission name is required.';
    }
    
    if (empty($slug)) {
        $errors[] = 'Permission slug is required.';
    } elseif (!preg_match('/^[a-z0-9._]+$/', $slug)) {
        $errors[] = 'Slug must contain only lowercase letters, numbers, dots, and underscores.';
    } else {
        // Check if slug exists (excluding current permission)
        $stmt = $mysqli->prepare("SELECT id FROM permissions WHERE slug = ? AND id != ?");
        $stmt->bind_param("si", $slug, $permissionId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'This slug is already taken.';
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE permissions SET name = ?, slug = ?, description = ?, module = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $slug, $description, $module, $permissionId);
        
        if ($stmt->execute()) {
            set_flash('success', 'Permission updated successfully.');
            header('Location: ' . url('admin/permissions.php'));
            exit;
        } else {
            $errors[] = 'Failed to update permission.';
        }
        $stmt->close();
    }
}

// Get existing modules for suggestions
$stmt = $mysqli->prepare("SELECT DISTINCT module FROM permissions WHERE module IS NOT NULL AND module != '' ORDER BY module");
$stmt->execute();
$modulesResult = $stmt->get_result();
$existingModules = $modulesResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<section class="container">
    <div id="header">
        <h4>Edit Permission</h4>
        <a href="<?= htmlspecialchars(url('admin/permissions.php')) ?>" class="btn btn-secondary">Back to Permissions</a>
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
            <label for="name">Permission Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>" 
                   pattern="[a-z0-9._]+" required>
        </div>
        
        <div class="form-group">
            <label for="module">Module</label>
            <input type="text" id="module" name="module" value="<?= htmlspecialchars($module) ?>" 
                   list="modules">
            <datalist id="modules">
                <?php foreach ($existingModules as $mod): ?>
                    <option value="<?= htmlspecialchars($mod['module']) ?>">
                <?php endforeach; ?>
            </datalist>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Permission</button>
            <a href="<?= htmlspecialchars(url('admin/permissions.php')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>
