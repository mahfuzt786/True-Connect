<div class="d-flex justify-content-between mb-3"><h2>Coupons</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCoupon"><i class="bi bi-plus"></i> New Coupon</button></div>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Code</th><th>Type</th><th>Value</th><th>Used</th><th>Expires</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($coupons['data'] as $c): ?>
<tr><td><strong><?= e($c['code']) ?></strong></td><td><?= e($c['type']) ?></td>
<td><?= $c['type']==='percentage' ? $c['value'].'%' : money($c['value']) ?></td>
<td><?= $c['usage_count'] ?> / <?= $c['usage_limit'] ?? '∞' ?></td>
<td><?= $c['expires_at'] ? formatDate($c['expires_at']) : '—' ?></td>
<td><span class="badge bg-<?= $c['is_active']?'success':'secondary' ?>"><?= $c['is_active']?'Active':'Off' ?></span></td>
<td><a href="/coupons/<?= $c['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($coupons) ?></div>

<div class="modal fade" id="addCoupon"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="/coupons">
<?= csrf_field() ?>
<div class="modal-header"><h5>Create Coupon</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <div class="mb-2"><label>Code</label><input name="code" class="form-control" required placeholder="SAVE20"></div>
    <div class="row g-2 mb-2">
        <div class="col-6"><label>Type</label><select name="type" class="form-select"><option value="percentage">Percentage</option><option value="fixed">Fixed</option><option value="free_shipping">Free Shipping</option></select></div>
        <div class="col-6"><label>Value</label><input type="number" step="0.01" name="value" class="form-control" required></div>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-6"><label>Min Order</label><input type="number" step="0.01" name="min_order_amount" class="form-control"></div>
        <div class="col-6"><label>Usage Limit</label><input type="number" name="usage_limit" class="form-control"></div>
    </div>
    <div class="row g-2">
        <div class="col-6"><label>Starts</label><input type="datetime-local" name="starts_at" class="form-control"></div>
        <div class="col-6"><label>Expires</label><input type="datetime-local" name="expires_at" class="form-control"></div>
    </div>
</div>
<div class="modal-footer"><button class="btn btn-primary">Create</button></div>
</form></div></div></div>
