<div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
    <div>
        <h2 class="mb-1"><?= e($store['name']) ?></h2>
        <p class="text-muted mb-0">Owner: <?= e($store['owner_name']) ?> &lt;<?= e($store['owner_email']) ?>&gt;</p>
    </div>
    <a href="/admin/stores/<?= (int)$store['id'] ?>/activity" class="btn btn-outline-info">
        <i class="bi bi-clock-history me-1"></i>Activity Log
    </a>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Orders</div><h4><?= $stats['orders']['c'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Revenue</div><h4><?= money($stats['orders']['total']) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Products</div><h4><?= $stats['products'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Vendors</div><h4><?= $stats['vendors'] ?></h4></div></div>
</div>
<?php if ($subscription): ?>
<div class="card p-3 mb-3"><h5>Subscription</h5>
<p>Plan: <strong><?= e($subscription['plan_name']) ?></strong> | Status: <span class="badge bg-info"><?= e($subscription['status']) ?></span></p>
</div>
<?php endif; ?>
<div class="d-flex gap-2">
    <?php if ($store['status']==='active'): ?>
        <form method="POST" action="/admin/stores/<?= $store['id'] ?>/suspend"><?= csrf_field() ?><button class="btn btn-warning">Suspend</button></form>
    <?php else: ?>
        <form method="POST" action="/admin/stores/<?= $store['id'] ?>/activate"><?= csrf_field() ?><button class="btn btn-success">Activate</button></form>
    <?php endif; ?>
    <form method="POST" action="/admin/stores/<?= $store['id'] ?>" onsubmit="return confirm('Permanently delete this store?')"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button class="btn btn-danger">Delete</button></form>
</div>
