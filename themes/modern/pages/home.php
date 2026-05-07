<section class="hero-banner text-center">
    <div class="container">
        <h1><?= e($store['name']) ?></h1>
        <p class="lead fs-4">Bold, beautiful products. Curated for you.</p>
        <a href="/shop/<?= e($store['slug']) ?>/products" class="btn btn-light btn-lg mt-4 px-5">Discover</a>
    </div>
</section>
<section class="py-5 mt-3">
    <div class="container">
        <h2 class="section-title text-center">Featured</h2>
        <div class="row g-4">
            <?php foreach ($featured as $p): ?>
            <div class="col-6 col-md-3"><div class="product-card">
                <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
                    <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img"><?php endif; ?>
                </a>
                <div class="p-3 text-center">
                    <h6 class="fw-bold"><?= e($p['name']) ?></h6>
                    <strong class="fs-5"><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></strong>
                </div>
            </div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
