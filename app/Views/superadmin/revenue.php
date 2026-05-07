<h2>Revenue</h2>
<div class="row g-3">
<div class="col-md-6"><div class="card p-3"><h5>Monthly Revenue</h5>
<table class="table"><thead><tr><th>Month</th><th>Transactions</th><th>Revenue</th></tr></thead><tbody>
<?php foreach ($monthly as $m): ?><tr><td><?= e($m['month']) ?></td><td><?= $m['transactions'] ?></td><td><?= money($m['revenue']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="col-md-6"><div class="card p-3"><h5>Revenue by Plan</h5>
<table class="table"><thead><tr><th>Plan</th><th>Subscribers</th><th>MRR</th></tr></thead><tbody>
<?php foreach ($byPlan as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= $p['subscribers'] ?></td><td><?= money($p['mrr'] ?? 0) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
</div>
