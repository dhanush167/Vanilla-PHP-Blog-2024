<?php
declare(strict_types=1);

/**
 * Database connection using environment variables
 * mysql_connect is deprecated - using mysqli_connect instead
 */

// Load environment variables
require_once __DIR__ . '/env.php';

$databaseHost = env('DB_HOST', 'localhost');
$databaseName = env('DB_NAME', 'blog');
$databaseUsername = env('DB_USERNAME', 'root');
$databasePassword = env('DB_PASSWORD', '');

$mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);

if ($mysqli->connect_errno) {
    die("DB connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');
