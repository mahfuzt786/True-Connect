<div class="container py-4">
<nav><a href="/shop/<?= e($store['slug']) ?>">Home</a> / <a href="/shop/<?= e($store['slug']) ?>/products">Shop</a> / <?= e($product['name']) ?></nav>
<div class="row g-4 mt-2">
<div class="col-md-6">
    <?php if (!empty($images)): ?>
        <img src="<?= e($images[0]['image']) ?>" id="mainImg" class="img-fluid rounded">
        <div class="d-flex gap-2 mt-2">
            <?php foreach ($images as $img): ?>
                <img src="<?= e($img['image']) ?>" onclick="document.getElementById('mainImg').src=this.src" style="width:80px;height:80px;object-fit:cover;cursor:pointer;border-radius:5px;">
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-light rounded" style="height:400px;"></div>
    <?php endif; ?>
</div>
<div class="col-md-6">
    <h1><?= e($product['name']) ?></h1>
    <?php if($product['rating']>0): ?>
        <div class="mb-2"><?= str_repeat('★', round($product['rating'])) ?><?= str_repeat('☆', 5-round($product['rating'])) ?> <small><?= $product['review_count'] ?> reviews</small></div>
    <?php endif; ?>
    <div class="mb-3">
        <span class="display-6 fw-bold text-primary"><?= money($product['price'], $store['currency'], $store['currency_symbol']) ?></span>
        <?php if($product['compare_price']>$product['price']): ?><span class="price-old ms-2"><?= money($product['compare_price'], $store['currency'], $store['currency_symbol']) ?></span><?php endif; ?>
    </div>
    <?php if($product['short_description']): ?><p><?= nl2br(e($product['short_description'])) ?></p><?php endif; ?>
    <?php if($vendor): ?><p>Sold by: <a href="/shop/<?= e($store['slug']) ?>/vendors/<?= e($vendor['business_slug']) ?>"><?= e($vendor['business_name']) ?></a></p><?php endif; ?>

    <form onsubmit="event.preventDefault(); addToCart(<?= $product['id'] ?>, this.qty.value);" class="mb-4">
        <div class="d-flex gap-2 align-items-center">
            <input type="number" name="qty" value="1" min="1" class="form-control" style="width:80px;">
            <button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-cart-plus me-2"></i>Add to Cart</button>
            <button class="btn btn-outline-danger btn-lg" type="button" onclick="toggleWishlist(<?= $product['id'] ?>)"><i class="bi bi-heart"></i></button>
        </div>
    </form>

    <div><strong>SKU:</strong> <?= e($product['sku'] ?? '—') ?></div>
    <?php if($product['quantity'] > 0): ?>
        <div class="text-success"><i class="bi bi-check-circle"></i> In Stock</div>
    <?php else: ?>
        <div class="text-danger"><i class="bi bi-x-circle"></i> Out of Stock</div>
    <?php endif; ?>
</div>
</div>

<div class="card mt-4 p-4">
    <h4>Description</h4>
    <div><?= $product['description'] ?></div>
</div>

<div class="card mt-3 p-4">
    <h4>Reviews (<?= count($reviews) ?>)</h4>
    <?php if (isAuth()): ?>
    <form method="POST" action="/shop/<?= e($store['slug']) ?>/reviews" class="mb-4">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <div class="mb-2"><label>Rating</label><select name="rating" class="form-select w-auto"><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= str_repeat('★',$i) ?></option><?php endfor; ?></select></div>
        <div class="mb-2"><input name="title" placeholder="Title (optional)" class="form-control"></div>
        <div class="mb-2"><textarea name="body" placeholder="Your review..." class="form-control" required></textarea></div>
        <button class="btn btn-primary">Submit Review</button>
    </form>
    <?php endif; ?>
    <?php foreach ($reviews as $r): ?>
        <div class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between">
                <strong><?= e($r['user_name']) ?></strong>
                <small class="text-muted"><?= timeAgo($r['created_at']) ?></small>
            </div>
            <div class="text-warning"><?= str_repeat('★', $r['rating']) ?></div>
            <p><?= e($r['body']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($related): ?>
<h4 class="mt-5">Related Products</h4>
<div class="row g-3">
    <?php foreach ($related as $p): ?>
        <div class="col-6 col-md-2">
            <div class="product-card">
                <a href="/shop/<?= e($store['slug']) ?>/products/<?= e($p['slug']) ?>">
                    <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" class="product-img" style="height:150px;"><?php endif; ?>
                </a>
                <div class="p-2 text-center">
                    <small><?= e($p['name']) ?></small><br>
                    <strong><?= money($p['price'], $store['currency'], $store['currency_symbol']) ?></strong>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div>
