<?php
/** @var array $product */
/** @var array $images */
/** @var array|null $vendor */
/** @var array $reviews */
/** @var array $ratingDist */
/** @var int $totalReviews */
/** @var array $related */

$sym = $product['currency_symbol'] ?? '₹';
$discountPct = null;
if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']) {
    $discountPct = (int)round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100);
}
$inStock = (int)$product['track_inventory'] === 0 || (int)$product['quantity'] > 0;
$tags = [];
if (!empty($product['tags'])) {
    $decoded = json_decode($product['tags'], true);
    if (is_array($decoded)) $tags = array_values(array_filter($decoded));
}
?>

<section class="py-3 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/marketplace" class="text-decoration-none">Marketplace</a></li>
                <li class="breadcrumb-item"><a href="/marketplace/products" class="text-decoration-none">Products</a></li>
                <?php if (!empty($product['category_slug'])): ?>
                    <li class="breadcrumb-item"><a href="/marketplace/products?category=<?= (int)$product['category_id'] ?>" class="text-decoration-none"><?= e($product['category_name']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active text-truncate" style="max-width: 250px;"><?= e($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <!-- Gallery -->
            <div class="col-lg-5">
                <div class="position-sticky" style="top: 80px;">
                    <div class="bg-light rounded-3 mb-3 d-flex align-items-center justify-content-center"
                         style="aspect-ratio: 1/1; overflow: hidden;">
                        <?php if (!empty($images)): ?>
                            <img src="<?= e($images[0]['image']) ?>" id="mainImg" alt="<?= e($product['name']) ?>"
                                 style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        <?php else: ?>
                            <i class="bi bi-image text-muted" style="font-size: 80px;"></i>
                        <?php endif; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($images as $i => $img): ?>
                                <button type="button"
                                        class="bg-white border rounded-2 p-1 thumb-btn <?= $i === 0 ? 'border-primary' : '' ?>"
                                        onclick="document.getElementById('mainImg').src=this.dataset.src;
                                                 document.querySelectorAll('.thumb-btn').forEach(b=>b.classList.remove('border-primary'));
                                                 this.classList.add('border-primary')"
                                        data-src="<?= e($img['image']) ?>"
                                        style="width: 64px; height: 64px;">
                                    <img src="<?= e($img['image']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info -->
            <div class="col-lg-7">
                <p class="text-muted mb-1 small">
                    Sold by
                    <a href="/shop/<?= e($product['store_slug']) ?>" class="text-decoration-none"><?= e($product['store_name']) ?></a>
                    <?php if ($vendor): ?>
                        · vendor: <a href="/shop/<?= e($product['store_slug']) ?>/vendors/<?= e($vendor['business_slug']) ?>" class="text-decoration-none"><?= e($vendor['business_name']) ?></a>
                    <?php endif; ?>
                    <?php if ($product['store_type'] === 'marketplace'): ?>
                        <span class="badge bg-primary-subtle text-primary ms-2">Marketplace</span>
                    <?php endif; ?>
                </p>

                <h1 class="h3 fw-bold mb-2"><?= e($product['name']) ?></h1>

                <?php if ((float)$product['rating'] > 0 || (int)$product['review_count'] > 0): ?>
                    <div class="mb-3">
                        <span class="badge bg-success">
                            <?= number_format((float)$product['rating'], 1) ?>
                            <i class="bi bi-star-fill" style="font-size: 11px;"></i>
                        </span>
                        <a href="#reviews" class="text-decoration-none ms-2">
                            <?= number_format((int)$product['review_count']) ?> rating<?= (int)$product['review_count'] === 1 ? '' : 's' ?>
                        </a>
                    </div>
                <?php endif; ?>

                <hr>

                <div class="mb-3">
                    <div class="d-flex align-items-baseline flex-wrap gap-2">
                        <?php if ($discountPct !== null): ?>
                            <span class="badge bg-danger fs-6"><?= $discountPct ?>% off</span>
                        <?php endif; ?>
                        <span class="fs-2 fw-bold"><?= $sym . number_format((float)$product['price']) ?></span>
                        <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                            <span class="text-muted text-decoration-line-through fs-5">M.R.P.: <?= $sym . number_format((float)$product['compare_price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Inclusive of all taxes</small>
                </div>

                <?php if ($inStock): ?>
                    <div class="text-success fw-semibold mb-3">
                        <i class="bi bi-check-circle-fill"></i> In stock
                        <?php if ((int)$product['track_inventory'] === 1 && (int)$product['quantity'] <= 10): ?>
                            <small class="text-warning ms-1">— only <?= (int)$product['quantity'] ?> left!</small>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-danger fw-semibold mb-3"><i class="bi bi-x-circle-fill"></i> Currently out of stock</div>
                <?php endif; ?>

                <?php if (!empty($product['short_description'])): ?>
                    <p class="text-muted"><?= nl2br(e($product['short_description'])) ?></p>
                <?php endif; ?>

                <!-- Add to cart routes through the originating store's cart endpoint. -->
                <form method="POST" action="/shop/<?= e($product['store_slug']) ?>/cart/add" class="mb-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="input-group" style="width: 130px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="const i=this.nextElementSibling;i.value=Math.max(1,(+i.value)-1)">−</button>
                            <input type="number" name="quantity" value="1" min="1" class="form-control text-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="const i=this.previousElementSibling;i.value=(+i.value)+1">+</button>
                        </div>
                        <button class="btn btn-warning btn-lg fw-semibold" type="submit" <?= !$inStock ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                        </button>
                        <button class="btn btn-danger btn-lg fw-semibold" type="submit" name="buy_now" value="1" <?= !$inStock ? 'disabled' : '' ?>>
                            <i class="bi bi-lightning-charge-fill me-2"></i>Buy Now
                        </button>
                        <button class="btn btn-outline-secondary btn-lg" type="button" title="Add to wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </form>

                <hr>

                <!-- Highlights -->
                <h6 class="fw-bold">Highlights</h6>
                <ul class="text-muted small mb-3">
                    <?php if (!empty($product['sku'])): ?><li>SKU: <?= e($product['sku']) ?></li><?php endif; ?>
                    <?php if (!empty($product['weight'])): ?><li>Weight: <?= e($product['weight']) ?> <?= e($product['weight_unit']) ?></li><?php endif; ?>
                    <?php if (!empty($product['length']) || !empty($product['width']) || !empty($product['height'])): ?>
                        <li>Dimensions: <?= e($product['length'] ?? '—') ?> × <?= e($product['width'] ?? '—') ?> × <?= e($product['height'] ?? '—') ?> <?= e($product['dimension_unit']) ?></li>
                    <?php endif; ?>
                    <?php if ($product['type'] === 'digital'): ?><li>Digital product — instant delivery</li><?php endif; ?>
                    <?php if ($tags): ?>
                        <li>
                            Tags:
                            <?php foreach ($tags as $t): ?>
                                <span class="badge bg-light text-dark border"><?= e($t) ?></span>
                            <?php endforeach; ?>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Trust strip -->
                <div class="row g-2 mt-2">
                    <div class="col-4 text-center small text-muted">
                        <i class="bi bi-truck d-block fs-4 text-primary mb-1"></i>Free delivery available
                    </div>
                    <div class="col-4 text-center small text-muted">
                        <i class="bi bi-shield-check d-block fs-4 text-primary mb-1"></i>Secure payments
                    </div>
                    <div class="col-4 text-center small text-muted">
                        <i class="bi bi-arrow-counterclockwise d-block fs-4 text-primary mb-1"></i>Easy returns
                    </div>
                </div>
            </div>
        </div>

        <!-- Description / details tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Description</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs">Specifications</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Reviews (<?= (int)$totalReviews ?>)</button></li>
                </ul>
                <div class="tab-content border border-top-0 p-4 bg-white">
                    <div class="tab-pane fade show active" id="desc">
                        <?= !empty($product['description']) ? $product['description'] : '<p class="text-muted">No description provided.</p>' ?>
                    </div>
                    <div class="tab-pane fade" id="specs">
                        <table class="table table-sm">
                            <tbody>
                                <tr><th style="width:30%;">Type</th><td><?= e(ucfirst($product['type'])) ?></td></tr>
                                <?php if (!empty($product['sku'])): ?><tr><th>SKU</th><td><?= e($product['sku']) ?></td></tr><?php endif; ?>
                                <?php if (!empty($product['barcode'])): ?><tr><th>Barcode</th><td><?= e($product['barcode']) ?></td></tr><?php endif; ?>
                                <?php if (!empty($product['weight'])): ?><tr><th>Weight</th><td><?= e($product['weight']) ?> <?= e($product['weight_unit']) ?></td></tr><?php endif; ?>
                                <?php if (!empty($product['length']) || !empty($product['width']) || !empty($product['height'])): ?>
                                    <tr><th>Dimensions</th><td><?= e($product['length'] ?? '—') ?> × <?= e($product['width'] ?? '—') ?> × <?= e($product['height'] ?? '—') ?> <?= e($product['dimension_unit']) ?></td></tr>
                                <?php endif; ?>
                                <tr><th>Category</th><td><?= e($product['category_name'] ?? '—') ?></td></tr>
                                <tr><th>Sold by</th><td><?= e($product['store_name']) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="reviews">
                        <?php if ($totalReviews === 0): ?>
                            <p class="text-muted">No reviews yet. Be the first to review this product.</p>
                        <?php else: ?>
                            <div class="row g-4 mb-4">
                                <div class="col-md-4 text-center">
                                    <div class="display-4 fw-bold"><?= number_format((float)$product['rating'], 1) ?></div>
                                    <div class="text-warning">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="bi <?= $s <= round((float)$product['rating']) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted"><?= number_format($totalReviews) ?> rating<?= $totalReviews === 1 ? '' : 's' ?></small>
                                </div>
                                <div class="col-md-8">
                                    <?php for ($s = 5; $s >= 1; $s--):
                                        $count = $ratingDist[$s] ?? 0;
                                        $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                                    ?>
                                        <div class="d-flex align-items-center gap-2 mb-1 small">
                                            <span style="width: 50px;"><?= $s ?> star</span>
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: <?= $pct ?>%;"></div>
                                            </div>
                                            <span class="text-muted" style="width: 40px;"><?= $count ?></span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isAuth()): ?>
                            <form method="POST" action="/shop/<?= e($product['store_slug']) ?>/reviews" class="border-top pt-3 mt-3">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                <h6>Write a review</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Rating</label>
                                    <select name="rating" class="form-select form-select-sm w-auto">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <input name="title" placeholder="Headline" class="form-control form-control-sm">
                                </div>
                                <div class="mb-2">
                                    <textarea name="body" placeholder="Share your experience…" class="form-control form-control-sm" required rows="3"></textarea>
                                </div>
                                <button class="btn btn-primary btn-sm">Submit review</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-light border mt-3">
                                <a href="/login">Sign in</a> to write a review.
                            </div>
                        <?php endif; ?>

                        <hr>

                        <?php foreach ($reviews as $r): ?>
                            <div class="border-bottom py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= e($r['user_name'] ?? 'Anonymous') ?></strong>
                                        <div class="text-warning small">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <i class="bi <?= $s <= (int)$r['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= timeAgo($r['created_at']) ?></small>
                                </div>
                                <?php if (!empty($r['title'])): ?><div class="fw-semibold mt-2"><?= e($r['title']) ?></div><?php endif; ?>
                                <p class="mb-0 mt-1"><?= nl2br(e($r['body'] ?? '')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related products -->
        <?php if (!empty($related)): ?>
            <div class="mt-5">
                <h4 class="fw-bold mb-3">You may also like</h4>
                <div class="row g-3">
                    <?php foreach ($related as $p): ?>
                        <div class="col-6 col-md-4 col-lg-2">
                            <?php include __DIR__ . '/_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
