<div class="d-flex justify-content-between mb-3"><h2>Categories</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCat"><i class="bi bi-plus"></i> Add Category</button></div>
<div class="card"><table class="table mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Parent</th><th>Products</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($categories as $c): ?>
        <tr><td><?= e($c['name']) ?></td><td><?= e($c['parent_name'] ?? '—') ?></td>
            <td><?= $c['product_count'] ?></td>
            <td><span class="badge bg-<?= $c['is_active']?'success':'secondary' ?>"><?= $c['is_active']?'Active':'Inactive' ?></span></td>
            <td><a href="/categories/<?= $c['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete?'))fetch('/categories/<?= $c['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button></td>
        </tr>
    <?php endforeach; ?>
</tbody></table></div>

<div class="modal fade" id="addCat"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="/categories" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-header"><h5>Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label>Name</label><input name="name" class="form-control" required></div>
            <div class="mb-2"><label>Parent</label><select name="parent_id" class="form-select"><option value="">None</option>
                <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select></div>
            <div class="mb-2"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
            <div><label>Image</label><input type="file" name="image" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
