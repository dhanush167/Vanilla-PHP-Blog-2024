<?php
declare(strict_types=1);

/**
 * CSRF helpers
 */

function csrf_token(): string
{
    ensure_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool
{
    ensure_session();

    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals((string)$_SESSION['csrf_token'], $token);
}

/**
 * Rotate token after successful POST (prevents replay)
 */
function csrf_rotate(): void
{
    ensure_session();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
