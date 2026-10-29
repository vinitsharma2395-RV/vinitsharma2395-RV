<?php
require __DIR__ . '/common.php';

$cookieError = '';
if (isset($_GET['theme'])) {
    $requestedTheme = (string) $_GET['theme'];
    if (!in_array($requestedTheme, ['light', 'dark'], true)) {
        $cookieError = 'Choose either the light or dark theme.';
    } else {
        setcookie('college_theme', $requestedTheme, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        header('Location: 16_theme_cookie.php');
        exit;
    }
}

$theme = ($_COOKIE['college_theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
pageStart('16. Theme cookie');
?>
<style>
    body[data-theme="dark"] { background: #111827; color: #edf2fa; }
    body[data-theme="dark"] header, body[data-theme="dark"] section { background: #1d293b; border-color: #39475d; }
</style>
<section data-theme="<?= e($theme) ?>">
    <?php if ($cookieError !== ''): ?><p class="notice error"><?= e($cookieError) ?></p><?php endif; ?>
    <p>Cookie preference read from the request: <strong><?= e($theme) ?></strong>.</p>
    <p><a href="?theme=light">Set light theme</a> · <a href="?theme=dark">Set dark theme</a></p>
    <p>The cookie is configured with a 30-day expiry, SameSite=Lax, and HttpOnly.</p>
</section>
<script>
    document.body.dataset.theme = <?= json_encode($theme, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php pageEnd(); ?>
