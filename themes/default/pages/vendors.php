<div class="container py-4">
<h2>Our Vendors</h2>
<div class="row g-3">
<?php foreach ($vendors as $v): ?>
<div class="col-md-3"><div class="card text-center p-3">
    <?php if($v['logo']): ?><img src="<?= e($v['logo']) ?>" class="rounded-circle mx-auto" style="width:80px;height:80px;object-fit:cover;"><?php else: ?><i class="bi bi-shop fs-1"></i><?php endif; ?>
    <h6 class="mt-2"><?= e($v['business_name']) ?></h6>
    <small><?= $v['product_count'] ?> products</small>
    <a href="/shop/<?= e($store['slug']) ?>/vendors/<?= e($v['business_slug']) ?>" class="btn btn-sm btn-outline-primary mt-2">Visit Shop</a>
</div></div>
<?php endforeach; ?>
</div>
</div>
