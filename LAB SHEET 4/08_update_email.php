<?php
require __DIR__ . '/common.php';

pageStart('8. Update student email');
?>
<section>
    <p>Update an existing row by student ID using a prepared statement.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            requireCsrf();
            $id = requireStudentId($_POST['id'] ?? null);
            $email = requireEmail($_POST['email'] ?? '');
            $statement = appDb()->prepare('UPDATE students SET email = :email WHERE id = :id');
            $statement->execute(['email' => $email, 'id' => $id]);
            echo '<p class="notice">' . ($statement->rowCount() ? 'Student email updated.' : 'No row changed; check the ID or current email.') . '</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="post">
        <?php csrfField(); ?>
        <label>Student ID <input name="id" type="number" min="1" required></label>
        <label>New email <input name="email" type="email" maxlength="254" required></label>
        <button type="submit">Update email</button>
    </form>
</section>
<?php pageEnd(); ?>
