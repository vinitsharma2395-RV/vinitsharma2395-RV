<?php
require __DIR__ . '/common.php';

pageStart('14. Create restricted MySQL user');
?>
<section>
    <p>Uses the administrator account to create/refresh the dedicated account from <code>COLLEGE_DB_USER</code> and <code>COLLEGE_DB_PASSWORD</code>. The only grants are SELECT, INSERT, and UPDATE on <code>college_db</code>.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            requireCsrf();
            $user = getenv('COLLEGE_DB_USER');
            $password = getenv('COLLEGE_DB_PASSWORD');
            if ($user === false || !preg_match('/^[A-Za-z0-9_]{1,32}$/', $user)) {
                throw new InvalidArgumentException('Set COLLEGE_DB_USER to a MySQL username with letters, digits, or underscores only.');
            }
            if ($password === false || strlen($password) < 16) {
                throw new InvalidArgumentException('Set COLLEGE_DB_PASSWORD to a password of at least 16 characters.');
            }
            $admin = adminDb();
            $quotedUser = $admin->quote($user);
            $quotedHost = $admin->quote(dbHost());
            $quotedPassword = $admin->quote($password);
            $admin->exec("CREATE USER IF NOT EXISTS {$quotedUser}@{$quotedHost} IDENTIFIED BY {$quotedPassword}");
            $admin->exec("ALTER USER {$quotedUser}@{$quotedHost} IDENTIFIED BY {$quotedPassword}");
            $admin->exec("GRANT SELECT, INSERT, UPDATE ON `" . COLLEGE_DB_NAME . "`.* TO {$quotedUser}@{$quotedHost}");
            echo '<p class="notice">Restricted user provisioned. No DELETE, DDL, or GRANT privilege was added.</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="post">
        <?php csrfField(); ?>
        <button type="submit">Provision restricted user</button>
    </form>
    <p class="muted">Exercise 9 intentionally demonstrates a denied DELETE until an administrator separately authorizes that privilege.</p>
</section>
<?php pageEnd(); ?>
