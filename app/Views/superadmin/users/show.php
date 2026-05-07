<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h2 class="mb-1"><?= e($user['name']) ?></h2>
        <p class="text-muted mb-0">
            Email: <?= e($user['email']) ?> | Role: <code><?= e($user['role']) ?></code> | Status:
            <span class="badge bg-<?= ['active'=>'success','pending'=>'warning','banned'=>'danger','inactive'=>'secondary'][$user['status']] ?? 'secondary' ?>"><?= e($user['status']) ?></span>
        </p>
    </div>
    <a href="/admin/users/<?= (int)$user['id'] ?>/activity" class="btn btn-outline-info">
        <i class="bi bi-clock-history me-1"></i>Activity Log
    </a>
</div>

<div class="card mb-3 p-3">
    <h5>Stores</h5>
    <?php if (empty($stores)): ?>
        <p class="text-muted small mb-0">This user does not own any stores.</p>
    <?php else: ?>
        <?php foreach ($stores as $s): ?>
            <div><a href="/admin/stores/<?= (int)$s['id'] ?>"><?= e($s['name']) ?></a> — <?= e($s['type']) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card p-3">
    <h5>Recent Orders (as customer)</h5>
    <table class="table">
        <thead><tr><th>Order#</th><th>Total</th><th>Date</th></tr></thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr><td>#<?= e($o['order_number']) ?></td><td><?= money($o['total']) ?></td><td><?= formatDate($o['created_at']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$isSelf       = (int)$user['id'] === (int)(auth()['id'] ?? 0);
$isSuperAdmin = $user['role'] === 'super_admin';
if (!$isSuperAdmin && !$isSelf):
?>
<div class="d-flex gap-2 mt-3">
    <?php if ($user['status'] === 'pending'): ?>
        <form method="POST" action="/admin/users/<?= (int)$user['id'] ?>/approve"><?= csrf_field() ?>
            <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
        </form>
        <form method="POST" action="/admin/users/<?= (int)$user['id'] ?>/reject" onsubmit="return confirm('Reject this user?');"><?= csrf_field() ?>
            <button class="btn btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
        </form>
    <?php elseif ($user['status'] === 'active'): ?>
        <form method="POST" action="/admin/users/<?= (int)$user['id'] ?>/ban" onsubmit="return confirm('Ban this user?');"><?= csrf_field() ?>
            <button class="btn btn-outline-danger"><i class="bi bi-slash-circle me-1"></i>Ban User</button>
        </form>
    <?php elseif (in_array($user['status'], ['banned','inactive'], true)): ?>
        <form method="POST" action="/admin/users/<?= (int)$user['id'] ?>/unban"><?= csrf_field() ?>
            <button class="btn btn-success"><i class="bi bi-arrow-clockwise me-1"></i>Reinstate</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>
