<?php
require_once __DIR__ . '/../config/config.php';
require_permission('permissions.create');

require_once CONFIG_PATH . '/connection.php';

$page_title = "Add New Permission";
require INCLUDES_PATH . "/header.php";

$errors = [];
$name = '';
$slug = '';
$description = '';
$module = '';

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
        // Check if slug exists
        $stmt = $mysqli->prepare("SELECT id FROM permissions WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'This slug is already taken.';
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO permissions (name, slug, description, module) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $slug, $description, $module);
        
        if ($stmt->execute()) {
            set_flash('success', 'Permission created successfully.');
            header('Location: ' . url('admin/permissions.php'));
            exit;
        } else {
            $errors[] = 'Failed to create permission.';
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
        <h4>Add New Permission</h4>
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
            <small>Format: module.action (e.g., 'users.create', 'articles.delete')</small>
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
            <small>Group permissions by module (e.g., 'users', 'articles', 'categories', 'admin')</small>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Permission</button>
            <a href="<?= htmlspecialchars(url('admin/permissions.php')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9]+/g, '.')
        .replace(/^\.+|\.+$/g, '');
    document.getElementById('slug').value = slug;
});
</script>

<?php require INCLUDES_PATH . "/footer.php"; ?>
