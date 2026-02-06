<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/auth.php';

require_permission('articles.view', 'home.php');

$canCreate = current_user_has_permission('articles.create');
$canEdit   = current_user_has_permission('articles.edit');
$canDelete = current_user_has_permission('articles.delete');

$page_title = "View All Data";

$loginId = (int)($_SESSION['id'] ?? 0);
if ($loginId <= 0) {
    header('Location: ' . url('auth/login.php'));
    exit;
}

// Fetch articles for logged-in user with category names
$stmt = $mysqli->prepare(
    "SELECT a.id, a.title, a.excerpt, a.status, a.created_at, c.name as category_name
     FROM articles a
     LEFT JOIN categories c ON a.category_id = c.id
     WHERE a.user_id = ?
     ORDER BY a.id DESC"
);

if (!$stmt) {
    set_flash('error', 'Database error. Please try again.');
    header('Location: ' . url('home.php'));
    exit;
}

$stmt->bind_param("i", $loginId);
$stmt->execute();
$result = $stmt->get_result();

// Flash success message
$success = flash('success');

require INCLUDES_PATH . "/header.php";
?>

<?php if (!empty($success)): ?>
    <p style="color:#177e03;font-weight:bold;text-align:center;">
        <?= htmlspecialchars($success) ?>
    </p>
<?php endif; ?>

<?php if ($result && $result->num_rows > 0): ?>
    <table class="cool-table">
        <thead>
        <tr>
            <th>Title</th>
            <th>Excerpt</th>
            <th>Category</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars((string)$row['title']) ?></td>
                <td><?= htmlspecialchars(substr((string)$row['excerpt'], 0, 100)) . (strlen((string)$row['excerpt']) > 100 ? '...' : '') ?></td>
                <td><?= htmlspecialchars((string)$row['category_name']) ?></td>
                <td><?= (int)$row['status'] === 1 ? 'Published' : 'Draft' ?></td>
                <td><?= date('Y-m-d', strtotime((string)$row['created_at'])) ?></td>
                <td>

       <?php if ($canEdit): ?>
            <a class="tblAct__link tblAct__link--edit"
               href="<?= htmlspecialchars(url('pages/article_edit_form.php?id=' . (int)$row['id'])) ?>">
                Edit
            </a>
       <?php endif; ?>

        <?php if ($canDelete): ?>
              <a class="tblAct__link tblAct__link--delete"
                       href="#"
                       onclick="openModal(<?= (int)$row['id'] ?>); return false;">Delete
              </a>
        <?php endif; ?>

                    <?php if (!$canEdit && !$canDelete): ?>
                        <span style="color:#888;">—</span>
                    <?php endif; ?>

                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php if ($canDelete): ?>
    <!-- DELETE MODAL (POST + CSRF) -->
    <div id="deleteModal" class="modal">
        <div id="delete_model_sub">
            <h3>Confirm Deletion</h3>
            <p id="modal-text">Are you sure you want to delete this article?</p>

            <form id="deleteForm" method="POST" action="<?= htmlspecialchars(url('action/article/article_delete_action.php')) ?>">
                <input type="hidden" name="id" id="delete_id">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                <button type="submit" id="delete_model_button_confirm">Yes, Delete</button>
                <button type="button" onclick="closeModal()" id="delete_model_button_cancel">Cancel</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

<?php else: ?>
    <p style="text-align:center;color:#666;padding:40px;">
        No articles found.
        <a href="<?= htmlspecialchars(url('pages/article_add_form.php')) ?>">Add your first article</a>!
    </p>
<?php endif; ?>

<?php
$stmt->close();
require INCLUDES_PATH . "/footer.php";
?>