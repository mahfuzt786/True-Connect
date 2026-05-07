<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">All Stores</h2>
    <a href="/admin/stores/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create Store</a>
</div>
<form class="card p-3 mb-3"><div class="row g-2">
    <div class="col-md-9"><input name="search" value="<?= e($search) ?>" class="form-control" placeholder="Search by store name or owner email"></div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Search</button></div>
</div></form>
<?php if (empty($stores['data'])): ?>
    <div class="card p-5 text-center">
        <i class="bi bi-shop text-muted" style="font-size: 48px;"></i>
        <h5 class="mt-3">No stores yet</h5>
        <p class="text-muted">Create the first store and assign a separate store-admin user to manage it.</p>
        <div><a href="/admin/stores/create" class="btn btn-primary">Create Your First Store</a></div>
    </div>
<?php else: ?>
<?php
$storeBadge = [
    'active'    => 'success',
    'pending'   => 'warning',
    'inactive'  => 'secondary',
    'suspended' => 'danger',
];
?>
<div class="card"><table class="table mb-0 align-middle">
<thead class="table-light"><tr><th>Store</th><th>Owner</th><th>Plan</th><th>Status</th><th>Orders</th><th>Products</th><th>Created</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php foreach ($stores['data'] as $s):
    $cls = $storeBadge[$s['status']] ?? 'secondary';
?>
<tr>
    <td><a href="/admin/stores/<?= (int)$s['id'] ?>"><?= e($s['name']) ?></a></td>
    <td><?= e($s['owner_email']) ?></td>
    <td><?= e($s['plan_name'] ?? '—') ?></td>
    <td><span class="badge bg-<?= $cls ?>"><?= e($s['status']) ?></span></td>
    <td><?= (int)$s['order_count'] ?></td>
    <td><?= (int)$s['product_count'] ?></td>
    <td><?= timeAgo($s['created_at']) ?></td>
    <td class="text-end">
        <a href="/admin/stores/<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
        <a href="/admin/stores/<?= (int)$s['id'] ?>/activity" class="btn btn-sm btn-outline-info" title="Activity log">
            <i class="bi bi-clock-history"></i> Log
        </a>
        <?php if ($s['status'] === 'pending'): ?>
            <form method="POST" action="/admin/stores/<?= (int)$s['id'] ?>/approve" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-success" title="Approve store"><i class="bi bi-check-lg"></i> Approve</button>
            </form>
            <form method="POST" action="/admin/stores/<?= (int)$s['id'] ?>/reject" class="d-inline" onsubmit="return confirm('Reject this store?');">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger" title="Reject store"><i class="bi bi-x-lg"></i> Reject</button>
            </form>
        <?php elseif ($s['status'] === 'active'): ?>
            <form method="POST" action="/admin/stores/<?= (int)$s['id'] ?>/impersonate" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-primary" title="Switch into this store's admin context">
                    <i class="bi bi-box-arrow-in-right"></i> Manage
                </button>
            </form>
            <form method="POST" action="/admin/stores/<?= (int)$s['id'] ?>/suspend" class="d-inline" onsubmit="return confirm('Suspend this store? Owner and customers will lose access until you re-activate.');">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-warning" title="Suspend store"><i class="bi bi-pause-circle"></i> Suspend</button>
            </form>
        <?php elseif (in_array($s['status'], ['suspended', 'inactive'], true)): ?>
            <form method="POST" action="/admin/stores/<?= (int)$s['id'] ?>/activate" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-success" title="Reactivate store"><i class="bi bi-play-circle"></i> Activate</button>
            </form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($stores) ?></div>
<?php endif; ?>
