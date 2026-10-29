<?php
require __DIR__ . '/common.php';

try {
    $rows = appDb()->query(
        'SELECT id, name, email, enrollment_date FROM students ORDER BY id'
    )->fetchAll(PDO::FETCH_NUM);
    $output = fopen('php://output', 'w');
    if ($output === false) {
        throw new RuntimeException('Could not open the CSV response stream.');
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="students.csv"');
    header('X-Content-Type-Options: nosniff');
    fputcsv($output, ['id', 'name', 'email', 'enrollment_date']);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
} catch (RuntimeException | InvalidArgumentException $exception) {
    http_response_code(500);
    exit('CSV export failed: ' . e($exception->getMessage()));
}
