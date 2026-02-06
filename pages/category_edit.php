<?php
require_once __DIR__ . '/../config/config.php';
require_permission('categories.edit');

require_once CONFIG_PATH . '/connection.php';

$categoryId = (int)($_GET['id'] ?? 0);

if ($categoryId === 0) {
    set_flash('error', 'Invalid category ID.');
    header('Location: ' . url('pages/categories.php'));
    exit;
}

// Fetch category
$stmt = $mysqli->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

if (!$category) {
    set_flash('error', 'Category not found.');
    header('Location: ' . url('pages/categories.php'));
    exit;
}

$page_title = "Edit Category";
require INCLUDES_PATH . "/header.php";

$errors = [];
$name = $category['name'];
$slug = $category['slug'];
$description = $category['description'] ?? '';

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
        $errors[] = 'Category name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Name is too long (max 100 characters).';
    }

    if (empty($slug)) {
        // Auto-generate slug from name if empty
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
    } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
        $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
    }

    // Check name uniqueness (excluding current category)
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->bind_param("si", $name, $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'This category name is already taken.';
        }
        $stmt->close();
    }

    // Check slug uniqueness (excluding current category)
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->bind_param("si", $slug, $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'This slug is already taken.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $description, $categoryId);

        if ($stmt->execute()) {
            set_flash('success', 'Category updated successfully.');
            header('Location: ' . url('pages/categories.php'));
            exit;
        } else {
            $errors[] = 'Failed to update category.';
        }
        $stmt->close();
    }
}
?>

<section class="container">
    <div id="header">
        <h4>Edit Category</h4>
        <a href="<?= htmlspecialchars(url('pages/categories.php')) ?>" class="btn btn-secondary">Back to Categories</a>
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
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="form-group">
            <label for="name">Category Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>"
                   pattern="[a-z0-9\-]+">
            <small>Lowercase letters, numbers, and hyphens only. Leave empty to auto‑generate.</small>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Category</button>
            <a href="<?= htmlspecialchars(url('pages/categories.php')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    // Only auto-generate if slug field is empty or matches the previously auto-generated slug
    if (!slugField.dataset.autoGenerated) {
        slugField.dataset.autoGenerated = slugField.value;
    }
    if (slugField.value === '' || slugField.value === slugField.dataset.autoGenerated) {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugField.value = slug;
        slugField.dataset.autoGenerated = slug;
    }
});
</script>

<?php require INCLUDES_PATH . "/footer.php"; ?>