<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    $action = (string) ($_POST['action'] ?? 'toggle');
    if ($bookId && $bookId > 0) {
        if ($action === 'remove') {
            $delete = db()->prepare('DELETE FROM wishlists WHERE user_id = :user AND book_id = :book');
            $delete->execute(['user' => $user['id'], 'book' => $bookId]);
            flash('Book removed from your wish list.');
        } else {
            $exists = db()->prepare('SELECT 1 FROM wishlists WHERE user_id = :user AND book_id = :book');
            $exists->execute(['user' => $user['id'], 'book' => $bookId]);
            if ($exists->fetchColumn()) flash('That book is already in your wish list.', 'notice');
            else {
                $insert = db()->prepare('INSERT INTO wishlists (user_id, book_id) SELECT :user, id FROM books WHERE id = :book');
                $insert->execute(['user' => $user['id'], 'book' => $bookId]);
                flash('Saved to your wish list.');
            }
        }
    }
    redirect($action === 'remove' ? '/LABSHEET%207/wishlist.php' : '/LABSHEET%207/book.php?id=' . (int) $bookId);
}
$stmt = db()->prepare('SELECT b.id, b.title, b.author, b.category, b.price, b.stock, b.image FROM wishlists w JOIN books b ON b.id = w.book_id WHERE w.user_id = :user ORDER BY w.created_at DESC');
$stmt->execute(['user' => $user['id']]);
$books = $stmt->fetchAll();
render_header('My wish list');
?>
<section class="page-heading"><div class="eyebrow"><span class="eyebrow-line"></span> FOR ANOTHER DAY</div><h1>Your wish list</h1><p>All the books you’d like to come back to.</p></section>
<?php if ($books): ?><div class="wishlist-grid"><?php foreach ($books as $book): ?><div class="wishlist-card"><?php include __DIR__ . '/includes/book-card.php'; ?><form method="post" action="/LABSHEET%207/wishlist.php"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>"><button class="text-link" type="submit">Remove from wish list</button></form></div><?php endforeach; ?></div><?php else: ?><div class="empty-state"><span>♡</span><h2>Your wish list is a blank page.</h2><p>Save books you love and find them here later.</p><a class="button button-dark" href="/LABSHEET%207/index.php#browse">Explore books <span>→</span></a></div><?php endif; ?>
<?php render_footer(); ?>
