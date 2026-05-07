<h2><?= e($customer['name']) ?></h2>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Total Orders</div><h4><?= $stats['total_orders'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Total Spent</div><h4><?= money($stats['total_spent']) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Last Order</div><h6><?= $stats['last_order'] ? formatDate($stats['last_order']) : '—' ?></h6></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Status</div><h6><?= ucfirst($customer['status']) ?></h6></div></div>
</div>
<div class="card mb-3"><div class="card-header bg-white"><strong>Recent Orders</strong></div>
<table class="table mb-0"><thead><tr><th>Order#</th><th>Date</th><th>Status</th><th>Total</th></tr></thead><tbody>
<?php foreach ($orders as $o): ?>
<tr><td><a href="/orders/<?= $o['id'] ?>">#<?= e($o['order_number']) ?></a></td><td><?= formatDate($o['created_at']) ?></td>
<td><?= e($o['status']) ?></td><td><?= money($o['total'], $o['currency']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
