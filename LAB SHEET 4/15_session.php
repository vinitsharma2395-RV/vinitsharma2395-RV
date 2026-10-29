<?php
require __DIR__ . '/common.php';

pageStart('15. PHP session management');
?>
<section>
    <?php if (isset($_SESSION['user_id'])): ?>
        <p class="notice">Session login is active. User: <strong><?= e($_SESSION['username'] ?? 'unknown') ?></strong>.</p>
        <p>Session user ID: <?= e($_SESSION['user_id']) ?></p>
        <p><a href="12_authentication.php">Sign out or return to authentication</a></p>
    <?php else: ?>
        <p>No login session exists. Sign in first to populate <code>$_SESSION</code>.</p>
        <p><a class="button" href="12_authentication.php">Open sign-in</a></p>
    <?php endif; ?>
    <p>This page calls <code>session_start()</code> in the shared include and reads session values across requests.</p>
</section>
<?php pageEnd(); ?>
