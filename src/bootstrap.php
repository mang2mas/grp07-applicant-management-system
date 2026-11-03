<?php
/**
 * AMS bootstrap: constants, environment, session, security headers, helpers.
 * Included once by public/index.php.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', __DIR__);

// Never show raw PHP errors to visitors; they go to the server log/console.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');

require_once BASE_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Content-Type: text/html; charset=UTF-8');
