<?php
/**
 * Applicant Management System (AMS) - Group 7
 * File: config/database.php
 *
 * PDO connection module for PostgreSQL.
 *
 * Credentials are NEVER hardcoded. They are read from environment variables,
 * which are loaded from a local, git-ignored `.env` file in the project root
 * (copy `.env.example` to `.env` and fill in your own values).
 *
 * Supported variables:
 *   DB_HOST      (default: 127.0.0.1)
 *   DB_PORT      (default: 5432)
 *   DB_NAME      (default: grp07_applicantdb)
 *   DB_USER      (required)
 *   DB_PASSWORD  (required; may be empty only for trust-auth local setups)
 *   DB_SSLMODE   (default: prefer; e.g. disable, require)
 *
 * Usage:
 *   require_once __DIR__ . '/../config/database.php';
 *   $pdo  = db();
 *   $stmt = $pdo->prepare('SELECT * FROM applicant WHERE applicant_id = :id');
 *   $stmt->execute([':id' => 1]);
 */

declare(strict_types=1);

/**
 * Load KEY=VALUE pairs from a .env file into the environment.
 * Existing real environment variables are never overwritten.
 */
function load_env(string $path): void
{
    if (!is_readable($path)) {
        return; // No .env file: fall back to real environment variables.
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and malformed lines.
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Strip matching surrounding quotes.
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        if ($key === '' || getenv($key) !== false) {
            continue; // Real environment wins over .env.
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

/**
 * Read an environment variable with an optional default.
 */
function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

/**
 * Return a shared PDO connection (created on first call).
 *
 * @throws RuntimeException on missing configuration or connection failure.
 *         The message is intentionally generic so credentials/host details
 *         are never leaked to the browser; details go to the PHP error log.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    load_env(dirname(__DIR__) . '/.env');

    $host     = env('DB_HOST', '127.0.0.1');
    $port     = env('DB_PORT', '5432');
    $name     = env('DB_NAME', 'grp07_applicantdb');
    $user     = env('DB_USER');
    $password = getenv('DB_PASSWORD'); // may legitimately be empty
    $sslmode  = env('DB_SSLMODE', 'prefer');

    if ($user === null || $password === false) {
        throw new RuntimeException(
            'Database configuration is incomplete. Copy .env.example to .env and set DB_USER and DB_PASSWORD.'
        );
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
        $host,
        $port,
        $name,
        $sslmode
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw on SQL errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statements
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $password, $options);
        $pdo->exec("SET client_encoding TO 'UTF8'");
    } catch (PDOException $e) {
        error_log('[AMS] Database connection failed: ' . $e->getMessage());
        throw new RuntimeException(
            'Unable to connect to the database. Check your .env settings and that PostgreSQL is running.',
            0,
            $e
        );
    }

    return $pdo;
}
