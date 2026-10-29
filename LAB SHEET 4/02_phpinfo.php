<?php
require __DIR__ . '/common.php';

$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteAddress, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('phpinfo() is available only from localhost.');
}
phpinfo();
