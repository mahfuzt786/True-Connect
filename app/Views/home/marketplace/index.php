<!-- Hero with search -->
<section class="hero py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="display-5 fw-bold">Discover from thousands of vendors</h1>
            <p class="lead opacity-75 mb-4">Shop across every store and seller on the platform — one cart, one checkout.</p>
            <form action="/marketplace/products" method="get" class="mx-auto" style="max-width: 600px;">
                <div class="input-group input-group-lg">
                    <input type="search" name="q" class="form-control" placeholder="Search for products, brands, vendors…">
                    <button class="btn btn-light fw-semibold" type="submit"><i class="bi bi-search me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Categories -->
<?php if (!empty($categories)): ?>
<section class="py-4 border-bottom bg-white">
    <div class="container">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <?php foreach ($categories as $cat): ?>
                <a href="/marketplace/products?category=<?= (int)$cat['id'] ?>"
                   class="btn btn-sm btn-outline-secondary rounded-pill">
                    <?= e($cat['name']) ?>
                    <span class="text-muted ms-1">(<?= (int)$cat['product_count'] ?>)</span>
                </a>
            <?php endforeach; ?>
            <a href="/marketplace/products" class="btn btn-sm btn-outline-primary rounded-pill">All products →</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured products -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="fw-bold mb-0">Featured products</h3>
            <a href="/marketplace/products" class="text-decoration-none">View all →</a>
        </div>
        <?php if (empty($featured)): ?>
            <div class="alert alert-light border text-center">
                No products yet. <a href="/register">Open a store</a> and start selling.
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($featured as $p): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <?php include __DIR__ . '/_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Top stores -->
<?php if (!empty($stores)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="fw-bold mb-0">Top stores</h3>
            <a href="/marketplace/stores" class="text-decoration-none">All stores →</a>
        </div>
        <div class="row g-3">
            <?php foreach ($stores as $s): ?>
                <div class="col-6 col-md-3">
                    <a href="/shop/<?= e($s['slug']) ?>" class="text-decoration-none text-dark">
                        <div class="bg-white rounded-3 p-3 h-100 shadow-sm text-center">
                            <?php if (!empty($s['logo'])): ?>
                                <img src="<?= e($s['logo']) ?>" alt="<?= e($s['name']) ?>" style="height:64px;object-fit:contain;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px;">
                                    <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <h6 class="fw-bold mt-3 mb-1"><?= e($s['name']) ?></h6>
                            <small class="text-muted d-block"><?= (int)$s['product_count'] ?> products</small>
                            <?php if ($s['type'] === 'marketplace'): ?>
                                <span class="badge bg-primary-subtle text-primary mt-2">Marketplace</span>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
