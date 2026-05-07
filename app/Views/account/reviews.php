<h2>My Reviews</h2>
<div class="card"><table class="table mb-0">
<thead><tr><th>Product</th><th>Rating</th><th>Review</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($reviews as $r): ?>
<tr><td><?= e($r['product_name']) ?></td><td><?= str_repeat('★',$r['rating']) ?></td>
<td><?= e(truncate($r['body'],80)) ?></td><td><?= e($r['status']) ?></td>
<td><?= timeAgo($r['created_at']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
