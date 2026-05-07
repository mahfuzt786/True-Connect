<h2>Super Admin Dashboard</h2>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><div class="text-muted small">Total Stores</div><h3><?= number_format($stats['total_stores']) ?></h3><small><?= $stats['active_stores'] ?> active</small></div></div>
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><div class="text-muted small">Total Users</div><h3><?= number_format($stats['total_users']) ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><div class="text-muted small">MRR</div><h3><?= money($stats['mrr']) ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><div class="text-muted small">Total Revenue</div><h3><?= money($stats['total_revenue']) ?></h3></div></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Active Subscriptions</div><h4><?= $stats['active_subscriptions'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Trialing</div><h4><?= $stats['trialing'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Total Orders</div><h4><?= number_format($stats['total_orders']) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Conversion</div><h4><?= $stats['total_users'] ? round($stats['active_subscriptions']/$stats['total_users']*100,1) : 0 ?>%</h4></div></div>
</div>
<div class="card mb-3"><div class="card-header bg-white"><strong>Revenue (30 days)</strong></div>
    <div class="card-body"><canvas id="rev" height="80"></canvas></div></div>
<div class="row g-3">
<div class="col-md-6"><div class="card"><div class="card-header bg-white"><strong>Recent Stores</strong></div>
<table class="table mb-0"><thead><tr><th>Store</th><th>Owner</th><th>Status</th></tr></thead><tbody>
<?php foreach ($recentStores as $s): ?><tr><td><a href="/admin/stores/<?= $s['id'] ?>"><?= e($s['name']) ?></a></td><td><?= e($s['owner_email']) ?></td><td><?= e($s['status']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="col-md-6"><div class="card"><div class="card-header bg-white"><strong>Recent Orders</strong></div>
<table class="table mb-0"><thead><tr><th>Order#</th><th>Store</th><th>Total</th></tr></thead><tbody>
<?php foreach ($recentOrders as $o): ?><tr><td>#<?= e($o['order_number']) ?></td><td><?= e($o['store_name']) ?></td><td><?= money($o['total']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{
const r=<?=json_encode($revenueChart)?>;
new Chart(document.getElementById('rev'),{type:'bar',data:{labels:r.map(x=>x.d),datasets:[{label:'Revenue',data:r.map(x=>parseFloat(x.total)),backgroundColor:'#0d6efd'}]}});
});</script>
