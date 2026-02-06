<?php
declare(strict_types=1);

/**
 * Bootstrap loads helpers + session + csrf.
 * This is included by config/config.php.
 */

require_once INCLUDES_PATH . '/session.php';

// Helpers (functions used everywhere)
require_once SRC_PATH . '/Helpers/url.php';
require_once SRC_PATH . '/Helpers/flash.php';
require_once SRC_PATH . '/Helpers/sanitize.php';
require_once CONFIG_PATH . '/connection.php';
require_once SRC_PATH . '/Helpers/permissions.php';

// CSRF (depends on ensure_session())
require_once INCLUDES_PATH . '/csrf.php';

// Start session early (so flash/csrf work immediately)
ensure_session();

// Optional: periodic session id regeneration
session_regenerate_periodic(300);
