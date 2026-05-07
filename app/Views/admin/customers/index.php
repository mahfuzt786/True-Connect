<div class="d-flex justify-content-between mb-3"><h2>Customers</h2>
    <a href="/customers/export" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export</a></div>
<form class="card p-3 mb-3"><div class="row g-2">
    <div class="col-md-9"><input name="search" value="<?= e($search) ?>" class="form-control" placeholder="Name or email..."></div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Search</button></div>
</div></form>
<div class="card"><table class="table mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Orders</th><th>Total Spent</th><th>Joined</th><th></th></tr></thead><tbody>
    <?php foreach ($customers['data'] as $c): ?>
        <tr><td><?= e($c['name']) ?></td><td><?= e($c['email']) ?></td>
            <td><?= $c['order_count'] ?></td><td><?= money($c['total_spent']) ?></td>
            <td><?= timeAgo($c['created_at']) ?></td>
            <td><a href="/customers/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td></tr>
    <?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($customers) ?></div>
