<h2>Vendors</h2>
<div class="btn-group mb-3">
    <a href="/vendors" class="btn btn-outline-primary <?= !$status?'active':'' ?>">All</a>
    <a href="/vendors?status=pending" class="btn btn-outline-warning <?= $status==='pending'?'active':'' ?>">Pending</a>
    <a href="/vendors?status=active" class="btn btn-outline-success <?= $status==='active'?'active':'' ?>">Active</a>
    <a href="/vendors/payouts" class="btn btn-outline-info">Payouts</a>
</div>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Business</th><th>Owner</th><th>Email</th><th>Status</th><th>Sales</th><th>Balance</th><th></th></tr></thead><tbody>
<?php foreach ($vendors['data'] as $v): ?>
<tr><td><strong><?= e($v['business_name']) ?></strong></td>
<td><?= e($v['user_name']) ?></td><td><?= e($v['user_email']) ?></td>
<td><span class="badge bg-<?= $v['status']==='active'?'success':($v['status']==='pending'?'warning':'secondary') ?>"><?= e($v['status']) ?></span></td>
<td><?= $v['total_sales'] ?></td><td><?= money($v['balance']) ?></td>
<td><a href="/vendors/<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($vendors) ?></div>
