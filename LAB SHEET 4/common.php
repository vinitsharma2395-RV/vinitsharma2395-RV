<?php
declare(strict_types=1);

const COLLEGE_DB_NAME = 'college_db';

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dbHost(): string
{
    $host = getenv('COLLEGE_DB_HOST');
    $host = $host === false ? '127.0.0.1' : $host;
    if (!preg_match('/^[A-Za-z0-9.-]+$/', $host)) {
        throw new InvalidArgumentException('COLLEGE_DB_HOST must be a hostname or IPv4 address.');
    }
    return $host;
}

function dbPort(): int
{
    $value = getenv('COLLEGE_DB_PORT');
    $port = filter_var($value === false ? '3306' : $value, FILTER_VALIDATE_INT);
    if ($port === false || $port < 1 || $port > 65535) {
        throw new InvalidArgumentException('COLLEGE_DB_PORT must be between 1 and 65535.');
    }
    return $port;
}

function appDb(): PDO
{
    $user = getenv('COLLEGE_DB_USER');
    $password = getenv('COLLEGE_DB_PASSWORD');
    if ($user === false || $user === '' || $password === false || $password === '') {
        throw new RuntimeException('Set COLLEGE_DB_USER and COLLEGE_DB_PASSWORD to the restricted application account.');
    }
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        dbHost(),
        dbPort(),
        COLLEGE_DB_NAME
    );
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function adminDb(): PDO
{
    $user = getenv('COLLEGE_DB_ADMIN_USER');
    $password = getenv('COLLEGE_DB_ADMIN_PASSWORD');
    if ($user === false || $user === '') {
        throw new RuntimeException('Set COLLEGE_DB_ADMIN_USER for one-time database administration.');
    }
    if ($password === false) {
        throw new RuntimeException('Set COLLEGE_DB_ADMIN_PASSWORD (it may be empty for a local passwordless account).');
    }
    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', dbHost(), dbPort());
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function requireCsrf(bool $fromGet = false): void
{
    $token = $fromGet ? ($_GET['csrf_token'] ?? '') : ($_POST['csrf_token'] ?? '');
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        throw new InvalidArgumentException('The request token is invalid or expired. Return to the page and try again.');
    }
}

function requireStudentId($value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('A valid positive student ID is required.');
    }
    return (int) $id;
}

function requireEmail($value): string
{
    $email = trim((string) $value);
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Enter a valid email address (up to 254 characters).');
    }
    return $email;
}

function pageStart(string $title): void
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> — PHP &amp; MySQL Lab</title>
        <link rel="stylesheet" href="lab.css">
    </head>
    <body>
    <main>
        <header>
            <p><a href="01_index.php">PHP &amp; MySQL College Lab</a></p>
            <h1><?= e($title) ?></h1>
            <nav><a href="01_index.php">Exercise index</a> · <a href="07_list_students.php">Students</a> · <a href="12_authentication.php">Sign in</a> · <a href="16_theme_cookie.php">Theme</a></nav>
        </header>
    <?php
}

function pageEnd(): void
{
    echo '</main></body></html>';
}

function showError(Throwable $exception): void
{
    echo '<div class="notice error" role="alert">' . e($exception->getMessage()) . '</div>';
}
