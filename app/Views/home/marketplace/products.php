<?php
/** @var array $products  Paginated result {data, page, per_page, total, last_page} */
/** @var array $categories */
/** @var array $stores */
/** @var array $filters */
$f = $filters;
$rows       = $products['data'] ?? [];
$total      = (int)($products['total'] ?? count($rows));
$page       = (int)($products['current_page'] ?? 1);
$lastPage   = (int)($products['last_page'] ?? 1);

// Build a query string preserving current filters (used by filter form + pagination).
function mp_qs(array $base, array $override = []): string {
    $q = array_merge($base, $override);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null && $v !== 0 && $v !== false);
    return http_build_query($q);
}
$baseFilters = [
    'q'         => $f['q'],
    'category'  => $f['categoryId'] ?: null,
    'store'     => $f['storeId'] ?: null,
    'min_price' => $f['minPrice'],
    'max_price' => $f['maxPrice'],
    'rating'    => $f['minRating'] ?: null,
    'in_stock'  => $f['inStock'] ? 1 : null,
    'sort'      => $f['sort'] !== 'popular' ? $f['sort'] : null,
];
?>

<section class="py-3 bg-light border-bottom">
    <div class="container">
        <form method="get" action="/marketplace/products" class="d-flex gap-2">
            <input type="search" name="q" value="<?= e($f['q']) ?>" class="form-control" placeholder="Search products…">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
        </form>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <!-- Filter sidebar -->
            <aside class="col-lg-3">
                <form method="get" action="/marketplace/products">
                    <input type="hidden" name="q" value="<?= e($f['q']) ?>">
                    <input type="hidden" name="sort" value="<?= e($f['sort']) ?>">

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Category</h6>
                            <div class="d-flex flex-column gap-1">
                                <label class="form-check">
                                    <input type="radio" name="category" value="" class="form-check-input" <?= !$f['categoryId'] ? 'checked' : '' ?>>
                                    <span class="form-check-label">All categories</span>
                                </label>
                                <?php foreach ($categories as $c): ?>
                                    <label class="form-check">
                                        <input type="radio" name="category" value="<?= (int)$c['id'] ?>" class="form-check-input" <?= $f['categoryId'] == $c['id'] ? 'checked' : '' ?>>
                                        <span class="form-check-label"><?= e($c['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Store</h6>
                            <select name="store" class="form-select form-select-sm">
                                <option value="">All stores</option>
                                <?php foreach ($stores as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>" <?= $f['storeId'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Price range</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" min="0" step="1" name="min_price" value="<?= e($f['minPrice']) ?>" class="form-control form-control-sm" placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" min="0" step="1" name="max_price" value="<?= e($f['maxPrice']) ?>" class="form-control form-control-sm" placeholder="Max">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Customer Rating</h6>
                            <div class="d-flex flex-column gap-1">
                                <?php foreach ([4, 3, 2, 1] as $stars): ?>
                                    <label class="form-check">
                                        <input type="radio" name="rating" value="<?= $stars ?>" class="form-check-input" <?= $f['minRating'] == $stars ? 'checked' : '' ?>>
                                        <span class="form-check-label">
                                            <?= str_repeat('★', $stars) ?><span class="text-muted"><?= str_repeat('★', 5 - $stars) ?></span>
                                            &amp; up
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                                <label class="form-check">
                                    <input type="radio" name="rating" value="" class="form-check-input" <?= !$f['minRating'] ? 'checked' : '' ?>>
                                    <span class="form-check-label">Any rating</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="form-check">
                                <input type="checkbox" name="in_stock" id="in_stock" value="1" class="form-check-input" <?= $f['inStock'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="in_stock">In stock only</label>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Apply filters</button>
                    <a href="/marketplace/products" class="btn btn-link w-100 mt-1">Clear all</a>
                </form>
            </aside>

            <!-- Results -->
            <div class="col-lg-9">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                    <div class="text-muted small">
                        <?= number_format($total) ?> result<?= $total === 1 ? '' : 's' ?>
                        <?php if ($f['q']): ?> for "<strong><?= e($f['q']) ?></strong>"<?php endif; ?>
                    </div>
                    <form method="get" action="/marketplace/products" class="d-flex gap-2 align-items-center">
                        <?php foreach ($baseFilters as $k => $v): if ($v === null || $k === 'sort') continue; ?>
                            <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                        <?php endforeach; ?>
                        <label class="small text-muted mb-0">Sort by</label>
                        <select name="sort" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <?php
                            $sortOptions = [
                                'popular'    => 'Popularity',
                                'newest'     => 'Newest',
                                'price_asc'  => 'Price: Low to High',
                                'price_desc' => 'Price: High to Low',
                                'rating'     => 'Customer Rating',
                            ];
                            foreach ($sortOptions as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $f['sort'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if (empty($rows)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search text-muted" style="font-size: 48px;"></i>
                        <h5 class="mt-3">No products match your filters</h5>
                        <p class="text-muted">Try adjusting filters or clearing them.</p>
                        <a href="/marketplace/products" class="btn btn-outline-primary">Reset filters</a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($rows as $p): ?>
                            <div class="col-6 col-md-4">
                                <?php include __DIR__ . '/_card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($lastPage > 1): ?>
                        <nav aria-label="Pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php
                                $start = max(1, $page - 2);
                                $end   = min($lastPage, $page + 2);
                                ?>
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= mp_qs($baseFilters, ['page' => $page - 1]) ?>">«</a>
                                </li>
                                <?php if ($start > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?<?= mp_qs($baseFilters, ['page' => 1]) ?>">1</a></li>
                                    <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                <?php endif; ?>
                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= mp_qs($baseFilters, ['page' => $i]) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($end < $lastPage): ?>
                                    <?php if ($end < $lastPage - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                    <li class="page-item"><a class="page-link" href="?<?= mp_qs($baseFilters, ['page' => $lastPage]) ?>"><?= $lastPage ?></a></li>
                                <?php endif; ?>
                                <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= mp_qs($baseFilters, ['page' => $page + 1]) ?>">»</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
