<?php declare(strict_types=1);

ob_start();

require_once __DIR__ . '/../config/config.php';  // Fixed: Added missing slash

// Everything is now handled by config.php!
require_auth(); // This single function does everything

// Optional: Regenerate session ID periodically for extra security
if (empty($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
} elseif ((time() - $_SESSION['regenerated']) > 300) { // Every 5 minutes
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}