<h2>Audit Log</h2>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Date</th><th>User</th><th>Store</th><th>Action</th><th>Model</th><th>IP</th></tr></thead><tbody>
<?php foreach ($logs['data'] as $l): ?>
<tr><td><small><?= formatDateTime($l['created_at']) ?></small></td>
<td><?= e($l['user_email'] ?? '—') ?></td><td><?= e($l['store_name'] ?? '—') ?></td>
<td><span class="badge bg-info"><?= e($l['action']) ?></span></td>
<td><?= e($l['model_type']) ?>#<?= $l['model_id'] ?></td>
<td><?= e($l['ip_address']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($logs) ?></div>
