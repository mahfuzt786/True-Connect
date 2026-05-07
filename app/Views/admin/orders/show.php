<div class="d-flex justify-content-between mb-3">
    <h2>Order #<?= e($order['order_number']) ?></h2>
    <div>
        <a href="/orders/<?= $order['id'] ?>/invoice" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-file-pdf"></i> Invoice</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between"><strong>Items</strong>
                <span>Status: <span class="badge bg-info"><?= e($order['status']) ?></span></span>
            </div>
            <table class="table mb-0">
                <thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $i): ?>
                    <tr>
                        <td><?= e($i['product_name']) ?><?php if($i['variant_name']): ?><br><small><?= e($i['variant_name']) ?></small><?php endif; ?></td>
                        <td><?= e($i['sku']) ?></td>
                        <td><?= $i['quantity'] ?></td>
                        <td><?= money($i['unit_price'], $order['currency']) ?></td>
                        <td><?= money($i['total'], $order['currency']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="card-footer text-end">
                <div>Subtotal: <?= money($order['subtotal'], $order['currency']) ?></div>
                <?php if($order['discount_amount']>0): ?><div>Discount: -<?= money($order['discount_amount'], $order['currency']) ?></div><?php endif; ?>
                <div>Tax: <?= money($order['tax_amount'], $order['currency']) ?></div>
                <div>Shipping: <?= money($order['shipping_amount'], $order['currency']) ?></div>
                <h5 class="mt-2">Total: <?= money($order['total'], $order['currency']) ?></h5>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Update Status</strong></div>
            <div class="card-body">
                <form method="POST" action="/orders/<?= $order['id'] ?>/status">
                    <?= csrf_field() ?>
                    <div class="row g-2">
                        <div class="col-md-4"><select name="status" class="form-select">
                            <?php foreach (['confirmed','processing','shipped','out_for_delivery','delivered','cancelled','on_hold'] as $s): ?>
                                <option value="<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                        <div class="col-md-6"><input name="note" placeholder="Optional note" class="form-control"></div>
                        <div class="col-md-2"><button class="btn btn-primary w-100">Update</button></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-white"><strong>History</strong></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($history as $h): ?>
                    <li class="list-group-item">
                        <strong><?= e($h['status']) ?></strong> — <?= e($h['comment'] ?? '') ?>
                        <br><small class="text-muted"><?= formatDateTime($h['created_at']) ?> by <?= e($h['created_by_name'] ?? 'System') ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-4">
        <?php if ($customer): ?>
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Customer</strong></div>
            <div class="card-body">
                <div><?= e($customer['name']) ?></div>
                <div><?= e($customer['email']) ?></div>
                <div><?= e($customer['phone'] ?? '') ?></div>
            </div>
        </div>
        <?php endif; ?>
        <?php $billing = json_decode($order['billing_address'] ?? '{}', true) ?: []; ?>
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Billing Address</strong></div>
            <div class="card-body small">
                <?= e(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?><br>
                <?= e($billing['address_line1'] ?? '') ?><br>
                <?= e($billing['city'] ?? '') ?>, <?= e($billing['state'] ?? '') ?> <?= e($billing['zip_code'] ?? '') ?><br>
                <?= e($billing['country'] ?? '') ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-white"><strong>Payment</strong></div>
            <div class="card-body">
                <div>Method: <?= e($order['payment_method']) ?></div>
                <div>Status: <span class="badge bg-<?= $order['payment_status']==='paid'?'success':'warning' ?>"><?= e($order['payment_status']) ?></span></div>
            </div>
        </div>
    </div>
</div>
