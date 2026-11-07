<?php
/**
 * POST action: sign out. Destroys the session, then returns to the login page.
 */

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $cookie['path'],
        'domain'   => $cookie['domain'],
        'secure'   => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'] ?: 'Lax',
    ]);
}
session_destroy();

// Start a clean session only to carry the "signed out" message to the login page.
session_start();
session_regenerate_id(true);
flash_add('info', 'You have been signed out.');
redirect(url('login'));
