<div class="container py-4">
<h2>Shopping Cart</h2>
<?php if (empty($cart['items'])): ?>
<div class="text-center py-5"><i class="bi bi-cart-x" style="font-size:80px;color:#ccc;"></i>
    <h4 class="mt-3">Your cart is empty</h4>
    <a href="/shop/<?= e($store['slug']) ?>/products" class="btn btn-primary mt-3">Continue Shopping</a>
</div>
<?php else: ?>
<div class="row g-4">
    <div class="col-md-8">
        <div class="card p-3">
            <?php foreach ($cart['items'] as $item): ?>
            <div class="cart-item d-flex align-items-center">
                <?php if($item['image']): ?><img src="<?= e($item['image']) ?>" width="80" height="80" class="rounded me-3" style="object-fit:cover;"><?php endif; ?>
                <div class="flex-grow-1">
                    <h6><a href="/shop/<?= e($store['slug']) ?>/products/<?= e($item['slug']) ?>" class="text-dark text-decoration-none"><?= e($item['product_name']) ?></a></h6>
                    <small><?= money($item['unit_price'], $store['currency'], $store['currency_symbol']) ?> each</small>
                </div>
                <form method="POST" action="/shop/<?= e($store['slug']) ?>/cart/update" class="d-flex me-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="form-control" style="width:80px;" onchange="this.form.submit()">
                </form>
                <strong class="me-3"><?= money($item['quantity']*$item['unit_price'], $store['currency'], $store['currency_symbol']) ?></strong>
                <form method="POST" action="/shop/<?= e($store['slug']) ?>/cart/remove" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Order Summary</h5>
            <div class="d-flex justify-content-between mb-2"><span>Subtotal:</span><strong><?= money($cart['subtotal'], $store['currency'], $store['currency_symbol']) ?></strong></div>
            <?php if ($cart['coupon_discount']>0): ?><div class="d-flex justify-content-between mb-2 text-success"><span>Discount:</span><strong>-<?= money($cart['coupon_discount'], $store['currency'], $store['currency_symbol']) ?></strong></div><?php endif; ?>
            <hr>
            <div class="d-flex justify-content-between"><h5>Total:</h5><h5><?= money($cart['total'], $store['currency'], $store['currency_symbol']) ?></h5></div>
            <form id="couponForm" class="my-3" onsubmit="event.preventDefault(); applyCoupon();">
                <div class="input-group">
                    <input id="couponCode" placeholder="Coupon code" class="form-control">
                    <button class="btn btn-outline-primary">Apply</button>
                </div>
            </form>
            <a href="/shop/<?= e($store['slug']) ?>/checkout" class="btn btn-primary btn-lg w-100">Checkout</a>
        </div>
    </div>
</div>
<script>
function applyCoupon() {
    const fd = new FormData();
    fd.append('code', document.getElementById('couponCode').value);
    fd.append('_csrf_token', document.querySelector('meta[name=csrf-token]').content);
    fetch('/shop/<?= e($store['slug']) ?>/coupon/apply', { method: 'POST', body: fd })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.error); });
}
</script>
<?php endif; ?>
</div>
