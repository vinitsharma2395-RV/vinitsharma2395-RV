<?php
require __DIR__ . '/includes/bootstrap.php';
$pdo = db();
$search = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$perPage = 8;
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(title LIKE :title_q OR author LIKE :author_q OR category LIKE :category_q)';
    $params['title_q'] = '%' . $search . '%';
    $params['author_q'] = '%' . $search . '%';
    $params['category_q'] = '%' . $search . '%';
}
if ($category !== '') {
    $where[] = 'category = :category';
    $params['category'] = $category;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$count = $pdo->prepare('SELECT COUNT(*) FROM books' . $whereSql);
$count->execute($params);
$totalBooks = (int) $count->fetchColumn();
$totalPages = max(1, (int) ceil($totalBooks / $perPage));
$page = min($page, $totalPages);
$statement = $pdo->prepare('SELECT id, title, author, category, price, stock, image FROM books' . $whereSql . ' ORDER BY id DESC LIMIT :limit OFFSET :offset');
foreach ($params as $key => $value) $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
$statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$statement->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$statement->execute();
$books = $statement->fetchAll();
$categories = $pdo->query('SELECT DISTINCT category FROM books ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
render_header('Find your next favorite');
?>
<section class="hero">
    <div class="hero-copy"><div class="eyebrow"><span class="eyebrow-line"></span> A LITTLE BOOKSHOP FOR BIG IDEAS</div>
        <h1>Find a book.<br><em>Find your way.</em></h1>
        <p>Stories to get lost in, ideas to take with you, and the perfect book for whatever season you’re in.</p>
        <div class="hero-actions"><a class="button button-dark" href="#browse">Explore the shelves <span>↗</span></a><span class="hero-aside">Curated with care<br>Delivered with love</span></div>
    </div>
    <div class="hero-scene" aria-label="A stack of books beside a cup of tea">
        <div class="sun-disc"></div><div class="scene-plant plant-one">✳</div><div class="scene-plant plant-two">✳</div>
        <div class="scene-book scene-book-back"><span>MAKE ROOM<br>FOR WONDER</span></div><div class="scene-book scene-book-mid"><span>THE ART<br>OF SLOWING<br>DOWN</span></div><div class="scene-book scene-book-front"><span>little<br>things</span><i>COLLECTED THOUGHTS</i></div>
        <div class="scene-cup"><span></span></div><div class="scene-caption">YOUR NEXT CHAPTER<br>STARTS HERE <b>✳</b></div>
    </div>
    <div class="hero-index"><span>01</span><i></i><span>03</span></div>
</section>
<section class="promo-strip"><div class="promo-icon">✳</div><p><strong>A good story travels.</strong> Enjoy free shipping on orders over $45.</p><a href="#browse">Shop the collection <span>→</span></a></section>
<section class="browse-section" id="browse">
    <div class="section-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span> THE BOOKSHELF</div><h2><?= $search || $category ? 'A shelf just for you.' : 'Worth a closer look.' ?></h2><p><?= $search ? 'Showing matches for “' . e($search) . '”.' : 'A few reader favorites to get you started.' ?></p></div><span class="count-note"><?= $totalBooks ?> TITLES TO EXPLORE</span></div>
    <form class="search-tools" method="get" action="/LABSHEET%207/index.php">
        <label class="search-box"><span aria-hidden="true">⌕</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by title, author, or subject…" aria-label="Search books"><button type="submit">Search <span>→</span></button></label>
        <label class="filter-select"><span>Filter by</span><select name="category" onchange="this.form.submit()"><option value="">All shelves</option><?php foreach ($categories as $item): ?><option value="<?= e($item) ?>" <?= $category === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></label>
    </form>
    <?php if ($books): ?><div class="book-grid"><?php foreach ($books as $book) include __DIR__ . '/includes/book-card.php'; ?></div>
        <?php if ($totalPages > 1): ?><nav class="pagination" aria-label="Book pages"><?php for ($p = 1; $p <= $totalPages; $p++): ?><a class="<?= $page === $p ? 'active' : '' ?>" href="?<?= e(http_build_query(['q' => $search, 'category' => $category, 'page' => $p])) ?>" <?= $page === $p ? 'aria-current="page"' : '' ?>><?= $p ?></a><?php endfor; ?></nav><?php endif; ?>
    <?php else: ?><div class="empty-state"><span>◌</span><h3>No books found</h3><p>Try another title, author, or shelf.</p><a class="button button-outline" href="/LABSHEET%207/index.php">Clear filters</a></div><?php endif; ?>
</section>
<section class="quote-banner"><div class="quote-mark">“</div><blockquote>A room without books is like a body without a soul.</blockquote><cite>— CICERO</cite><span class="banner-flower">✳</span></section>
<?php render_footer(); ?>
