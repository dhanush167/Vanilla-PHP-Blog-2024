<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title ?? 'MySite') ?></title>
    <link href="<?= htmlspecialchars(url('assets/css/style.css')) ?>" rel="stylesheet" type="text/css">
    <script src="<?= htmlspecialchars(url('assets/js/script.js')) ?>"></script>
</head>
<body>
<?php
require_once __DIR__ . '/navbar.php';
?>


