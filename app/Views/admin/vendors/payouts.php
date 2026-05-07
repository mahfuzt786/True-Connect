<h2>Vendor Payouts</h2>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Date</th><th>Vendor</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead><tbody>
<?php foreach ($payouts['data'] as $p): ?>
<tr><td><?= formatDate($p['created_at']) ?></td><td><?= e($p['business_name']) ?></td>
<td><?= money($p['amount'], $p['currency']) ?></td><td><?= e($p['method']) ?></td>
<td><span class="badge bg-<?= $p['status']==='completed'?'success':'warning' ?>"><?= e($p['status']) ?></span></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($payouts) ?></div>
