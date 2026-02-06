
<?php
require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/auth.php';

require_permission('articles.edit', 'pages/article_view_form.php');


$page_title = "Edit Article";

/* -------------------------
   VALIDATE & FETCH ARTICLE
-------------------------- */
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . url('pages/article_view_form.php'));
    exit;
}

$loginId = (int)($_SESSION['id'] ?? 0);
if ($loginId <= 0) {
    header('Location: ' . url('auth/login.php'));
    exit;
}

// Only fetch article that belongs to logged-in user
$stmt = $mysqli->prepare(
    "SELECT title, excerpt, description, status, category_id
     FROM articles
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $id, $loginId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    header('Location: ' . url('pages/article_view_form.php'));
    exit;
}

$article = $result->fetch_assoc();
$stmt->close();

// Fetch categories for dropdown
$categories = [];
$categories_result = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
    $categories_result->free();
}

// Errors (one-time)
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

require INCLUDES_PATH . "/header.php";
?>

<form method="post" action="<?= htmlspecialchars(url('action/article/article_edit_action.php')) ?>" class="login-form">
    <h2>Edit Article</h2>

    <?php if (!empty($errors)): ?>
        <?php foreach ((array)$errors as $error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- CSRF TOKEN -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <div class="form-group">
        <label for="title">Title</label>
        <input id="title" type="text" name="title" required maxlength="100"
               value="<?= htmlspecialchars((string)$article['title']) ?>">
    </div>

    <div class="form-group">
        <label for="excerpt">Excerpt</label>
        <textarea id="excerpt" name="excerpt" required rows="3"><?= htmlspecialchars((string)$article['excerpt']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" required rows="6"><?= htmlspecialchars((string)$article['description']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $cat_id => $cat_name): ?>
                <option value="<?= (int)$cat_id ?>" <?= ((int)$article['category_id'] === (int)$cat_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat_name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="status" value="1" <?= ((int)$article['status'] === 1) ? 'checked' : '' ?>>
            Published
        </label>
    </div>

    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <button type="submit" name="Update" class="login-btn">Update</button>
</form>

<?php require INCLUDES_PATH . "/footer.php"; ?>
