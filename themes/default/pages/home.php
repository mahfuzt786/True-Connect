<section class="hero-banner text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to <?= e($store['name']) ?></h1>
        <p class="lead"><?= e($store['description'] ?? 'Discover amazing products at great prices') ?></p>
        <a href="/shop/<?= e($store['slug']) ?>/products" class="btn btn-light btn-lg mt-3">Shop Now</a>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="row g-3">
            <?php foreach ($categories as $c): ?>
            <div class="col-6 col-md-3">
                <a href="/shop/<?= e($store['slug']) ?>/category/<?= e($c['slug']) ?>" class="text-decoration-none">
                    <div class="product-card text-center p-4">
                        <?php if($c['image']): ?><img src="<?= e($c['image']) ?>" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;"><?php else: ?><i class="bi bi-grid fs-1 text-primary"></i><?php endif; ?>
                        <div class="mt-2 text-dark"><?= e($c['name']) ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($featured): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <div class="row g-3">
            <?php foreach ($featured as $p): ?>
                <div class="col-6 col-md-3">
                    <div class="product-card">
                        <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
                            <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img"><?php else: ?><div class="bg-light product-img"></div><?php endif; ?>
                        </a>
                        <div class="p-3">
                            <h6><a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>" class="text-dark text-decoration-none"><?= e($p['name']) ?></a></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></strong>
                                <button class="btn btn-sm btn-primary" onclick="addToCart(<?= $p['id'] ?>)"><i class="bi bi-cart-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($newest): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title">New Arrivals</h2>
        <div class="row g-3">
            <?php foreach ($newest as $p): ?>
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
    </div>
</section>
<?php endif; ?>
