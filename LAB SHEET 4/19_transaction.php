<?php
require __DIR__ . '/common.php';

pageStart('19. PDO transaction');
?>
<section>
    <p>Creates a student and an audit row in one InnoDB transaction. A failure during either insert rolls back the whole operation.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo = null;
        try {
            requireCsrf();
            $name = trim((string) ($_POST['name'] ?? ''));
            $nameLength = preg_match_all('/./us', $name);
            if ($name === '' || $nameLength === false || $nameLength > 120) {
                throw new InvalidArgumentException('Name is required and must be at most 120 characters.');
            }
            $email = requireEmail($_POST['email'] ?? '');
            $pdo = appDb();
            $pdo->beginTransaction();
            $student = $pdo->prepare(
                'INSERT INTO students (name, email, enrollment_date) VALUES (:name, :email, :date)'
            );
            $student->execute(['name' => $name, 'email' => $email, 'date' => date('Y-m-d')]);
            $studentId = (int) $pdo->lastInsertId();

            $audit = $pdo->prepare(
                'INSERT INTO student_audit (student_id, action, details) VALUES (:id, :action, :details)'
            );
            $audit->execute([
                'id' => $studentId,
                'action' => 'created',
                'details' => 'Created by exercise 19 transaction.',
            ]);
            $pdo->commit();
            echo '<p class="notice">Student ' . e($studentId) . ' and its audit row were committed.</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            showError($exception);
        }
    }
    ?>
    <form method="post">
        <?php csrfField(); ?>
        <label>Name <input name="name" maxlength="120" required></label>
        <label>Email <input name="email" type="email" maxlength="254" required></label>
        <button type="submit">Run transaction</button>
    </form>
</section>
<?php pageEnd(); ?>
