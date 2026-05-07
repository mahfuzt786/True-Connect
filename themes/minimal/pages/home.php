<section class="hero-banner">
    <div class="container">
        <h1><?= e($store['name']) ?></h1>
        <p class="text-muted mt-3"><?= e($store['description'] ?? 'Curated essentials.') ?></p>
        <a href="/shop/<?= e($store['slug']) ?>/products" class="btn btn-primary mt-4">Shop Collection</a>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center">Latest</h2>
        <div class="row g-5">
            <?php foreach (array_slice($newest, 0, 8) as $p): ?>
            <div class="col-6 col-md-3"><div class="product-card text-center">
                <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
                    <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img"><?php endif; ?>
                </a>
                <div class="pt-3">
                    <div class="text-uppercase small text-muted"><?= e($p['name']) ?></div>
                    <div class="mt-1"><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></div>
                </div>
            </div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
