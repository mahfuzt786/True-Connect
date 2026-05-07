<h2>Product Analytics</h2>
<div class="row g-3">
<div class="col-md-6"><div class="card p-3"><h5>Best Sellers</h5>
    <table class="table"><thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead><tbody>
    <?php foreach ($data['best_sellers'] as $p): ?>
    <tr><td><?= e($p['name']) ?></td><td><?= $p['units_sold'] ?></td><td><?= money($p['revenue']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
</div></div>
<div class="col-md-6"><div class="card p-3"><h5>Low Stock</h5>
    <table class="table"><thead><tr><th>Product</th><th>Stock</th></tr></thead><tbody>
    <?php foreach ($data['low_stock'] as $p): ?>
    <tr><td><?= e($p['name']) ?></td><td><span class="text-danger"><?= $p['quantity'] ?></span></td></tr>
    <?php endforeach; ?>
    </tbody></table>
</div></div>
</div>
