<?php
declare(strict_types=1);

require_once __DIR__ . "/../../config/config.php";
require_once INCLUDES_PATH . "/auth.php";
require_permission('articles.delete', 'pages/article_view_form.php');

/* -------------------------
   POST ONLY
-------------------------- */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . url('pages/article_view_form.php'));
    exit;
}

/* -------------------------
   CSRF VALIDATION
-------------------------- */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf_token($token)) {
    set_flash('error', 'Invalid or expired request.');
    header("Location: " . url('pages/article_view_form.php'));
    exit;
}

// Rotate token after valid request (prevents replay)
csrf_rotate();

/* -------------------------
   VALIDATE ID
-------------------------- */
$id = (int)($_POST['id'] ?? 0);
$user_id = (int)($_SESSION['id'] ?? 0);

if ($id <= 0 || $user_id <= 0) {
    header("Location: " . url('pages/article_view_form.php'));
    exit;
}

/* -------------------------
   DELETE QUERY (SAFE)
-------------------------- */
$stmt = $mysqli->prepare(
    "DELETE FROM articles WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

/* -------------------------
   SUCCESS MESSAGE
-------------------------- */
if ($stmt->affected_rows > 0) {
    set_flash('success', "Article deleted successfully.");
}

header("Location: " . url('pages/article_view_form.php'));
exit;
