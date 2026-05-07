<h2>Job Queue</h2>
<ul class="nav nav-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#pending">Pending (<?= count($pending) ?>)</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#failed">Failed (<?= count($failed) ?>)</a></li></ul>
<div class="tab-content card p-3">
<div id="pending" class="tab-pane fade show active">
<table class="table"><thead><tr><th>ID</th><th>Queue</th><th>Attempts</th><th>Available</th></tr></thead><tbody>
<?php foreach ($pending as $j): ?><tr><td><?= $j['id'] ?></td><td><?= e($j['queue']) ?></td><td><?= $j['attempts'] ?></td><td><?= formatDateTime($j['available_at']) ?></td></tr><?php endforeach; ?>
</tbody></table>
</div>
<div id="failed" class="tab-pane fade">
<table class="table"><thead><tr><th>ID</th><th>Queue</th><th>Failed At</th><th></th></tr></thead><tbody>
<?php foreach ($failed as $j): ?>
<tr><td><?= $j['id'] ?></td><td><?= e($j['queue']) ?></td><td><?= formatDateTime($j['failed_at']) ?></td>
<td><form method="POST" action="/admin/jobs/<?= $j['id'] ?>/retry" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Retry</button></form></td></tr>
<?php endforeach; ?>
</tbody></table>
</div>
</div>
