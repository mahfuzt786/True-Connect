<div class="container py-4">
<h2><?= e($category['name']) ?></h2>
<?php if($category['description']): ?><p class="text-muted"><?= e($category['description']) ?></p><?php endif; ?>
<div class="row g-3">
<?php foreach ($products['data'] as $p): ?>
    <div class="col-6 col-md-3">
        <div class="product-card">
            <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
                <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img"><?php else: ?><div class="bg-light product-img"></div><?php endif; ?>
            </a>
            <div class="p-3">
                <h6><a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>" class="text-dark text-decoration-none"><?= e($p['name']) ?></a></h6>
                <strong><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></strong>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<div class="mt-4"><?= paginate($products) ?></div>
</div>
