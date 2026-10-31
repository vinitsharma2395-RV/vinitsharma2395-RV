<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('/LABSHEET%207/index.php');
$errors = [];
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || text_length($name) > 120) $errors[] = 'Enter your name (up to 120 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Your password must be at least 8 characters.';
    if (!$errors) {
        $check = db()->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute(['email' => $email]);
        if ($check->fetch()) $errors[] = 'An account with this email already exists.';
        else {
            $insert = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)');
            $insert->execute(['name' => $name, 'email' => $email, 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) db()->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'customer'];
            flash('Welcome to Paper & Pine. Your next great read is waiting.');
            redirect('/LABSHEET%207/index.php');
        }
    }
}
render_header('Create an account');
?>
<section class="auth-layout"><div class="auth-art"><span class="eyebrow">A WORLD OF STORIES</span><h2>Your next favorite<br>is out there.</h2><div class="auth-stack"><i></i><i></i><i></i></div><p>Keep your reads, orders, and wish list all in one place.</p></div><div class="auth-panel"><div class="eyebrow"><span class="eyebrow-line"></span> JOIN OUR LITTLE BOOKSHOP</div><h1>Create your account</h1><p class="form-intro">A few details and you’re in.</p>
    <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
    <form method="post" data-validate><?= csrf_field() ?><label>Full name<input name="name" required maxlength="120" autocomplete="name" value="<?= e($name) ?>"></label><label>Email address<input type="email" name="email" required maxlength="190" autocomplete="email" value="<?= e($email) ?>"></label><label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"><small>Use at least 8 characters.</small></label><button class="button button-dark button-wide" type="submit">Create account <span>→</span></button></form>
    <p class="auth-switch">Already have an account? <a href="/LABSHEET%207/login.php">Sign in</a></p></div></section>
<?php render_footer(); ?>
