<?php
require __DIR__ . '/common.php';

$directory = str_replace('\\', '/', __DIR__);
pageStart('10. Apache virtual host');
?>
<section>
    <p>Enable the virtual-host configuration include in Apache's <code>httpd.conf</code>, then add this block to <code>conf/extra/httpd-vhosts.conf</code>:</p>
    <pre><?= e('<VirtualHost *:80>
    ServerName collegeweb.local
    DocumentRoot "' . $directory . '"
    DirectoryIndex 01_index.php index.php index.html
    <Directory "' . $directory . '">
        AllowOverride All
        Require local
    </Directory>
    ErrorLog "logs/collegeweb-error.log"
    CustomLog "logs/collegeweb-access.log" common
</VirtualHost>') ?></pre>
    <p>Add <code>127.0.0.1 collegeweb.local</code> to <code>C:\Windows\System32\drivers\etc\hosts</code> with administrator permissions, restart Apache, and open <code>http://collegeweb.local/</code>.</p>
    <p>Set the main Apache document root to this folder if you also want this page at <code>http://localhost/</code>. The correct config location depends on your Apache distribution.</p>
</section>
<?php pageEnd(); ?>
