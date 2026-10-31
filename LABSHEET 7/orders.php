<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$stmt = db()->prepare('SELECT id, total, status, created_at FROM orders WHERE user_id = :user ORDER BY created_at DESC');
$stmt->execute(['user' => $user['id']]);
$orders = $stmt->fetchAll();
$lineStmt = db()->prepare('SELECT title_snapshot, quantity, unit_price FROM order_items WHERE order_id = :id ORDER BY id');
render_header('My orders');
?>
<section class="page-heading"><div class="eyebrow"><span class="eyebrow-line"></span> YOUR READING JOURNEY</div><h1>Order history</h1><p>Your past purchases, all in one place.</p></section>
<?php if ($orders): ?><div class="orders-list"><?php foreach ($orders as $order): $lineStmt->execute(['id' => $order['id']]); $lines = $lineStmt->fetchAll(); ?><article class="order-card"><div class="order-card-head"><div><span class="summary-eyebrow">ORDER #<?= (int) $order['id'] ?></span><time><?= e(date('F j, Y', strtotime($order['created_at']))) ?></time></div><span class="status-pill status-<?= strtolower(e($order['status'])) ?>"><?= e($order['status']) ?></span></div><div class="order-book-lines"><?php foreach ($lines as $line): ?><div><span><?= e($line['title_snapshot']) ?> <small>× <?= (int) $line['quantity'] ?></small></span><span><?= money((float) $line['unit_price'] * (int) $line['quantity']) ?></span></div><?php endforeach; ?></div><div class="order-card-bottom"><strong><?= money($order['total']) ?></strong><a class="text-link" href="/LABSHEET%207/invoice.php?id=<?= (int) $order['id'] ?>">View invoice →</a></div></article><?php endforeach; ?></div><?php else: ?><div class="empty-state"><span>✳</span><h2>No orders just yet.</h2><p>Your reading journey is waiting to begin.</p><a class="button button-dark" href="/LABSHEET%207/index.php#browse">Find your next read <span>→</span></a></div><?php endif; ?>
<?php render_footer(); ?>
