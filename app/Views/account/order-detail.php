<h2>Order #<?= e($order['order_number']) ?></h2>
<p>Status: <span class="badge bg-info"><?= e($order['status']) ?></span> | Total: <strong><?= money($order['total'], $order['currency']) ?></strong></p>
<div class="card"><table class="table"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>
<?php foreach ($items as $i): ?>
<tr><td><?= e($i['product_name']) ?></td><td><?= $i['quantity'] ?></td><td><?= money($i['unit_price']) ?></td><td><?= money($i['total']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
