<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$statuses = ['Pending', 'Shipped', 'Delivered'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $status = (string) ($_POST['status'] ?? '');
    if ($id && in_array($status, $statuses, true)) {
        $update = db()->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $update->execute(['status' => $status, 'id' => $id]);
        flash('Order status updated.');
    } else flash('Choose a valid order status.', 'error');
    redirect('/LABSHEET%207/admin/orders.php');
}
$orders = db()->query('SELECT o.id, o.full_name, o.city, o.total, o.status, o.created_at, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC')->fetchAll();
$lineStmt = db()->prepare('SELECT title_snapshot, quantity FROM order_items WHERE order_id = :id ORDER BY id');
render_header('Manage orders');
?>
<section class="page-heading admin-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span> PAPER & PINE ADMIN</div><h1>Customer orders</h1><p>Every order, from placed to delivered.</p></div><nav class="admin-nav"><a href="/LABSHEET%207/admin/index.php">Inventory</a><a class="active" href="/LABSHEET%207/admin/orders.php">Orders</a><a href="/LABSHEET%207/admin/reports.php">Sales report</a></nav></section>
<?php if ($orders): ?><div class="admin-orders"><?php foreach ($orders as $order): $lineStmt->execute(['id' => $order['id']]); $items = $lineStmt->fetchAll(); ?><article class="admin-order-card"><div class="admin-order-summary"><div><span class="summary-eyebrow">ORDER #<?= (int) $order['id'] ?></span><h2><?= e($order['full_name']) ?></h2><p><?= e($order['email']) ?> · <?= e($order['city']) ?></p><time><?= e(date('M j, Y · g:i a', strtotime($order['created_at']))) ?></time></div><div><strong><?= money($order['total']) ?></strong><a class="text-link" href="/LABSHEET%207/invoice.php?id=<?= (int) $order['id'] ?>">Invoice →</a></div></div><div class="admin-order-items"><?php foreach ($items as $item): ?><span><?= e($item['title_snapshot']) ?> <small>× <?= (int) $item['quantity'] ?></small></span><?php endforeach; ?></div><form class="status-form" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $order['id'] ?>"><label>Status<select name="status"><?php foreach ($statuses as $status): ?><option <?= $status === $order['status'] ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></label><button class="button button-small button-outline" type="submit">Save status</button></form></article><?php endforeach; ?></div><?php else: ?><div class="empty-state"><span>♧</span><h2>No customer orders yet.</h2></div><?php endif; ?>
<?php render_footer(); ?>
