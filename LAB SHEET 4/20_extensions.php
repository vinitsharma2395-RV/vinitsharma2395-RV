<?php
require __DIR__ . '/common.php';

pageStart('20. PHP extensions');
$extensions = ['mysqli', 'pdo_mysql', 'mbstring', 'curl'];
?>
<section>
    <p>Extension status from <code>extension_loaded()</code>:</p>
    <ul>
        <?php foreach ($extensions as $extension): ?>
            <li><code><?= e($extension) ?></code>:
                <?php if (extension_loaded($extension)): ?>
                    <span class="ok">active</span>
                <?php else: ?>
                    <span class="danger">not active</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <p>Enable missing <code>extension=</code> entries in the php.ini file reported by exercise 2, then restart Apache and reload this page.</p>
</section>
<?php pageEnd(); ?>
