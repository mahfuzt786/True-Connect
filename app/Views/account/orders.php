<h2>My Orders</h2>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Order#</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody>
<?php foreach ($orders['data'] as $o): ?>
<tr><td>#<?= e($o['order_number']) ?></td><td><?= formatDate($o['created_at']) ?></td>
<td><span class="badge bg-info"><?= e($o['status']) ?></span></td>
<td><?= money($o['total'], $o['currency']) ?></td>
<td><a href="/account/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">Details</a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($orders) ?></div>
