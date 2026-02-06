<?php
declare(strict_types=1);

require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/connection.php";
require_permission('articles.create', 'pages/article_view_form.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . url('pages/article_add_form.php'));
    exit;
}

/* -------------------------
   CSRF VALIDATION
-------------------------- */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf_token($token)) {
    set_errors(["Invalid or expired form submission."]);
    header("Location: " . url('pages/article_add_form.php'));
    exit;
}

// Rotate token after a valid request (prevents replay)
csrf_rotate();

/* -------------------------
   INPUT
-------------------------- */
$title       = trim((string)($_POST['title'] ?? ''));
$excerpt     = trim((string)($_POST['excerpt'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$status      = isset($_POST['status']) ? 1 : 0;
$category_id = trim((string)($_POST['category_id'] ?? ''));

$loginId = (int)($_SESSION['id'] ?? 0);

/* -------------------------
   VALIDATION
-------------------------- */
$errors = [];

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

// Generate slug from title
$slug = strtolower($title);
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim($slug, '-');

// Ensure slug uniqueness
$counter = 1;
$originalSlug = $slug;
$checkStmt = $mysqli->prepare("SELECT id FROM articles WHERE slug = ?");
if (!$checkStmt) {
    set_errors(["Database error. Please try again."]);
    set_old([
        'title'       => $title,
        'excerpt'     => $excerpt,
        'description' => $description,
        'status'      => $status,
        'category_id' => $category_id,
    ]);
    header("Location: " . url('pages/article_add_form.php'));
    exit;
}

while (true) {
    $checkStmt->bind_param("s", $slug);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows === 0) {
        break;
    }
    $slug = $originalSlug . '-' . $counter;
    $counter++;
}
$checkStmt->close();

if (!empty($errors)) {
    set_errors($errors);
    set_old([
        'title'       => $title,
        'excerpt'     => $excerpt,
        'description' => $description,
        'status'      => $status,
        'category_id' => $category_id,
    ]);
    header("Location: " . url('pages/article_add_form.php'));
    exit;
}

/* -------------------------
   INSERT
-------------------------- */
$stmt = $mysqli->prepare(
    "INSERT INTO articles (title, slug, excerpt, description, status, user_id, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    set_errors(["Database error. Please try again."]);
    header("Location: " . url('pages/article_add_form.php'));
    exit;
}

$stmt->bind_param("ssssiii", $title, $slug, $excerpt, $description, $status, $loginId, $category_id);

if ($stmt->execute()) {
    set_flash('success', "Article added successfully.");
    header("Location: " . url('pages/article_view_form.php'));
    exit;
}

set_errors(["Database error. Please try again."]);
set_old([
    'title'       => $title,
    'excerpt'     => $excerpt,
    'description' => $description,
    'status'      => $status,
    'category_id' => $category_id,
]);
header("Location: " . url('pages/article_add_form.php'));
exit;
