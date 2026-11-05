<?php
/**
 * POST action: authenticate with username + password.
 * The password is checked with password_verify() against the BCRYPT hash in `users`.
 * On success the session holds user_id, username and role (Administrator | Staff | Viewer).
 */

// A real BCRYPT hash checked when the username does not exist, so the response
// time does not reveal which usernames are valid.
const LOGIN_DUMMY_HASH = '$2y$10$AhMBxxcAenJP0l3YOlt8weAhSeGowlVUAkHslMN4KvhMW2hBpm9FC';

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

$user = null;
if ($username !== '' && mb_strlen($username) <= 50) {
    $user = db_query(
        'SELECT user_id, username, password_hash, role FROM users WHERE username = :username',
        [':username' => $username]
    )->fetch() ?: null;
}

$valid = password_verify($password, $user['password_hash'] ?? LOGIN_DUMMY_HASH) && $user !== null;

if (!$valid) {
    form_state_set([], ['username' => $username]);
    flash_add('error', 'Invalid username or password.');
    redirect(url('login'));
}

// Upgrade the stored hash transparently if the algorithm or cost changed.
if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
    db_query(
        'UPDATE users SET password_hash = :hash WHERE user_id = :id',
        [':hash' => password_hash($password, PASSWORD_BCRYPT), ':id' => (int) $user['user_id']]
    );
}

session_regenerate_id(true);   // prevent session fixation
unset($_SESSION['csrf']);      // issue a fresh CSRF token for the signed-in session

$_SESSION['user_id']  = (int) $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

flash_add('success', 'Welcome, ' . $user['username'] . '.');
redirect(url('dashboard'));
