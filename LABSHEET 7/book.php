<?php
require __DIR__ . '/includes/bootstrap.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Book not found.'); }
$stmt = db()->prepare('SELECT id, title, author, category, description, price, stock, image FROM books WHERE id = :id');
$stmt->execute(['id' => $id]);
$book = $stmt->fetch();
if (!$book) { http_response_code(404); exit('Book not found.'); }
$reviewStmt = db()->prepare('SELECT r.rating, r.comment, r.created_at, u.name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.book_id = :id ORDER BY r.created_at DESC');
$reviewStmt->execute(['id' => $id]);
$reviews = $reviewStmt->fetchAll();
$average = $reviews ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
$user = current_user();
$myReview = null;
if ($user) { $mine = db()->prepare('SELECT rating, comment FROM reviews WHERE user_id = :user AND book_id = :book'); $mine->execute(['user' => $user['id'], 'book' => $id]); $myReview = $mine->fetch() ?: null; }
$tone = ['moss', 'clay', 'ink', 'sun', 'plum', 'sea', 'rose'][((int) $book['id'] - 1) % 7];
$image = book_image($book['image']);
render_header($book['title']);
?>
<div class="breadcrumb"><a href="/LABSHEET%207/index.php">Home</a><span>›</span><span><?= e($book['category']) ?></span><span>›</span><span><?= e($book['title']) ?></span></div>
<section class="detail-layout"><div class="detail-cover"><div class="book-art detail-art <?= e($tone) ?>"><?php if ($image): ?><img src="<?= e($image) ?>" alt="Cover of <?= e($book['title']) ?>"><?php else: ?><span class="cover-kicker"><?= e($book['category']) ?></span><span class="cover-title"><?= e($book['title']) ?></span><span class="cover-author"><?= e($book['author']) ?></span><span class="cover-seal">P<span>&</span>P</span><?php endif; ?></div><span class="detail-shadow"></span></div>
    <div class="detail-info"><div class="eyebrow"><span class="eyebrow-line"></span> <?= e(strtoupper($book['category'])) ?></div><h1><?= e($book['title']) ?></h1><p class="detail-author">by <?= e($book['author']) ?></p><div class="rating-line"><span class="stars"><?= $reviews ? str_repeat('★', (int) round($average)) . str_repeat('☆', 5 - (int) round($average)) : '☆☆☆☆☆' ?></span><span><?= $reviews ? number_format($average, 1) . ' (' . count($reviews) . ' reader ' . (count($reviews) === 1 ? 'review' : 'reviews') . ')' : 'Be the first to review' ?></span></div><div class="detail-price"><?= money($book['price']) ?></div><p class="detail-description"><?= nl2br(e($book['description'] ?: 'A wonderful addition to any reader’s shelf.')) ?></p>
    <div class="detail-stock <?= (int) $book['stock'] > 0 ? '' : 'out' ?>"><span class="stock-dot"></span><?= (int) $book['stock'] > 0 ? 'In stock · ' . (int) $book['stock'] . ' available' : 'Currently sold out' ?></div>
    <?php if ((int) $book['stock'] > 0): ?><form class="detail-buy" method="post" action="/LABSHEET%207/cart.php"><?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>"><label class="quantity-field"><span class="sr-only">Quantity</span><input type="number" name="quantity" min="1" max="<?= (int) $book['stock'] ?>" value="1"></label><button class="button button-dark" type="submit">Add to bag <span>＋</span></button></form><?php endif; ?>
    <?php if ($user): ?><form class="wishlist-inline" method="post" action="/LABSHEET%207/wishlist.php"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>"><button class="text-link" type="submit">♡ Save to wish list</button></form><?php else: ?><a class="text-link wishlist-inline" href="/LABSHEET%207/login.php">♡ Sign in to save this book</a><?php endif; ?>
    </div></section>
<section class="reviews-section" id="reviews"><div class="section-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span> READER NOTES</div><h2>Stories worth sharing.</h2></div><span class="count-note"><?= count($reviews) ?> REVIEWS</span></div>
    <?php if ($user): ?><form class="review-form" method="post" action="/LABSHEET%207/review.php" data-validate><?= csrf_field() ?><input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>"><h3><?= $myReview ? 'Update your review' : 'Leave a review' ?></h3><div class="review-fields"><label>Your rating<select name="rating" required><?php for ($r=5; $r>=1; $r--): ?><option value="<?= $r ?>" <?= (int) ($myReview['rating'] ?? 5) === $r ? 'selected' : '' ?>><?= $r ?> star<?= $r > 1 ? 's' : '' ?></option><?php endfor; ?></select></label><label class="review-comment">Your thoughts<textarea name="comment" rows="3" maxlength="2000" required placeholder="What stayed with you after the last page?"><?= e($myReview['comment'] ?? '') ?></textarea></label></div><button class="button button-outline" type="submit"><?= $myReview ? 'Update review' : 'Post review' ?></button></form><?php else: ?><p class="review-signin"><a href="/LABSHEET%207/login.php">Sign in</a> to leave a note for fellow readers.</p><?php endif; ?>
    <?php if ($reviews): ?><div class="review-list"><?php foreach ($reviews as $review): ?><article class="review-card"><div class="review-top"><div><strong><?= e($review['name']) ?></strong><div class="stars"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></div></div><time><?= e(date('M j, Y', strtotime($review['created_at']))) ?></time></div><p><?= nl2br(e($review['comment'])) ?></p></article><?php endforeach; ?></div><?php else: ?><div class="empty-state compact"><span>✳</span><p>No notes yet. Yours could be the first.</p></div><?php endif; ?>
</section>
<?php render_footer(); ?>
