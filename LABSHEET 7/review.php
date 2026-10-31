<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/LABSHEET%207/index.php');
verify_csrf();
$bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
$rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
$comment = trim((string) ($_POST['comment'] ?? ''));
if (!$bookId || $bookId < 1 || !$rating || $rating < 1 || $rating > 5 || $comment === '' || text_length($comment) > 2000) {
    flash('Add a rating from 1 to 5 and a comment under 2,000 characters.', 'error');
    redirect($bookId && $bookId > 0 ? '/LABSHEET%207/book.php?id=' . $bookId . '#reviews' : '/LABSHEET%207/index.php');
}
$bookCheck = db()->prepare('SELECT id FROM books WHERE id = :id');
$bookCheck->execute(['id' => $bookId]);
if (!$bookCheck->fetch()) { http_response_code(404); exit('Book not found.'); }
$save = db()->prepare('INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (:user, :book, :rating, :comment) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP');
$save->execute(['user' => $user['id'], 'book' => $bookId, 'rating' => $rating, 'comment' => $comment]);
flash('Your review has been saved. Thank you for sharing.');
redirect('/LABSHEET%207/book.php?id=' . $bookId . '#reviews');
