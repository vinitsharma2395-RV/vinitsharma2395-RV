<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        $dsn = getenv('BOOKSTORE_DSN') ?: 'mysql:host=127.0.0.1;dbname=bookstore;charset=utf8mb4';
        $username = getenv('BOOKSTORE_DB_USER') ?: 'root';
        $password = getenv('BOOKSTORE_DB_PASSWORD') ?: '';
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page, and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('Please sign in to continue.', 'notice');
        redirect('/LABSHEET%207/login.php');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('You do not have permission to view this page.');
    }
    return $user;
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $stored = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $stored;
}

function money(mixed $amount): string
{
    return '$' . number_format((float) $amount, 2);
}

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function book_image(?string $image): ?string
{
    if (!$image) return null;
    if (str_starts_with($image, '/') || preg_match('~^https?://~i', $image)) return $image;
    return null;
}

function render_header(string $title): void
{
    $user = current_user();
    $cartCount = array_sum(array_map('intval', $_SESSION['cart'] ?? []));
    $notice = flash();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · Paper & Pine</title>
        <link rel="stylesheet" href="/LABSHEET%207/assets/style.css">
        <script src="/LABSHEET%207/assets/app.js" defer></script>
    </head>
    <body>
    <header class="site-header">
        <a class="brand" href="/LABSHEET%207/index.php" aria-label="Paper and Pine home"><span class="brand-mark">P</span><span>paper<span class="brand-amp">&</span>pine</span></a>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="/LABSHEET%207/index.php#browse">Browse</a>
            <a href="/LABSHEET%207/index.php#about">Our story</a>
            <?php if ($user): ?><a href="/LABSHEET%207/orders.php">My orders</a><?php endif; ?>
            <?php if ($user): ?><a href="/LABSHEET%207/wishlist.php">Wish list</a><?php endif; ?>
            <?php if ($user && $user['role'] === 'admin'): ?><a href="/LABSHEET%207/admin/index.php">Admin</a><?php endif; ?>
        </nav>
        <div class="header-actions">
            <?php if ($user): ?>
                <span class="welcome-name">Hi, <?= e(explode(' ', $user['name'])[0]) ?></span>
                <form class="logout-form" method="post" action="/LABSHEET%207/logout.php"><?= csrf_field() ?><button class="text-link" type="submit">Sign out</button></form>
            <?php else: ?>
                <a class="text-link" href="/LABSHEET%207/login.php">Sign in</a>
                <a class="button button-small button-outline" href="/LABSHEET%207/register.php">Create account</a>
            <?php endif; ?>
            <a class="cart-link" href="/LABSHEET%207/cart.php" aria-label="Shopping bag, <?= (int) $cartCount ?> items"><span aria-hidden="true">♧</span><span>Bag</span><b><?= (int) $cartCount ?></b></a>
        </div>
    </header>
    <main class="page-shell">
        <?php if ($notice): ?><div class="flash flash-<?= e($notice['type']) ?>" role="status"><?= e($notice['message']) ?></div><?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="site-footer" id="about">
        <div><a class="brand brand-footer" href="/LABSHEET%207/index.php"><span class="brand-mark">P</span><span>paper<span class="brand-amp">&</span>pine</span></a><p>Good books. Thoughtful finds. A little more wonder in every day.</p></div>
        <div class="footer-note">Made for readers, wherever you are.<br><span>© <?= date('Y') ?> Paper & Pine Bookshop</span></div>
    </footer>
    </body>
    </html>
    <?php
}
