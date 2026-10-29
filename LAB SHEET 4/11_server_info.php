<?php
require __DIR__ . '/common.php';

pageStart('11. Apache server variables');
$variables = [
    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? '(not provided)',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? '(not provided)',
    'SERVER_PROTOCOL' => $_SERVER['SERVER_PROTOCOL'] ?? '(not provided)',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? '(not provided)',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '(not provided)',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? '(not provided)',
    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? '(not provided)',
    'PHP_SAPI' => PHP_SAPI,
];
?>
<section>
    <div class="table-wrap"><table><thead><tr><th>$_SERVER key</th><th>Value</th></tr></thead><tbody>
        <?php foreach ($variables as $key => $value): ?>
            <tr><td><code><?= e($key) ?></code></td><td><?= e($value) ?></td></tr>
        <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php pageEnd(); ?>
