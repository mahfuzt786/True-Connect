<div class="container py-4">
<div class="text-center mb-4">
    <?php if($vendor['logo']): ?><img src="<?= e($vendor['logo']) ?>" class="rounded-circle" style="width:100px;height:100px;object-fit:cover;"><?php endif; ?>
    <h2 class="mt-2"><?= e($vendor['business_name']) ?></h2>
    <p class="text-muted"><?= e($vendor['description']) ?></p>
    <div class="text-warning"><?= str_repeat('★', round($vendor['rating'])) ?> <?= $vendor['rating'] ?>/5</div>
</div>
<h4>Products</h4>
<div class="row g-3">
<?php foreach ($products['data'] as $p): ?>
<div class="col-6 col-md-3"><div class="product-card">
    <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
        <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img"><?php endif; ?>
    </a>
    <div class="p-3"><h6><?= e($p['name']) ?></h6><strong><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></strong></div>
</div></div>
<?php endforeach; ?>
</div>
<div class="mt-3"><?= paginate($products) ?></div>
</div>
