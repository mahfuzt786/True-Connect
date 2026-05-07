<?php /** @var array $stores */ /** @var string $type */ ?>
<section class="hero py-5">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Browse Stores</h1>
        <p class="lead opacity-75">Every active store on the platform — discover marketplaces and single-vendor shops.</p>
    </div>
</section>

<section class="py-3 border-bottom bg-white">
    <div class="container">
        <ul class="nav nav-pills justify-content-center">
            <li class="nav-item"><a class="nav-link <?= $type === '' ? 'active' : '' ?>" href="/marketplace/stores">All</a></li>
            <li class="nav-item"><a class="nav-link <?= $type === 'marketplace' ? 'active' : '' ?>" href="/marketplace/stores?type=marketplace">Marketplaces</a></li>
            <li class="nav-item"><a class="nav-link <?= $type === 'ecommerce' ? 'active' : '' ?>" href="/marketplace/stores?type=ecommerce">Single-vendor shops</a></li>
        </ul>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (empty($stores)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No stores found.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($stores as $s): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="/shop/<?= e($s['slug']) ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <?php if (!empty($s['logo'])): ?>
                                        <img src="<?= e($s['logo']) ?>" alt="<?= e($s['name']) ?>" style="height:64px;object-fit:contain;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center fw-bold text-muted" style="width:64px;height:64px;font-size:24px;">
                                            <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <h6 class="fw-bold mt-3 mb-1"><?= e($s['name']) ?></h6>
                                    <small class="text-muted"><?= (int)$s['product_count'] ?> products</small>
                                    <?php if ($s['type'] === 'marketplace'): ?>
                                        <div><span class="badge bg-primary-subtle text-primary mt-2">Marketplace</span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
