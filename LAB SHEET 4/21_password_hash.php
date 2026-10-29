<?php
require __DIR__ . '/common.php';

pageStart('21. Password hashing and verification');
?>
<section>
    <p>New credentials are stored with <code>password_hash(PASSWORD_DEFAULT)</code>. Sign-in reads the stored hash and uses <code>password_verify()</code>.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            requireCsrf();
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            if (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username)) {
                throw new InvalidArgumentException('Username must be 3–64 letters, digits, dots, underscores, or hyphens.');
            }
            if (strlen($password) < 12) {
                throw new InvalidArgumentException('Password must contain at least 12 characters.');
            }
            $pdo = appDb();
            if (($_POST['mode'] ?? '') === 'register') {
                $statement = $pdo->prepare(
                    'INSERT INTO users (username, password_hash) VALUES (:username, :hash)'
                );
                $statement->execute([
                    'username' => $username,
                    'hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                echo '<p class="notice">Account created. Only the one-way password hash was saved.</p>';
            } else {
                $statement = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :username');
                $statement->execute(['username' => $username]);
                $user = $statement->fetch();
                if (!$user || !password_verify($password, $user['password_hash'])) {
                    throw new InvalidArgumentException('Invalid username or password.');
                }
                echo '<p class="notice">password_verify() succeeded for ' . e($user['username']) . '.</p>';
            }
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="post">
        <?php csrfField(); ?>
        <label>Username <input name="username" maxlength="64" required></label>
        <label>Password (12+ characters) <input name="password" type="password" minlength="12" required></label>
        <button type="submit" name="mode" value="register">Hash and create account</button>
        <button type="submit" name="mode" value="login">Verify stored password</button>
    </form>
</section>
<?php pageEnd(); ?>
