<h2 class="mb-3">Users</h2>
<form class="card p-3 mb-3"><div class="row g-2">
    <div class="col-md-7"><input name="search" value="<?= e($search) ?>" class="form-control" placeholder="Name or email"></div>
    <div class="col-md-3"><select name="role" class="form-select"><option value="">All Roles</option>
        <?php foreach (['super_admin','store_owner','vendor','customer'] as $r): ?>
            <option value="<?= $r ?>" <?= $role==$r?'selected':'' ?>><?= $r ?></option>
        <?php endforeach; ?>
    </select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</div></form>
<div class="card"><table class="table mb-0 align-middle">
<thead class="table-light"><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php
$statusBadge = [
    'active'   => 'success',
    'pending'  => 'warning',
    'inactive' => 'secondary',
    'banned'   => 'danger',
];
foreach ($users['data'] as $u):
    $isSelf       = (int)$u['id'] === (int)(auth()['id'] ?? 0);
    $isSuperAdmin = $u['role'] === 'super_admin';
    $cls          = $statusBadge[$u['status']] ?? 'secondary';
?>
<tr>
    <td><?= e($u['name']) ?></td>
    <td><?= e($u['email']) ?></td>
    <td><?= e($u['role']) ?></td>
    <td><span class="badge bg-<?= $cls ?>"><?= e($u['status']) ?></span></td>
    <td><?= timeAgo($u['created_at']) ?></td>
    <td class="text-end">
        <a href="/admin/users/<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
        <a href="/admin/users/<?= (int)$u['id'] ?>/activity" class="btn btn-sm btn-outline-info" title="Activity log">
            <i class="bi bi-clock-history"></i> Log
        </a>
        <?php if (!$isSuperAdmin && !$isSelf): ?>
            <?php if ($u['status'] === 'pending'): ?>
                <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/approve" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-success" title="Approve user"><i class="bi bi-check-lg"></i> Approve</button>
                </form>
                <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/reject" class="d-inline" onsubmit="return confirm('Reject this user? They will not be able to sign in.');">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger" title="Reject user"><i class="bi bi-x-lg"></i> Reject</button>
                </form>
            <?php elseif ($u['status'] === 'active'): ?>
                <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/ban" class="d-inline" onsubmit="return confirm('Ban this user? They will lose access immediately.');">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger" title="Ban user"><i class="bi bi-slash-circle"></i> Ban</button>
                </form>
            <?php elseif (in_array($u['status'], ['banned', 'inactive'], true)): ?>
                <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/unban" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-success" title="Reinstate user"><i class="bi bi-arrow-clockwise"></i> Reinstate</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($users) ?></div>
