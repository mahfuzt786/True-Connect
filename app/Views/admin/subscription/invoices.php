<h2>Invoices</h2>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Invoice#</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($invoices['data'] as $inv): ?>
<tr><td><?= e($inv['invoice_number']) ?></td><td><?= formatDate($inv['created_at']) ?></td>
<td><?= money($inv['amount'], $inv['currency']) ?></td>
<td><span class="badge bg-<?= $inv['status']==='paid'?'success':'warning' ?>"><?= e($inv['status']) ?></span></td>
<td><a href="/subscription/invoices/<?= $inv['id'] ?>/download" class="btn btn-sm btn-outline-secondary">Download</a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($invoices) ?></div>
