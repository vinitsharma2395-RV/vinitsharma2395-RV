<?php
require __DIR__ . '/common.php';

pageStart('6. Insert a student');
?>
<section>
    <p>The values are inserted through a PDO prepared statement. Enrollment date defaults to today when left blank.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            requireCsrf();
            $name = trim((string) ($_POST['name'] ?? ''));
            $nameLength = preg_match_all('/./us', $name);
            if ($name === '' || $nameLength === false || $nameLength > 120) {
                throw new InvalidArgumentException('Name is required and must be at most 120 characters.');
            }
            $email = requireEmail($_POST['email'] ?? '');
            $date = trim((string) ($_POST['enrollment_date'] ?? ''));
            if ($date === '') {
                $date = date('Y-m-d');
            }
            $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
            if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
                throw new InvalidArgumentException('Enter a valid enrollment date.');
            }
            $pdo = appDb();
            $statement = $pdo->prepare(
                'INSERT INTO students (name, email, enrollment_date) VALUES (:name, :email, :date)'
            );
            $statement->execute(['name' => $name, 'email' => $email, 'date' => $date]);
            echo '<p class="notice">Student record inserted. New ID: ' . e($pdo->lastInsertId()) . '.</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="post">
        <?php csrfField(); ?>
        <label>Name <input name="name" maxlength="120" required></label>
        <label>Email <input name="email" type="email" maxlength="254" required></label>
        <label>Enrollment date <input name="enrollment_date" type="date"></label>
        <button type="submit">Insert student</button>
    </form>
</section>
<?php pageEnd(); ?>
