<?php
declare(strict_types=1);

/**
 * Session security + auth helpers
 * IMPORTANT: session ini settings must be set BEFORE session_start()
 */

/** Session timeout duration (30 minutes) */
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 1800);
}

/**
 * Configure session cookie/security settings (call before session_start)
 */
function session_configure(): void
{
    // Only configure once
    static $configured = false;
    if ($configured) return;
    $configured = true;

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    // Secure cookie only on HTTPS
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');

    // PHP < 7.3 has issues with cookie_samesite ini, but in modern PHP it's ok
    // If you get warnings, you can remove this line and set SameSite via setcookie() manually.
    ini_set('session.cookie_samesite', 'Strict');
}

/**
 * Start session safely
 */
function ensure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_configure();
    session_start();
}

/**
 * Optional: periodic session id regeneration
 */
function session_regenerate_periodic(int $seconds = 300): void
{
    ensure_session();

    $now = time();
    $last = (int)($_SESSION['regenerated'] ?? 0);

    if ($last === 0) {
        session_regenerate_id(true);
        $_SESSION['regenerated'] = $now;
        return;
    }

    if (($now - $last) >= $seconds) {
        session_regenerate_id(true);
        $_SESSION['regenerated'] = $now;
    }
}

/**
 * Check session timeout and redirect if expired
 */
function check_session_timeout(): void
{
    ensure_session();

    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - (int)$_SESSION['last_activity'];

        if ($elapsed > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            header('Location: ' . url('auth/login.php?timeout=1'));
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
}

/**
 * Auth state
 */
function is_authenticated(): bool
{
    ensure_session();
    return !empty($_SESSION['valid']) && !empty($_SESSION['id']);
}

/**
 * Require login on protected pages
 */
function require_auth(): void
{
    check_session_timeout();

    if (!is_authenticated()) {
        header('Location: ' . url('auth/login.php'));
        exit;
    }
}

/**
 * Logout helper (optional)
 */
function logout_user(): void
{
    ensure_session();
    session_unset();
    session_destroy();
    header('Location: ' . url('auth/login.php'));
    exit;
}
