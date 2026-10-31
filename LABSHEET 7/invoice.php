<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Order not found.'); }
$stmt = db()->prepare('SELECT * FROM orders WHERE id = :id AND (user_id = :user_id OR :is_admin = 1)');
$stmt->execute(['id' => $id, 'user_id' => $user['id'], 'is_admin' => $user['role'] === 'admin' ? 1 : 0]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); exit('Order not found.'); }
$lines = db()->prepare('SELECT title_snapshot, author_snapshot, unit_price, quantity FROM order_items WHERE order_id = :order ORDER BY id');
$lines->execute(['order' => $id]);
$items = $lines->fetchAll();
$itemsSubtotal = array_sum(array_map(fn($item) => (float) $item['unit_price'] * (int) $item['quantity'], $items));
$shipping = max(0, (float) $order['total'] - $itemsSubtotal);
render_header('Order confirmation');
?>
<section class="invoice-wrap"><div class="invoice-success"><span>✓</span><div><div class="eyebrow">ORDER PLACED</div><h1>Thank you, <?= e(explode(' ', $order['full_name'])[0]) ?>.</h1><p>Your next chapter is on its way. We’ll take good care of it.</p></div></div><article class="invoice-card"><div class="invoice-top"><div><a class="brand" href="/LABSHEET%207/index.php"><span class="brand-mark">P</span><span>paper<span class="brand-amp">&</span>pine</span></a><p>ORDER #<?= (int) $order['id'] ?></p></div><div class="invoice-status"><span><?= e(strtoupper($order['status'])) ?></span><time><?= e(date('M j, Y', strtotime($order['created_at']))) ?></time></div></div><div class="invoice-address"><div><span class="summary-eyebrow">DELIVERING TO</span><strong><?= e($order['full_name']) ?></strong><span><?= e($order['address']) ?></span><span><?= e($order['city']) ?>, <?= e($order['postal_code']) ?></span><span><?= e($order['phone']) ?></span></div><div><span class="summary-eyebrow">ORDER STATUS</span><strong><?= e($order['status']) ?></strong><span>We’ll update your order as it makes its way to you.</span></div></div><div class="invoice-lines"><div class="invoice-table-head"><span>BOOK</span><span>QTY</span><span>PRICE</span><span>LINE TOTAL</span></div><?php foreach ($items as $item): ?><div class="invoice-line"><span><strong><?= e($item['title_snapshot']) ?></strong><small><?= e($item['author_snapshot']) ?></small></span><span><?= (int) $item['quantity'] ?></span><span><?= money($item['unit_price']) ?></span><strong><?= money((float) $item['unit_price'] * (int) $item['quantity']) ?></strong></div><?php endforeach; ?><div class="invoice-line invoice-shipping"><span>Shipping</span><span>—</span><span><?= $shipping > 0 ? money($shipping) : 'Free' ?></span><strong><?= money($shipping) ?></strong></div></div><div class="invoice-total"><span>Order total</span><strong><?= money($order['total']) ?></strong></div><div class="invoice-actions"><a class="button button-dark" href="/LABSHEET%207/orders.php">View order history <span>→</span></a><button class="button button-outline" onclick="window.print()" type="button">Print invoice</button></div></article></section>
<?php render_footer(); ?>
