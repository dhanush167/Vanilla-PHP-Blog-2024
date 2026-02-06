<?php
declare(strict_types=1);

/**
 * Flash message helpers
 * flash('success') returns and deletes it
 */

function flash(string $key): ?string
{
    ensure_session();

    if (!empty($_SESSION[$key])) {
        $msg = (string)$_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }

    return null;
}

function set_flash(string $key, string $message): void
{
    ensure_session();
    $_SESSION[$key] = $message;
}

/**
 * Common pattern for errors arrays (optional helper)
 */
function set_errors(array $errors): void
{
    ensure_session();
    $_SESSION['errors'] = $errors;
}

function set_old(array $old): void
{
    ensure_session();
    $_SESSION['old'] = $old;
}
