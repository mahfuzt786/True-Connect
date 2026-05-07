<h2>Sales Report</h2>
<form class="card p-3 mb-3"><div class="row g-2">
    <div class="col-md-3"><input type="date" name="from" value="<?= e($from) ?>" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="to" value="<?= e($to) ?>" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Generate</button></div>
    <div class="col-md-4"><a href="/dashboard/reports/export?type=sales&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary w-100"><i class="bi bi-download"></i> Export CSV</a></div>
</div></form>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Orders</div><h4><?= $data['summary']['orders'] ?? 0 ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Revenue</div><h4><?= money($data['summary']['revenue'] ?? 0) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Avg Order Value</div><h4><?= money($data['summary']['aov'] ?? 0) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Tax Collected</div><h4><?= money($data['summary']['tax'] ?? 0) ?></h4></div></div>
</div>
<div class="card"><table class="table mb-0"><thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead><tbody>
<?php foreach ($data['daily'] as $d): ?>
<tr><td><?= formatDate($d['d']) ?></td><td><?= $d['orders'] ?></td><td><?= money($d['revenue']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
