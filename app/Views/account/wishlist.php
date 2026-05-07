<h2>My Wishlist</h2>
<div class="row g-3">
<?php foreach ($items as $i): ?>
<div class="col-md-3"><div class="card">
    <?php if($i['image']): ?><img src="<?= e($i['image']) ?>" class="card-img-top"><?php endif; ?>
    <div class="card-body">
        <h6><?= e($i['name']) ?></h6>
        <strong><?= money($i['price']) ?></strong>
        <a href="/shop/<?= e($i['store_slug']) ?>/products/<?= e($i['slug']) ?>" class="btn btn-primary btn-sm w-100 mt-2">View</a>
    </div>
</div></div>
<?php endforeach; ?>
<?php if (empty($items)): ?><div class="col-12 text-center text-muted py-5">Your wishlist is empty.</div><?php endif; ?>
</div>
