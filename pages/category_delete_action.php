<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/config.php";
require_once INCLUDES_PATH . "/auth.php";
require_permission('categories.delete');

/* -------------------------
   POST ONLY
-------------------------- */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . url('pages/categories.php'));
    exit;
}

/* -------------------------
   CSRF VALIDATION
-------------------------- */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf_token($token)) {
    set_flash('error', 'Invalid or expired request.');
    header("Location: " . url('pages/categories.php'));
    exit;
}

// Rotate token after valid request (prevents replay)
csrf_rotate();

/* -------------------------
   VALIDATE ID
-------------------------- */
$categoryId = (int)($_POST['category_id'] ?? 0);
$userId = (int)($_SESSION['id'] ?? 0);

if ($categoryId <= 0 || $userId <= 0) {
    header("Location: " . url('pages/categories.php'));
    exit;
}

/* -------------------------
   CHECK CATEGORY AND ARTICLE COUNT
-------------------------- */
$stmt = $mysqli->prepare("SELECT c.*, COUNT(a.id) as article_count
                          FROM categories c
                          LEFT JOIN articles a ON c.id = a.category_id
                          WHERE c.id = ?
                          GROUP BY c.id");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

if (!$category) {
    set_flash('error', 'Category not found.');
    header("Location: " . url('pages/categories.php'));
    exit;
}

$articleCount = (int)$category['article_count'];
if ($articleCount > 0) {
    set_flash('error', "Cannot delete category: it has $articleCount article(s). Delete articles first.");
    header("Location: " . url('pages/categories.php'));
    exit;
}

/* -------------------------
   DELETE QUERY (SAFE)
-------------------------- */
$stmt = $mysqli->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();

/* -------------------------
   SUCCESS MESSAGE
-------------------------- */
if ($stmt->affected_rows > 0) {
    set_flash('success', "Category deleted successfully.");
}

header("Location: " . url('pages/categories.php'));
exit;