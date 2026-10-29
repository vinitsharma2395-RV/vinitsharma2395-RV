<?php
require __DIR__ . '/common.php';

pageStart('1. Apache default page');
?>
<section>
    <h2>PHP is running</h2>
    <p>This page is the Apache directory index for the lab folder. Set <code>DirectoryIndex 01_index.php index.php index.html</code> and use this folder as the document root, then open <code>http://localhost/</code>.</p>
    <p>Server software: <strong><?= e($_SERVER['SERVER_SOFTWARE'] ?? 'PHP built-in server or not supplied') ?></strong></p>
    <p>PHP version: <strong><?= e(PHP_VERSION) ?></strong></p>
    <p>For persistent error reporting, use <a href="02_phpinfo.php">exercise 2</a> to locate the active php.ini, set <code>display_errors = On</code>, then restart Apache. The shared include also enables it for this development page.</p>
</section>
<section>
    <h2>Exercises</h2>
    <div class="grid">
        <?php
        $exercises = [
            '02_phpinfo.php' => '2. PHP configuration / phpinfo',
            '03_create_database.php' => '3. Create college_db',
            '04_pdo_connection.php' => '4. PDO connection status',
            '05_create_tables.php' => '5. Create MySQL tables',
            '06_insert_student.php' => '6. Insert student',
            '07_list_students.php' => '7. Display students',
            '08_update_email.php' => '8. Update student email',
            '09_delete_student.php' => '9. Delete student by GET',
            '10_virtual_host.php' => '10. Apache virtual host',
            '11_server_info.php' => '11. $_SERVER information',
            '12_authentication.php' => '12. Database authentication',
            '13_upload_image.php' => '13. Image upload',
            '14_create_app_user.php' => '14. Restricted MySQL user',
            '15_session.php' => '15. Session management',
            '16_theme_cookie.php' => '16. Theme cookie',
            '17_export_csv.php' => '17. CSV export',
            '18_apache_logs.php' => '18. Apache logs',
            '19_transaction.php' => '19. PDO transaction',
            '20_extensions.php' => '20. PHP extensions',
            '21_password_hash.php' => '21. Password hash / verify',
        ];
        foreach ($exercises as $file => $label):
        ?>
            <a class="card" href="<?= e($file) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</section>
<?php pageEnd(); ?>
