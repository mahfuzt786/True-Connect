<h2>Customer Analytics</h2>
<div class="row g-3 mb-3">
<div class="col-md-6"><div class="card p-3 text-center"><div class="text-muted">New Customers</div><h3><?= $data['new_vs_returning']['new_customers'] ?? 0 ?></h3></div></div>
<div class="col-md-6"><div class="card p-3 text-center"><div class="text-muted">Returning Customers</div><h3><?= $data['new_vs_returning']['returning_customers'] ?? 0 ?></h3></div></div>
</div>
<div class="card"><div class="card-body"><h5>Top Customers</h5>
<table class="table"><thead><tr><th>Name</th><th>Orders</th><th>Spent</th></tr></thead><tbody>
<?php foreach ($data['top_customers'] as $c): ?>
<tr><td><?= e($c['name']) ?></td><td><?= $c['orders'] ?></td><td><?= money($c['spent']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
