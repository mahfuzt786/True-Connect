<div class="d-flex justify-content-between mb-3"><h2>Pages</h2><a href="/store/pages/create" class="btn btn-primary">New Page</a></div>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody>
<?php foreach ($pages as $p): ?>
<tr><td><?= e($p['title']) ?></td><td><?= e($p['slug']) ?></td>
<td><?= $p['is_active']?'Active':'Inactive' ?></td><td><?= timeAgo($p['updated_at']) ?></td>
<td><a href="/store/pages/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
