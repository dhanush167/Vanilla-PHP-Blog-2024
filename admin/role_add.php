<?php
require_once __DIR__ . '/../config/config.php';
require_permission('roles.create');

require_once CONFIG_PATH . '/connection.php';

$page_title = "Add New Role";
require INCLUDES_PATH . "/header.php";

$errors = [];
$name = '';
$slug = '';
$description = '';

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
        // Check if slug exists
        $stmt = $mysqli->prepare("SELECT id FROM roles WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'This slug is already taken.';
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO roles (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $slug, $description);
        
        if ($stmt->execute()) {
            set_flash('success', 'Role created successfully.');
            header('Location: ' . url('admin/roles.php'));
            exit;
        } else {
            $errors[] = 'Failed to create role.';
        }
        $stmt->close();
    }
}
?>

<section class="container">
    <div id="header">
        <h4>Add New Role</h4>
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
            <small>Lowercase letters, numbers, and underscores only (e.g., 'content_manager')</small>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Role</button>
            <a href="<?= htmlspecialchars(url('admin/roles.php')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
    document.getElementById('slug').value = slug;
});
</script>

<?php require INCLUDES_PATH . "/footer.php"; ?>
