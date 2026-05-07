<h2>All Subscriptions</h2>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Store</th><th>Owner</th><th>Plan</th><th>Cycle</th><th>Status</th><th>Amount</th><th>Period End</th></tr></thead><tbody>
<?php foreach ($subscriptions['data'] as $s): ?>
<tr><td><?= e($s['store_name']) ?></td><td><?= e($s['owner_email']) ?></td><td><?= e($s['plan_name']) ?></td>
<td><?= e($s['billing_cycle']) ?></td>
<td><span class="badge bg-<?= $s['status']==='active'?'success':($s['status']==='trialing'?'info':'secondary') ?>"><?= e($s['status']) ?></span></td>
<td><?= money($s['amount'], $s['currency']) ?></td>
<td><?= formatDate($s['current_period_end']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($subscriptions) ?></div>
