<?php
declare(strict_types=1);

/**
 * Simple .env file loader
 * Loads environment variables from .env file
 */
function load_env(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Set as environment variable and in $_ENV
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/**
 * Get environment variable with optional default
 */
function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value === false) {
        $value = $_ENV[$key] ?? null;
    }

    return $value !== null && $value !== false ? $value : $default;
}

// Load .env file from project root
load_env(dirname(__DIR__) . '/.env');
