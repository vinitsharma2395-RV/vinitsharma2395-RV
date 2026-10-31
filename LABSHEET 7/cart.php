<?php
require __DIR__ . '/includes/bootstrap.php';
$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) $cart = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    if ($action === 'add' && $bookId && $bookId > 0) {
        $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
        $quantity = $quantity === false ? 0 : $quantity;
        $stmt = db()->prepare('SELECT id, title, stock FROM books WHERE id = :id');
        $stmt->execute(['id' => $bookId]);
        $book = $stmt->fetch();
        $already = (int) ($cart[$bookId] ?? 0);
        if (!$book) flash('That book is no longer available.', 'error');
        elseif ($quantity < 1 || $already + $quantity > (int) $book['stock']) flash('Only ' . (int) $book['stock'] . ' copies of “' . $book['title'] . '” are currently available.', 'error');
        else { $cart[$bookId] = $already + $quantity; $_SESSION['cart'] = $cart; flash('“' . $book['title'] . '” was added to your bag.'); }
    } elseif ($action === 'update' && $bookId && isset($cart[$bookId])) {
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        $stmt = db()->prepare('SELECT stock FROM books WHERE id = :id');
        $stmt->execute(['id' => $bookId]);
        $stock = $stmt->fetchColumn();
        if ($quantity === false || $quantity < 1 || $stock === false || $quantity > (int) $stock) flash('Please choose a quantity within the available stock.', 'error');
        else { $cart[$bookId] = $quantity; $_SESSION['cart'] = $cart; flash('Your bag has been updated.'); }
    } elseif ($action === 'remove' && $bookId) {
        unset($cart[$bookId]); $_SESSION['cart'] = $cart; flash('The book was removed from your bag.');
    }
    redirect('/LABSHEET%207/cart.php');
}

$items = [];
if ($cart) {
    $ids = array_values(array_filter(array_map('intval', array_keys($cart)), fn($id) => $id > 0));
    if ($ids) {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT id, title, author, category, price, stock, image FROM books WHERE id IN ($marks) ORDER BY title");
        $stmt->execute($ids);
        $items = $stmt->fetchAll();
        $availableIds = array_map('intval', array_column($items, 'id'));
        foreach (array_keys($cart) as $cartId) {
            if (!in_array((int) $cartId, $availableIds, true)) unset($cart[$cartId]);
        }
        $_SESSION['cart'] = $cart;
    }
}
$subtotal = 0.0;
render_header('Your bag');
?>
<section class="page-heading"><div class="eyebrow"><span class="eyebrow-line"></span> SAVED FOR YOUR NEXT CHAPTER</div><h1>Your book bag <span>(<?= array_sum(array_map('intval', $cart)) ?>)</span></h1></section>
<?php if ($items): ?><div class="cart-layout"><div class="cart-items">
    <?php foreach ($items as $item): $quantity = (int) ($cart[$item['id']] ?? 0); $subtotal += (float) $item['price'] * $quantity; $tone = ['moss', 'clay', 'ink', 'sun', 'plum', 'sea', 'rose'][((int) $item['id'] - 1) % 7]; ?>
    <article class="cart-row"><div class="cart-thumb book-art <?= e($tone) ?>"><span class="cover-kicker"><?= e($item['category']) ?></span><span class="cover-title"><?= e($item['title']) ?></span><span class="cover-author"><?= e($item['author']) ?></span></div><div class="cart-book-info"><a href="/LABSHEET%207/book.php?id=<?= (int) $item['id'] ?>"><?= e($item['title']) ?></a><span>by <?= e($item['author']) ?></span><span class="cart-stock"><?= (int) $item['stock'] ?> available</span><form method="post" action="/LABSHEET%207/cart.php" class="remove-form"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="book_id" value="<?= (int) $item['id'] ?>"><button class="text-link" type="submit">Remove</button></form></div><form method="post" action="/LABSHEET%207/cart.php" class="quantity-update"><?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="book_id" value="<?= (int) $item['id'] ?>"><label><span class="sr-only">Quantity for <?= e($item['title']) ?></span><input type="number" name="quantity" value="<?= min($quantity, max(1, (int) $item['stock'])) ?>" min="1" max="<?= (int) $item['stock'] ?>"></label><button type="submit" class="text-link">Update</button></form><strong class="cart-line-total"><?= money((float) $item['price'] * $quantity) ?></strong></article>
    <?php endforeach; ?>
    <?php foreach ($cart as $id => $qty): if (!in_array((int) $id, array_column($items, 'id'), true)): ?><form method="post" action="/LABSHEET%207/cart.php" class="stale-cart"><?= csrf_field() ?><span>This book is no longer listed.</span><input type="hidden" name="action" value="remove"><input type="hidden" name="book_id" value="<?= (int) $id ?>"><button class="text-link" type="submit">Remove</button></form><?php endif; endforeach; ?>
    <a class="continue-link" href="/LABSHEET%207/index.php#browse">← Continue browsing</a></div>
    <aside class="summary-card"><div class="summary-eyebrow">A LITTLE SUMMARY</div><div class="summary-line"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div><div class="summary-line"><span>Shipping</span><strong><?= $subtotal >= 45 ? 'On us' : money(4.95) ?></strong></div><div class="summary-total"><span>Estimated total</span><strong><?= money($subtotal + ($subtotal >= 45 ? 0 : 4.95)) ?></strong></div><p>Free shipping on orders over $45.</p><a class="button button-dark button-wide" href="/LABSHEET%207/checkout.php">Continue to checkout <span>→</span></a><div class="secure-note">♡ Thoughtfully packed for you</div></aside></div>
<?php else: ?><div class="empty-state cart-empty"><span>♧</span><h2>Your bag is taking a little break.</h2><p>Find a story that feels like yours and bring it along.</p><a class="button button-dark" href="/LABSHEET%207/index.php#browse">Browse the bookshelf <span>→</span></a></div><?php endif; ?>
<?php render_footer(); ?>
