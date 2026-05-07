<h2 class="mb-4">Dashboard</h2>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="d-flex justify-content-between"><div class="text-muted small">Today's Revenue</div><i class="bi bi-cash-stack text-success"></i></div>
        <h3 class="mt-2 mb-0"><?= money($stats['revenue_today'], $store['currency'] ?? 'USD', $store['currency_symbol'] ?? '$') ?></h3>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="d-flex justify-content-between"><div class="text-muted small">Today's Orders</div><i class="bi bi-bag text-primary"></i></div>
        <h3 class="mt-2 mb-0"><?= $stats['orders_today'] ?></h3>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="d-flex justify-content-between"><div class="text-muted small">Total Revenue</div><i class="bi bi-graph-up text-warning"></i></div>
        <h3 class="mt-2 mb-0"><?= money($stats['total_revenue'], $store['currency'] ?? 'USD', $store['currency_symbol'] ?? '$') ?></h3>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="d-flex justify-content-between"><div class="text-muted small">Total Customers</div><i class="bi bi-people text-info"></i></div>
        <h3 class="mt-2 mb-0"><?= number_format($stats['total_customers']) ?></h3>
    </div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-warning shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Pending Orders</div>
                <h4 class="mt-2 mb-0 text-warning"><?= $stats['pending_orders'] ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Low Stock Alerts</div>
                <h4 class="mt-2 mb-0 text-danger"><?= $stats['low_stock_products'] ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Total Orders</div>
            <h4 class="mt-2 mb-0"><?= number_format($stats['total_orders']) ?></h4>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Active Products</div>
            <h4 class="mt-2 mb-0"><?= number_format($stats['total_products']) ?></h4>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Revenue (last 30 days)</strong></div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Order Status</strong></div>
            <div class="card-body"><canvas id="statusChart" height="180"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between"><strong>Recent Orders</strong>
                <a href="/orders" class="small">View all →</a></div>
            <div class="table-responsive"><table class="table mb-0">
                <thead><tr><th>Order#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><a href="/orders/<?= $o['id'] ?>">#<?= e($o['order_number']) ?></a></td>
                        <td><?= e($o['customer_name'] ?? 'Guest') ?></td>
                        <td><?= money($o['total'], $o['currency']) ?></td>
                        <td><span class="badge bg-light text-dark"><?= e($o['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Top Products</strong></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($topProducts as $p): ?>
                    <li class="list-group-item d-flex align-items-center">
                        <?php if ($p['image']): ?><img src="<?= e($p['image']) ?>" width="40" height="40" class="me-2 rounded"><?php endif; ?>
                        <div class="flex-grow-1"><?= e($p['name']) ?><br><small class="text-muted"><?= $p['sales_count'] ?> sold</small></div>
                        <strong><?= money($p['price'], $store['currency'] ?? 'USD') ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;
    const rev = <?= json_encode($revenueChart) ?>;
    new Chart(document.getElementById('revenueChart'), {
        type: 'line', data: {
            labels: rev.map(r => r.d),
            datasets: [{ label: 'Revenue', data: rev.map(r => parseFloat(r.revenue)), borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.1)', fill: true, tension: 0.3 }]
        }, options: { responsive: true, plugins: { legend: { display: false } } }
    });
    const status = <?= json_encode($orderStatusData) ?>;
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut', data: {
            labels: status.map(s => s.status),
            datasets: [{ data: status.map(s => s.cnt), backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6c757d','#0dcaf0'] }]
        }
    });
});
</script>
