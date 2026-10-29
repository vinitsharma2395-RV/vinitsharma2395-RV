<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    try {
        requireCsrf();
        $_SESSION = [];
        session_destroy();
        session_start();
    } catch (InvalidArgumentException $exception) {
        $logoutError = $exception;
    }
}

pageStart('12. Database authentication');
?>
<section>
    <p>Sign-in looks up a stored password hash and calls <code>password_verify()</code>. Create accounts in exercise 21.</p>
    <?php
    if (isset($logoutError)) {
        showError($logoutError);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
        try {
            requireCsrf();
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $statement = appDb()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username');
            $statement->execute(['username' => $username]);
            $user = $statement->fetch();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                throw new InvalidArgumentException('Invalid username or password.');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            echo '<p class="notice">Authentication succeeded. Welcome, ' . e($user['username']) . '.</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    if (isset($_SESSION['user_id'])):
    ?>
        <p>Signed in as <strong><?= e($_SESSION['username']) ?></strong>.</p>
        <form method="post">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="logout">
            <button type="submit">Sign out</button>
        </form>
    <?php else: ?>
        <form method="post">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="login">
            <label>Username <input name="username" autocomplete="username" required></label>
            <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
            <button type="submit">Sign in</button>
        </form>
    <?php endif; ?>
</section>
<?php pageEnd(); ?>
