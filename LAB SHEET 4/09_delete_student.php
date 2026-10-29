<?php
require __DIR__ . '/common.php';

pageStart('9. Delete student using GET');
?>
<section>
    <p>This form submits the student ID and CSRF token using GET. The SQL statement is parameterized.</p>
    <p class="notice">The dedicated app account is intentionally limited to SELECT, INSERT, and UPDATE. MySQL will deny this DELETE until an administrator explicitly grants DELETE on the table.</p>
    <?php
    if (isset($_GET['id'])) {
        try {
            requireCsrf(true);
            $id = requireStudentId($_GET['id']);
            $statement = appDb()->prepare('DELETE FROM students WHERE id = :id');
            $statement->execute(['id' => $id]);
            echo '<p class="notice">' . ($statement->rowCount() ? 'Student deleted.' : 'No student exists with that ID.') . '</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="get" onsubmit="return confirm('Delete this student record?')">
        <label>Student ID <input name="id" type="number" min="1" required></label>
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <button type="submit">Delete by GET</button>
    </form>
</section>
<?php pageEnd(); ?>
