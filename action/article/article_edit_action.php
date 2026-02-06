<?php
declare(strict_types=1);

require_once __DIR__ . "/../../config/config.php";
require_once INCLUDES_PATH . "/auth.php";

require_permission('articles.edit', 'pages/article_view_form.php');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('pages/article_view_form.php'));
    exit;
}

/* -------------------------
   CSRF VALIDATION
-------------------------- */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf_token($token)) {
    set_errors(["Invalid or expired form submission."]);
    header('Location: ' . url('pages/article_view_form.php'));
    exit;
}

// Rotate token after valid request (prevents replay)
csrf_rotate();

/* -------------------------
   INPUT
-------------------------- */
$errors = [];

$id          = (int)($_POST['id'] ?? 0);
$title       = trim((string)($_POST['title'] ?? ''));
$excerpt     = trim((string)($_POST['excerpt'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$status      = isset($_POST['status']) ? 1 : 0;
$category_id = trim((string)($_POST['category_id'] ?? ''));
$loginId     = (int)($_SESSION['id'] ?? 0);

/* -------------------------
   VALIDATION
-------------------------- */
if ($id <= 0) {
    $errors[] = "Invalid article.";
}

if ($title === '') {
    $errors[] = "Title field is required.";
} elseif (mb_strlen($title) > 100) {
    $errors[] = "Title is too long (max 100 characters).";
}

if ($excerpt === '') {
    $errors[] = "Excerpt field is required.";
}

if ($description === '') {
    $errors[] = "Description field is required.";
}

if ($category_id === '' || !ctype_digit($category_id)) {
    $errors[] = "Please select a valid category.";
} else {
    $category_id = (int)$category_id;
}

if ($loginId <= 0) {
    $errors[] = "Session expired. Please log in again.";
}

if (!empty($errors)) {
    set_errors($errors);
    header('Location: ' . url('pages/article_edit_form.php?id=' . $id));
    exit;
}

/* -------------------------
   FETCH EXISTING ARTICLE (for slug generation)
-------------------------- */
$stmt = $mysqli->prepare(
    "SELECT title, slug FROM articles WHERE id = ? AND user_id = ?"
);
if (!$stmt) {
    set_errors(["Database error. Please try again."]);
    header('Location: ' . url('pages/article_edit_form.php?id=' . $id));
    exit;
}
$stmt->bind_param("ii", $id, $loginId);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();
$stmt->close();

if (!$article) {
    // Article not found or doesn't belong to user
    set_flash('error', "Article not found or you are not authorized.");
    header('Location: ' . url('pages/article_view_form.php'));
    exit;
}

$oldTitle = $article['title'];
$oldSlug = $article['slug'];

// Generate slug only if title changed
if ($oldTitle !== $title) {
    // Generate base slug from title
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    // Ensure slug uniqueness (excluding current article)
    $counter = 1;
    $originalSlug = $slug;
    $checkStmt = $mysqli->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
    if (!$checkStmt) {
        set_errors(["Database error. Please try again."]);
        header('Location: ' . url('pages/article_edit_form.php?id=' . $id));
        exit;
    }

    while (true) {
        $checkStmt->bind_param("si", $slug, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows === 0) {
            break;
        }
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    $checkStmt->close();
} else {
    // Keep existing slug
    $slug = $oldSlug;
}

/* -------------------------
   UPDATE (authorized)
-------------------------- */
$stmt = $mysqli->prepare(
    "UPDATE articles
     SET title = ?, slug = ?, excerpt = ?, description = ?, status = ?, category_id = ?
     WHERE id = ? AND user_id = ?"
);

if (!$stmt) {
    set_errors(["Database error. Please try again."]);
    header('Location: ' . url('pages/article_edit_form.php?id=' . $id));
    exit;
}

// title(string), slug(string), excerpt(string), description(string), status(int), category_id(int), id(int), user_id(int)
$stmt->bind_param("ssssiiii", $title, $slug, $excerpt, $description, $status, $category_id, $id, $loginId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $stmt->close();
    set_flash('success', "Article updated successfully.");
    header('Location: ' . url('pages/article_view_form.php'));
    exit;
}

$stmt->close();

// Could be "no changes" OR unauthorized/not found
set_flash('success', "No changes were made.");
header('Location: ' . url('pages/article_edit_form.php?id=' . $id));
exit;
