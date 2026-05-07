<h2>Reviews & Ratings</h2>
<div class="btn-group mb-3">
    <a href="/reviews" class="btn btn-outline-primary <?= !$status?'active':'' ?>">All</a>
    <a href="/reviews?status=pending" class="btn btn-outline-warning <?= $status==='pending'?'active':'' ?>">Pending</a>
    <a href="/reviews?status=approved" class="btn btn-outline-success <?= $status==='approved'?'active':'' ?>">Approved</a>
    <a href="/reviews?status=rejected" class="btn btn-outline-danger <?= $status==='rejected'?'active':'' ?>">Rejected</a>
</div>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Product</th><th>Rating</th><th>Customer</th><th>Review</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($reviews['data'] as $r): ?>
<tr><td><?= e($r['product_name']) ?></td>
<td><?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5-$r['rating']) ?></td>
<td><?= e($r['user_name']) ?></td>
<td><?= e(truncate($r['body'], 80)) ?></td>
<td><span class="badge bg-<?= $r['status']==='approved'?'success':($r['status']==='pending'?'warning':'danger') ?>"><?= e($r['status']) ?></span></td>
<td>
    <?php if ($r['status']==='pending'): ?>
        <form method="POST" action="/reviews/<?= $r['id'] ?>/approve" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve</button></form>
        <form method="POST" action="/reviews/<?= $r['id'] ?>/reject" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-warning">Reject</button></form>
    <?php endif; ?>
    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete?'))fetch('/reviews/<?= $r['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button>
</td></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3"><?= paginate($reviews) ?></div>
