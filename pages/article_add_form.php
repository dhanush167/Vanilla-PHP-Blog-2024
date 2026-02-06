<?php
require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/auth.php';

require_permission('articles.create', 'pages/article_view_form.php');

$page_title = "Add Data";

// Flash messages (success + errors + old input)
$success = flash('success') ?? '';
$errors  = $_SESSION['errors'] ?? [];
$old     = $_SESSION['old'] ?? ['title' => '', 'excerpt' => '', 'description' => '', 'status' => '', 'category_id' => ''];

// Fetch categories for dropdown
$categories = [];
$categories_result = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
    $categories_result->free();
}

unset($_SESSION['errors'], $_SESSION['old']);

require INCLUDES_PATH . "/header.php";
?>

<form class="login-form" method="post" action="<?= htmlspecialchars(url('action/article/article_add_action.php')) ?>" autocomplete="off">
    <h2>Add Article</h2>

    <!-- SUCCESS MESSAGE -->
    <?php if (!empty($success)): ?>
        <p style="color: #177e03; font-weight: bold;">
            <?= htmlspecialchars($success) ?>
        </p>
    <?php endif; ?>

    <!-- ERROR MESSAGES -->
    <?php if (!empty($errors)): ?>
        <?php foreach ((array)$errors as $error): ?>
            <p style="color: #c1121f;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- CSRF TOKEN -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <div class="form-group">
        <label for="title">Title</label>
        <input id="title" type="text" name="title" required maxlength="100"
               value="<?= htmlspecialchars((string)$old['title']) ?>">
    </div>

    <div class="form-group">
        <label for="excerpt">Excerpt</label>
        <textarea id="excerpt" name="excerpt" required rows="3"><?= htmlspecialchars((string)$old['excerpt']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" required rows="6"><?= htmlspecialchars((string)$old['description']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $id => $name): ?>
                <option value="<?= (int)$id ?>" <?= ((int)($old['category_id'] ?? 0) === (int)$id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="status" value="1" <?= !empty($old['status']) ? 'checked' : '' ?>>
            Published
        </label>
    </div>

    <div class="form-group">
        <button type="submit" name="Submit" class="login-btn">Submit</button>
    </div>
</form>

<?php require INCLUDES_PATH . "/footer.php"; ?>
