<div class="d-flex justify-content-between mb-3">
    <h2>Orders</h2>
    <a href="/orders/export?from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export CSV</a>
</div>
<form class="card p-3 mb-3"><div class="row g-2">
    <div class="col-md-3"><input name="search" value="<?= e($search) ?>" class="form-control" placeholder="Order# or customer..."></div>
    <div class="col-md-2"><select name="status" class="form-select"><option value="">All Status</option>
        <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled','refunded'] as $s): ?><option <?= $status==$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-3"><input type="date" name="from" value="<?= e($from) ?>" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="to" value="<?= e($to) ?>" class="form-control"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">Go</button></div>
</div></form>
<div class="card">
    <div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Order#</th><th>Date</th><th>Customer</th><th>Status</th><th>Payment</th><th>Total</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders['data'] as $o): ?>
            <tr>
                <td><a href="/orders/<?= $o['id'] ?>">#<?= e($o['order_number']) ?></a></td>
                <td><?= formatDate($o['created_at']) ?></td>
                <td><?= e($o['customer_name'] ?? 'Guest') ?><br><small class="text-muted"><?= e($o['customer_email'] ?? '') ?></small></td>
                <td><span class="badge bg-info"><?= e($o['status']) ?></span></td>
                <td><span class="badge bg-<?= $o['payment_status']==='paid'?'success':($o['payment_status']==='pending'?'warning':'danger') ?>"><?= e($o['payment_status']) ?></span></td>
                <td><?= money($o['total'], $o['currency']) ?></td>
                <td><a href="/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<div class="mt-3"><?= paginate($orders) ?></div>
