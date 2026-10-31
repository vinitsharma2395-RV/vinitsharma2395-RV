<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('/LABSHEET%207/index.php');
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $statement = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
        flash('Welcome back, ' . $user['name'] . '.');
        redirect('/LABSHEET%207/index.php');
    }
    $error = 'That email and password combination was not recognized.';
}
render_header('Sign in');
?>
<section class="auth-layout"><div class="auth-art"><span class="eyebrow">PICK UP WHERE YOU LEFT OFF</span><h2>There’s always<br>another chapter.</h2><div class="auth-stack"><i></i><i></i><i></i></div><p>Come back to your saved books, recent orders, and favorite shelves.</p></div><div class="auth-panel"><div class="eyebrow"><span class="eyebrow-line"></span> WELCOME BACK</div><h1>Sign in</h1><p class="form-intro">Your bookshelf missed you.</p>
    <?php if ($error): ?><div class="form-errors" role="alert"><p><?= e($error) ?></p></div><?php endif; ?>
    <form method="post" data-validate><?= csrf_field() ?><label>Email address<input type="email" name="email" required autocomplete="email" value="<?= e($email) ?>"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="button button-dark button-wide" type="submit">Sign in <span>→</span></button></form>
    <p class="auth-switch">New to Paper & Pine? <a href="/LABSHEET%207/register.php">Create an account</a></p></div></section>
<?php render_footer(); ?>
