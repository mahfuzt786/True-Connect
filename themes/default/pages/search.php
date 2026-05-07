<div class="container py-4">
<h2>Search Results</h2>
<?php if($q): ?><p>Showing results for "<strong><?= e($q) ?></strong>" — <?= count($products) ?> found</p><?php endif; ?>
<div class="row g-3">
<?php foreach ($products as $p): ?>
    <div class="col-6 col-md-3">
        <div class="product-card">
            <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
                <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img"><?php endif; ?>
            </a>
            <div class="p-3"><h6><?= e($p['name']) ?></h6><strong><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></strong></div>
        </div>
    </div>
<?php endforeach; ?>
<?php if (empty($products) && $q): ?><div class="col-12 text-center text-muted py-5">No results found.</div><?php endif; ?>
</div>
</div>
