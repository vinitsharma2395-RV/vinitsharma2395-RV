<?php
require __DIR__ . '/common.php';

pageStart('18. Apache performance and logs');
?>
<section>
    <h2>Generate a request</h2>
    <p><a class="button" href="01_index.php">Request the lab index page</a></p>
    <p>Reload this page or visit the link several times, then inspect the corresponding Apache access log for request lines and the error log for server/PHP startup problems.</p>
</section>
<section>
    <h2>Common Windows paths</h2>
    <ul>
        <li>XAMPP: <code>C:\xampp\apache\logs\access.log</code> and <code>C:\xampp\apache\logs\error.log</code></li>
        <li>Apache Lounge: commonly <code>C:\Apache24\logs\access.log</code> and <code>C:\Apache24\logs\error.log</code></li>
        <li>Custom virtual host: use the <code>ErrorLog</code> and <code>CustomLog</code> locations configured for that host.</li>
    </ul>
    <p>From PowerShell, send sample requests with <code>curl.exe -I http://localhost/</code>, then inspect recent log lines using <code>Get-Content C:\xampp\apache\logs\access.log -Tail 20</code>. Paths vary by installation.</p>
    <p class="muted">A local PHP page can generate requests but cannot reliably discover Apache's configured log paths; check the active Apache configuration.</p>
</section>
<?php pageEnd(); ?>
