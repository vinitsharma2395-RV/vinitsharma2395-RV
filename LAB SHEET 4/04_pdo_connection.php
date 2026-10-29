<?php
require __DIR__ . '/common.php';

pageStart('4. Secure PDO connection');
?>
<section>
    <p>Credentials come from <code>COLLEGE_DB_USER</code> and <code>COLLEGE_DB_PASSWORD</code>, not source code. PDO exceptions are enabled and emulated prepared statements are disabled.</p>
    <?php
    try {
        $pdo = appDb();
        $pdo->query('SELECT 1');
        echo '<p class="notice">PDO connected successfully to <code>' . e(COLLEGE_DB_NAME) . '</code>.</p>';
    } catch (RuntimeException | InvalidArgumentException $exception) {
        showError($exception);
    }
    ?>
</section>
<?php pageEnd(); ?>
