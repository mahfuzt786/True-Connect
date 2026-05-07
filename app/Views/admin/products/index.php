<div class="d-flex justify-content-between mb-3">
    <h2>Products</h2>
    <div>
        <a href="/products/import" class="btn btn-outline-secondary"><i class="bi bi-upload"></i> Import</a>
        <a href="/products/export" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export</a>
        <a href="/products/create" class="btn btn-primary"><i class="bi bi-plus"></i> Add Product</a>
    </div>
</div>
<form class="card p-3 mb-3"><div class="row g-2">
    <div class="col-md-4"><input name="search" value="<?= e($search) ?>" class="form-control" placeholder="Search products..."></div>
    <div class="col-md-3"><select name="category" class="form-select"><option value="">All Categories</option>
        <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $category==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-2"><select name="status" class="form-select"><option value="">All Status</option>
        <?php foreach (['active','inactive','draft','archived'] as $s): ?><option <?= $status==$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
</div></form>
<form method="POST" action="/products/bulk">
    <?= csrf_field() ?>
    <div class="card">
        <div class="table-responsive"><table class="table mb-0">
            <thead class="table-light">
                <tr><th><input type="checkbox" id="selectAll"></th><th>Image</th><th>Name</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($products['data'] as $p): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $p['id'] ?>"></td>
                    <td><?php if($p['primary_image']): ?><img src="<?= e($p['primary_image']) ?>" width="50" height="50" class="rounded"><?php else: ?><div class="bg-light rounded" style="width:50px;height:50px;"></div><?php endif; ?></td>
                    <td><a href="/products/<?= $p['id'] ?>/edit"><?= e($p['name']) ?></a><?php if($p['featured']): ?> <span class="badge bg-warning">Featured</span><?php endif; ?></td>
                    <td><?= e($p['sku']) ?></td>
                    <td><?= e($p['category_name'] ?? '—') ?></td>
                    <td><?= money($p['price']) ?></td>
                    <td><?= $p['quantity'] ?></td>
                    <td><span class="badge bg-<?= $p['status']==='active'?'success':($p['status']==='draft'?'warning':'secondary') ?>"><?= e($p['status']) ?></span></td>
                    <td>
                        <a href="/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete?')) deleteRow(<?= $p['id'] ?>)"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <div class="card-footer d-flex justify-content-between">
            <select name="action" class="form-select w-auto">
                <option value="">Bulk Action</option><option value="activate">Activate</option><option value="deactivate">Deactivate</option><option value="feature">Feature</option><option value="delete">Delete</option>
            </select>
            <button class="btn btn-primary">Apply</button>
        </div>
    </div>
</form>
<div class="mt-3"><?= paginate($products) ?></div>
<script>
document.getElementById('selectAll').addEventListener('change', e => {
    document.querySelectorAll('input[name="ids[]"]').forEach(c => c.checked = e.target.checked);
});
function deleteRow(id) { fetch('/products/' + id, { method: 'DELETE', headers: { 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content } }).then(() => location.reload()); }
</script>
