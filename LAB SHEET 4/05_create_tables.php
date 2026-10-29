<?php
require __DIR__ . '/common.php';

pageStart('5. Create MySQL tables');
?>
<section>
    <p>Run the table-creation SQL as a database administrator. The extra <code>users</code> and <code>student_audit</code> tables support exercises 12, 19, and 21.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            requireCsrf();
            $admin = adminDb();
            $admin->exec('USE `' . COLLEGE_DB_NAME . '`');
            $admin->exec(
                'CREATE TABLE IF NOT EXISTS students (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(120) NOT NULL,
                    email VARCHAR(254) NOT NULL,
                    enrollment_date DATE NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            $admin->exec(
                'CREATE TABLE IF NOT EXISTS users (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(64) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            $admin->exec(
                'CREATE TABLE IF NOT EXISTS student_audit (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    student_id INT UNSIGNED NULL,
                    action VARCHAR(32) NOT NULL,
                    details VARCHAR(512) NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_student_audit_student FOREIGN KEY (student_id)
                        REFERENCES students(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            echo '<p class="notice">The students, users, and student_audit tables are ready.</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="post">
        <?php csrfField(); ?>
        <button type="submit">Create tables</button>
    </form>
</section>
<?php pageEnd(); ?>
