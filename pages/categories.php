<?php
require_once __DIR__ . '/../config/config.php';
require_permission('categories.view');

require_once CONFIG_PATH . '/connection.php';

$page_title = "Manage Categories";
require INCLUDES_PATH . "/header.php";


// Fetch all categories with article count
$stmt = $mysqli->prepare("SELECT c.*, COUNT(a.id) as article_count
                          FROM categories c
                          LEFT JOIN articles a ON c.id = a.category_id
                          GROUP BY c.id
                          ORDER BY c.name");
$stmt->execute();
$result = $stmt->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<section class="container">
    <div id="header">
        <h4>Manage Categories</h4>
        <?php if (current_user_has_permission('categories.create')): ?>
            <a href="<?= htmlspecialchars(url('pages/category_add.php')) ?>" class="btn btn-primary">Add New Category</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($categories)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Articles</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['id']) ?></td>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                        <td><?= htmlspecialchars($cat['description'] ?? '') ?></td>
                        <td><?= htmlspecialchars($cat['article_count']) ?></td>
                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($cat['created_at']))) ?></td>
                        <td>
                            <?php if (current_user_has_permission('categories.edit')): ?>
                                <a href="<?= htmlspecialchars(url('pages/category_edit.php?id=' . $cat['id'])) ?>" class="btn btn-sm">Edit</a>
                            <?php endif; ?>
                            <?php if (current_user_has_permission('categories.delete') && (int)$cat['article_count'] === 0): ?>
                                <form method="POST" action="<?= htmlspecialchars(url('pages/category_delete_action.php')) ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="category_id" value="<?= htmlspecialchars($cat['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No categories found.</p>
    <?php endif; ?>
</section>

<?php require INCLUDES_PATH . "/footer.php"; ?>