<?php
/** @var array $p Product row with store_slug, currency_symbol, image, etc. */
$discountPct = null;
if (!empty($p['compare_price']) && $p['compare_price'] > 0 && $p['compare_price'] > $p['price']) {
    $discountPct = (int)round((($p['compare_price'] - $p['price']) / $p['compare_price']) * 100);
}
$sym = $p['currency_symbol'] ?? '₹';
?>
<div class="card h-100 product-card border-0 shadow-sm">
    <a href="/marketplace/products/<?= (int)$p['id'] ?>" class="text-decoration-none text-dark">
        <div class="position-relative bg-light" style="aspect-ratio: 1 / 1; overflow: hidden; border-top-left-radius: var(--bs-card-border-radius); border-top-right-radius: var(--bs-card-border-radius);">
            <?php if (!empty($p['image'])): ?>
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
                     style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center text-muted h-100">
                    <i class="bi bi-image" style="font-size: 48px;"></i>
                </div>
            <?php endif; ?>
            <?php if ($discountPct !== null): ?>
                <span class="position-absolute top-0 start-0 m-2 badge bg-danger"><?= $discountPct ?>% OFF</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-3">
            <small class="text-muted text-truncate d-block"><?= e($p['store_name'] ?? '') ?></small>
            <h6 class="card-title mb-2 text-truncate" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></h6>
            <?php if (!empty($p['rating']) && $p['rating'] > 0): ?>
                <div class="small mb-2">
                    <span class="badge bg-success"><?= number_format((float)$p['rating'], 1) ?> <i class="bi bi-star-fill" style="font-size:9px;"></i></span>
                    <span class="text-muted ms-1">(<?= (int)$p['review_count'] ?>)</span>
                </div>
            <?php endif; ?>
            <div>
                <strong class="fs-5"><?= $sym . number_format((float)$p['price']) ?></strong>
                <?php if (!empty($p['compare_price']) && $p['compare_price'] > $p['price']): ?>
                    <small class="text-muted text-decoration-line-through ms-1"><?= $sym . number_format((float)$p['compare_price']) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </a>
</div>
