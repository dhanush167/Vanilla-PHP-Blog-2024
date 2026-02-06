<?php
declare(strict_types=1);

/**
 * Application constants / environment
 */

define('APP_NAME', 'Vanilla php crud application');
define('BASE_URL', '/php-blog-vanilla'); // same as your current
define('APP_ENV', 'local'); // local | production

date_default_timezone_set('Asia/Colombo');

/**
 * Error reporting
 */
if (APP_ENV === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

/**
 * Paths
 */
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('SRC_PATH', ROOT_PATH . '/src');







