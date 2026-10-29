<?php
require __DIR__ . '/common.php';

pageStart('7. List students');
?>
<section>
    <p>Student rows are fetched from MySQL and escaped before being placed in the HTML table.</p>
    <?php
    try {
        $students = appDb()->query(
            'SELECT id, name, email, enrollment_date FROM students ORDER BY id'
        )->fetchAll();
        if (!$students) {
            echo '<p class="muted">There are no student records yet.</p>';
        } else {
            echo '<div class="table-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Enrollment date</th></tr></thead><tbody>';
            foreach ($students as $student) {
                echo '<tr><td>' . e($student['id']) . '</td><td>' . e($student['name'])
                    . '</td><td>' . e($student['email']) . '</td><td>' . e($student['enrollment_date'])
                    . '</td></tr>';
            }
            echo '</tbody></table></div>';
        }
    } catch (RuntimeException | InvalidArgumentException $exception) {
        showError($exception);
    }
    ?>
    <p><a class="button" href="06_insert_student.php">Add a student</a></p>
</section>
<?php pageEnd(); ?>
