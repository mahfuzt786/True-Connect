<div class="container py-4">
<h2><?= $catSlug ? e(ucfirst($catSlug)) : 'All Products' ?></h2>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card p-3">
            <h6>Filter</h6>
            <form>
                <div class="mb-2"><label>Category</label><select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    <?php foreach ($categories as $c): ?><option value="<?= e($c['slug']) ?>" <?= $catSlug==$c['slug']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                </select></div>
                <div class="mb-2"><label>Sort By</label><select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option>
                    <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High to Low</option>
                    <option value="popular" <?= $sort==='popular'?'selected':'' ?>>Best Sellers</option>
                    <option value="rating" <?= $sort==='rating'?'selected':'' ?>>Top Rated</option>
                </select></div>
                <div class="row g-2"><div class="col-6"><input type="number" name="min_price" placeholder="Min" class="form-control"></div>
                <div class="col-6"><input type="number" name="max_price" placeholder="Max" class="form-control"></div></div>
                <button class="btn btn-primary w-100 mt-3">Apply</button>
            </form>
        </div>
    </div>
    <div class="col-md-9">
        <div class="row g-3">
            <?php foreach ($products['data'] as $p): ?>
                <div class="col-6 col-md-4">
                    <div class="product-card">
                        <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
                            <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img"><?php else: ?><div class="bg-light product-img"></div><?php endif; ?>
                        </a>
                        <div class="p-3">
                            <h6><a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>" class="text-dark text-decoration-none"><?= e($p['name']) ?></a></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div><strong><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></strong>
                                <?php if($p['compare_price']>$p['price']): ?><small class="price-old ms-2"><?= money($p['compare_price'], $store['currency'], $store['currency_symbol']) ?></small><?php endif; ?>
                                </div>
                                <button class="btn btn-sm btn-primary" onclick="addToCart(<?= $p['id'] ?>)"><i class="bi bi-cart-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4"><?= paginate($products) ?></div>
    </div>
</div>
</div>
