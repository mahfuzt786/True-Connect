<div class="container py-4">
<h2>Checkout</h2>
<form method="POST" action="/shop/<?= e($store['slug']) ?>/checkout">
<?= csrf_field() ?>
<div class="row g-4">
    <div class="col-md-8">
        <div class="card p-4 mb-3">
            <h5>Billing Information</h5>
            <div class="row g-2">
                <div class="col-6"><label>First Name *</label><input name="billing_first_name" class="form-control" required></div>
                <div class="col-6"><label>Last Name *</label><input name="billing_last_name" class="form-control" required></div>
                <div class="col-6"><label>Email *</label><input type="email" name="billing_email" class="form-control" required value="<?= e(auth()['email'] ?? '') ?>"></div>
                <div class="col-6"><label>Phone</label><input name="billing_phone" class="form-control"></div>
                <div class="col-12"><label>Address *</label><input name="billing_address_line1" class="form-control" required></div>
                <div class="col-6"><label>City *</label><input name="billing_city" class="form-control" required></div>
                <div class="col-6"><label>State</label><input name="billing_state" class="form-control"></div>
                <div class="col-6"><label>Country *</label><input name="billing_country" class="form-control" required></div>
                <div class="col-6"><label>ZIP *</label><input name="billing_zip_code" class="form-control" required></div>
            </div>
            <div class="form-check mt-3"><input type="checkbox" name="same_as_billing" value="1" checked id="sab" class="form-check-input"><label for="sab">Shipping address same as billing</label></div>
        </div>
        <div class="card p-4 mb-3" id="shippingFields" style="display:none;">
            <h5>Shipping Address</h5>
            <div class="row g-2">
                <div class="col-6"><input name="shipping_first_name" class="form-control" placeholder="First Name"></div>
                <div class="col-6"><input name="shipping_last_name" class="form-control" placeholder="Last Name"></div>
                <div class="col-12"><input name="shipping_address_line1" class="form-control" placeholder="Address"></div>
                <div class="col-6"><input name="shipping_city" class="form-control" placeholder="City"></div>
                <div class="col-6"><input name="shipping_state" class="form-control" placeholder="State"></div>
                <div class="col-6"><input name="shipping_country" class="form-control" placeholder="Country"></div>
                <div class="col-6"><input name="shipping_zip_code" class="form-control" placeholder="ZIP"></div>
            </div>
        </div>
        <div class="card p-4 mb-3">
            <h5>Payment Method</h5>
            <?php foreach ($paymentMethods as $pm): ?>
                <div class="form-check mb-2">
                    <input type="radio" name="payment_method" value="<?= e($pm['gateway']) ?>" id="pm<?= $pm['id'] ?>" class="form-check-input" required>
                    <label for="pm<?= $pm['id'] ?>" class="form-check-label"><?= e($pm['display_name'] ?: ucfirst($pm['gateway'])) ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card p-4">
            <label>Order Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 sticky-top" style="top:80px;">
            <h5>Order Summary</h5>
            <?php foreach ($cart['items'] as $item): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?= e($item['product_name']) ?> × <?= $item['quantity'] ?></span>
                    <span><?= money($item['quantity']*$item['unit_price'], $store['currency'], $store['currency_symbol']) ?></span>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between mb-1"><span>Subtotal:</span><strong><?= money($cart['subtotal'], $store['currency'], $store['currency_symbol']) ?></strong></div>
            <?php if($cart['coupon_discount']>0): ?><div class="d-flex justify-content-between mb-1 text-success"><span>Discount:</span><strong>-<?= money($cart['coupon_discount'], $store['currency'], $store['currency_symbol']) ?></strong></div><?php endif; ?>
            <hr>
            <div class="d-flex justify-content-between"><h5>Total:</h5><h5><?= money($cart['total'], $store['currency'], $store['currency_symbol']) ?></h5></div>
            <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">Place Order</button>
        </div>
    </div>
</div>
</form>
<script>document.getElementById('sab').addEventListener('change',e=>{document.getElementById('shippingFields').style.display=e.target.checked?'none':'block';});</script>
</div>
