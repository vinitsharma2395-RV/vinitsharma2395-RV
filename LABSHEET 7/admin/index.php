<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $title = trim((string) ($_POST['title'] ?? ''));
    $author = trim((string) ($_POST['author'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $image = trim((string) ($_POST['image'] ?? ''));
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
    if ($action === 'add') {
        if ($title === '' || text_length($title) > 220) $errors[] = 'Enter a title under 220 characters.';
        if ($author === '' || text_length($author) > 160) $errors[] = 'Enter an author under 160 characters.';
        if ($category === '' || text_length($category) > 100) $errors[] = 'Enter a category under 100 characters.';
        if ($price === false || $price < 0 || $price > 99999999) $errors[] = 'Enter a valid price.';
        if ($stock === false || $stock < 0) $errors[] = 'Enter a valid stock quantity.';
        if ($image !== '' && (text_length($image) > 500 || !str_starts_with($image, '/') && !preg_match('~^https?://~i', $image))) $errors[] = 'Image must be a local path or an http(s) URL.';
        if (!$errors) {
            $insert = db()->prepare('INSERT INTO books (title, author, category, description, price, stock, image) VALUES (:title, :author, :category, :description, :price, :stock, :image)');
            $insert->execute(['title' => $title, 'author' => $author, 'category' => $category, 'description' => $description, 'price' => $price, 'stock' => $stock, 'image' => $image ?: null]);
            flash('The new book has been added.');
            redirect('/LABSHEET%207/admin/index.php');
        }
    } elseif ($action === 'update') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) $errors[] = 'Select a valid book.';
        if ($price === false || $price < 0 || $price > 99999999) $errors[] = 'Enter a valid price.';
        if ($stock === false || $stock < 0) $errors[] = 'Enter a valid stock quantity.';
        if (!$errors) {
            $update = db()->prepare('UPDATE books SET price = :price, stock = :stock WHERE id = :id');
            $update->execute(['price' => $price, 'stock' => $stock, 'id' => $id]);
            flash('Price and inventory updated.');
            redirect('/LABSHEET%207/admin/index.php');
        }
    }
}
$books = db()->query('SELECT id, title, author, category, price, stock FROM books ORDER BY title')->fetchAll();
render_header('Book inventory');
?>
<section class="page-heading admin-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span> PAPER & PINE ADMIN</div><h1>Book inventory</h1><p>Keep the shelves fresh and the details up to date.</p></div><nav class="admin-nav"><a class="active" href="/LABSHEET%207/admin/index.php">Inventory</a><a href="/LABSHEET%207/admin/orders.php">Orders</a><a href="/LABSHEET%207/admin/reports.php">Sales report</a></nav></section>
<?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<section class="admin-add-panel"><div><div class="eyebrow"><span class="eyebrow-line"></span> GROW THE SHELF</div><h2>Add a new book</h2><p>Introduce readers to something wonderful.</p></div><form method="post" class="admin-book-form" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="add"><label>Title<input name="title" required maxlength="220"></label><label>Author<input name="author" required maxlength="160"></label><label>Category<input name="category" required maxlength="100" placeholder="Fiction"></label><label>Price<input type="number" name="price" min="0" step="0.01" required></label><label>Stock<input type="number" name="stock" min="0" step="1" required></label><label>Cover image URL <small>Optional</small><input type="text" name="image" maxlength="500" placeholder="/assets/covers/book.jpg"></label><label class="admin-wide">Description<textarea name="description" rows="3"></textarea></label><button class="button button-dark" type="submit">Add to inventory <span>＋</span></button></form></section>
<section class="admin-table-section"><div class="section-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span> CURRENT SHELF</div><h2><?= count($books) ?> books</h2></div></div><div class="table-wrap"><table class="admin-table"><thead><tr><th>Book</th><th>Category</th><th>Price</th><th>Stock</th><th>Save changes</th></tr></thead><tbody><?php foreach ($books as $book): ?><tr><td><strong><?= e($book['title']) ?></strong><small><?= e($book['author']) ?></small></td><td><?= e($book['category']) ?></td><td colspan="3"><form method="post" class="inventory-edit"><?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $book['id'] ?>"><label class="sr-only" for="price-<?= (int) $book['id'] ?>">Price for <?= e($book['title']) ?></label><input id="price-<?= (int) $book['id'] ?>" type="number" name="price" min="0" step="0.01" required value="<?= e($book['price']) ?>"><label class="sr-only" for="stock-<?= (int) $book['id'] ?>">Stock for <?= e($book['title']) ?></label><input id="stock-<?= (int) $book['id'] ?>" type="number" name="stock" min="0" step="1" required value="<?= (int) $book['stock'] ?>"><button class="button button-small button-outline" type="submit">Update</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php render_footer(); ?>
