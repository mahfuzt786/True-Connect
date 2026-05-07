<h2>Email Logs</h2>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Date</th><th>To</th><th>Subject</th><th>Status</th></tr></thead><tbody>
<?php foreach ($logs['data'] as $l): ?>
<tr><td><small><?= formatDateTime($l['created_at']) ?></small></td>
<td><?= e($l['to_email']) ?></td><td><?= e($l['subject']) ?></td>
<td><span class="badge bg-<?= $l['status']==='sent'?'success':'danger' ?>"><?= e($l['status']) ?></span></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($logs) ?></div>
