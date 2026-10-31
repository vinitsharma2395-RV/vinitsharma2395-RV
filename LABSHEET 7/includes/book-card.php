<?php
$coverTones = ['moss', 'clay', 'ink', 'sun', 'plum', 'sea', 'rose'];
$tone = $coverTones[((int) $book['id'] - 1) % count($coverTones)];
$image = book_image($book['image'] ?? null);
?>
<article class="book-card">
    <a class="book-art <?= e($tone) ?>" href="/LABSHEET%207/book.php?id=<?= (int) $book['id'] ?>" aria-label="View <?= e($book['title']) ?>">
        <?php if ($image): ?><img src="<?= e($image) ?>" alt="Cover of <?= e($book['title']) ?>" loading="lazy">
        <?php else: ?><span class="cover-kicker"><?= e($book['category'] ?? 'PAPER & PINE') ?></span><span class="cover-title"><?= e($book['title']) ?></span><span class="cover-author"><?= e($book['author']) ?></span><span class="cover-seal">P<span>&</span>P</span><?php endif; ?>
    </a>
    <div class="book-meta">
        <div><a class="book-title" href="/LABSHEET%207/book.php?id=<?= (int) $book['id'] ?>"><?= e($book['title']) ?></a><div class="book-author"><?= e($book['author']) ?></div></div>
        <span class="book-price"><?= money($book['price']) ?></span>
    </div>
    <div class="book-card-bottom"><span class="book-category"><?= e($book['category']) ?></span>
        <?php if ((int) $book['stock'] > 0): ?>
            <form method="post" action="/LABSHEET%207/cart.php">
                <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>"><input type="hidden" name="quantity" value="1">
                <button class="icon-button" type="submit" aria-label="Add <?= e($book['title']) ?> to bag">＋</button>
            </form>
        <?php else: ?><span class="stock-out">Sold out</span><?php endif; ?>
    </div>
</article>
