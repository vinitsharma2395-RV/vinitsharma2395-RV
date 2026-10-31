<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart) || !$cart) { flash('Your bag is empty.', 'notice'); redirect('/LABSHEET%207/cart.php'); }
$ids = array_values(array_filter(array_map('intval', array_keys($cart)), fn($id) => $id > 0));
if (!$ids) redirect('/LABSHEET%207/cart.php');
$marks = implode(',', array_fill(0, count($ids), '?'));
$stmt = db()->prepare("SELECT id, title, author, price, stock FROM books WHERE id IN ($marks) ORDER BY id");
$stmt->execute($ids);
$items = $stmt->fetchAll();
if (count($items) !== count(array_unique($ids))) {
    flash('A book in your bag is no longer available. Please review your bag.', 'notice');
    redirect('/LABSHEET%207/cart.php');
}
$subtotal = 0.0;
foreach ($items as $item) $subtotal += (float) $item['price'] * (int) ($cart[$item['id']] ?? 0);
$shippingCost = $subtotal >= 45 ? 0.0 : 4.95;
$errors = [];
$shipping = ['full_name' => $user['name'], 'address' => '', 'city' => '', 'postal_code' => '', 'phone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($shipping as $field => $_) $shipping[$field] = trim((string) ($_POST[$field] ?? ''));
    if ($shipping['full_name'] === '' || text_length($shipping['full_name']) > 120) $errors[] = 'Enter a valid recipient name.';
    if ($shipping['address'] === '' || text_length($shipping['address']) > 255) $errors[] = 'Enter a valid street address.';
    if ($shipping['city'] === '' || text_length($shipping['city']) > 100) $errors[] = 'Enter a valid city.';
    if ($shipping['postal_code'] === '' || text_length($shipping['postal_code']) > 20) $errors[] = 'Enter a valid postal code.';
    if ($shipping['phone'] === '' || text_length($shipping['phone']) > 30) $errors[] = 'Enter a valid phone number.';
    if (!$items) $errors[] = 'The books in your bag are no longer available.';
    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare("SELECT id, title, author, price, stock FROM books WHERE id IN ($marks) ORDER BY id FOR UPDATE");
            $lock->execute($ids);
            $lockedItems = $lock->fetchAll();
            if (count($lockedItems) !== count(array_unique($ids))) throw new RuntimeException('A book in your bag is no longer available.');
            $itemsTotal = 0.0;
            foreach ($lockedItems as $item) {
                $quantity = (int) ($cart[$item['id']] ?? 0);
                if ($quantity < 1 || $quantity > (int) $item['stock']) throw new RuntimeException('Stock changed while you were shopping. Please update your bag.');
                $itemsTotal += (float) $item['price'] * $quantity;
            }
            $total = $itemsTotal + ($itemsTotal >= 45 ? 0 : 4.95);
            $insertOrder = $pdo->prepare('INSERT INTO orders (user_id, full_name, address, city, postal_code, phone, total) VALUES (:user_id, :full_name, :address, :city, :postal_code, :phone, :total)');
            $insertOrder->execute(['user_id' => $user['id'], 'full_name' => $shipping['full_name'], 'address' => $shipping['address'], 'city' => $shipping['city'], 'postal_code' => $shipping['postal_code'], 'phone' => $shipping['phone'], 'total' => $total]);
            $orderId = (int) $pdo->lastInsertId();
            $insertItem = $pdo->prepare('INSERT INTO order_items (order_id, book_id, title_snapshot, author_snapshot, unit_price, quantity) VALUES (:order_id, :book_id, :title, :author, :price, :quantity)');
            $decrement = $pdo->prepare('UPDATE books SET stock = stock - :quantity WHERE id = :id AND stock >= :check_quantity');
            foreach ($lockedItems as $item) {
                $quantity = (int) $cart[$item['id']];
                $insertItem->execute(['order_id' => $orderId, 'book_id' => $item['id'], 'title' => $item['title'], 'author' => $item['author'], 'price' => $item['price'], 'quantity' => $quantity]);
                $decrement->execute(['quantity' => $quantity, 'id' => $item['id'], 'check_quantity' => $quantity]);
                if ($decrement->rowCount() !== 1) throw new RuntimeException('Stock changed while you were shopping. Please update your bag.');
            }
            $pdo->commit();
            unset($_SESSION['cart']);
            redirect('/LABSHEET%207/invoice.php?id=' . $orderId);
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $error instanceof RuntimeException ? $error->getMessage() : 'We could not place your order. Please try again.';
        }
    }
}
render_header('Checkout');
?>
<section class="page-heading"><div class="eyebrow"><span class="eyebrow-line"></span> ALMOST YOURS</div><h1>Delivery details</h1><p>We’ll get these books on their way to you.</p></section>
<div class="checkout-layout"><section class="checkout-form-panel"><h2>Where should we send them?</h2>
    <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
    <form method="post" data-validate><?= csrf_field() ?><label>Full name<input name="full_name" required maxlength="120" autocomplete="name" value="<?= e($shipping['full_name']) ?>"></label><label>Street address<input name="address" required maxlength="255" autocomplete="street-address" value="<?= e($shipping['address']) ?>"></label><div class="form-row"><label>City<input name="city" required maxlength="100" autocomplete="address-level2" value="<?= e($shipping['city']) ?>"></label><label>Postal code<input name="postal_code" required maxlength="20" autocomplete="postal-code" value="<?= e($shipping['postal_code']) ?>"></label></div><label>Phone number<input type="tel" name="phone" required maxlength="30" autocomplete="tel" value="<?= e($shipping['phone']) ?>"></label><button class="button button-dark button-wide" type="submit">Place my order <span>→</span></button><p class="form-footnote">Your order is placed securely and stock is reserved when you confirm.</p></form></section>
    <aside class="summary-card checkout-summary"><div class="summary-eyebrow">YOUR BAG</div><?php foreach ($items as $item): $quantity = (int) ($cart[$item['id']] ?? 0); ?><div class="checkout-item"><span><?= e($item['title']) ?> <b>× <?= $quantity ?></b></span><strong><?= money((float) $item['price'] * $quantity) ?></strong></div><?php endforeach; ?><div class="summary-line"><span>Shipping</span><strong><?= $shippingCost > 0 ? money($shippingCost) : 'On us' ?></strong></div><div class="summary-total"><span>Total</span><strong><?= money($subtotal + $shippingCost) ?></strong></div><p>Shipping is free on orders over $45.</p></aside></div>
<?php render_footer(); ?>
