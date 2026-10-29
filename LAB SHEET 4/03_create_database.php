<?php
require __DIR__ . '/common.php';

pageStart('3. Create the MySQL database');
?>
<section>
    <p>This action creates <code>college_db</code> using a separate MySQL administrator account. Set <code>COLLEGE_DB_ADMIN_USER</code> and <code>COLLEGE_DB_ADMIN_PASSWORD</code> in the Apache/PHP environment first.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            requireCsrf();
            $admin = adminDb();
            $admin->exec('CREATE DATABASE IF NOT EXISTS `' . COLLEGE_DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            echo '<p class="notice">Database <code>' . e(COLLEGE_DB_NAME) . '</code> is ready.</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="post">
        <?php csrfField(); ?>
        <button type="submit">Create database</button>
    </form>
</section>
<?php pageEnd(); ?>
