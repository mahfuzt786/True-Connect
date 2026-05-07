<h2>Analytics</h2>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Revenue</div><h4><?= money($data['kpis']['revenue']) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Orders</div><h4><?= number_format($data['kpis']['orders']) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Customers</div><h4><?= number_format($data['kpis']['customers']) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Avg Order Value</div><h4><?= money($data['kpis']['aov']) ?></h4></div></div>
</div>
<div class="card mb-3"><div class="card-header bg-white"><strong>Revenue Trend</strong></div>
    <div class="card-body"><canvas id="rev" height="80"></canvas></div></div>
<div class="row g-3">
    <div class="col-md-6"><div class="card p-3">
        <h6>Top Products</h6>
        <table class="table"><thead><tr><th>Product</th><th>Sold</th></tr></thead><tbody>
        <?php foreach ($data['top_products'] as $p): ?>
        <tr><td><?= e($p['name']) ?></td><td><?= $p['sales_count'] ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div></div>
    <div class="col-md-6"><div class="card p-3">
        <h6>Order Status</h6>
        <canvas id="status" height="180"></canvas>
    </div></div>
</div>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const r = <?= json_encode($data['revenue_chart']) ?>;
    new Chart(document.getElementById('rev'), {type:'line',data:{labels:r.map(x=>x.d),datasets:[{label:'Revenue',data:r.map(x=>parseFloat(x.revenue)),borderColor:'#0d6efd',fill:true,backgroundColor:'rgba(13,110,253,.1)'}]}});
    const s = <?= json_encode($data['order_status']) ?>;
    new Chart(document.getElementById('status'), {type:'doughnut',data:{labels:s.map(x=>x.status),datasets:[{data:s.map(x=>x.cnt),backgroundColor:['#0d6efd','#198754','#ffc107','#dc3545','#6c757d']}]}});
});
</script>
