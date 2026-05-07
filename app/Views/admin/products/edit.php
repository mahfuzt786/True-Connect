<h2>Edit Product</h2>
<form method="POST" action="/products/<?= $product['id'] ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card p-4 mb-3">
                <div class="mb-3"><label>Name *</label><input name="name" value="<?= e($product['name']) ?>" class="form-control" required></div>
                <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="6"><?= e($product['description']) ?></textarea></div>
                <div class="mb-3"><label>Short Description</label><textarea name="short_description" class="form-control" rows="2"><?= e($product['short_description']) ?></textarea></div>
            </div>
            <div class="card p-4 mb-3">
                <h5>Images</h5>
                <div class="row g-2 mb-3">
                    <?php foreach ($images as $img): ?>
                        <div class="col-3 position-relative">
                            <img src="<?= e($img['image']) ?>" class="img-fluid rounded">
                            <?php if ($img['is_primary']): ?><span class="badge bg-success position-absolute top-0 start-0 m-1">Primary</span><?php endif; ?>
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="deleteImage(<?= $product['id'] ?>,<?= $img['id'] ?>,this)"><i class="bi bi-x"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="file" name="images[]" multiple class="form-control" accept="image/*">
            </div>
            <div class="card p-4 mb-3">
                <h5>Pricing & Inventory</h5>
                <div class="row g-3">
                    <div class="col-md-4"><label>Price</label><input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" class="form-control"></div>
                    <div class="col-md-4"><label>Compare Price</label><input type="number" step="0.01" name="compare_price" value="<?= $product['compare_price'] ?>" class="form-control"></div>
                    <div class="col-md-4"><label>Cost</label><input type="number" step="0.01" name="cost_price" value="<?= $product['cost_price'] ?>" class="form-control"></div>
                    <div class="col-md-4"><label>SKU</label><input name="sku" value="<?= e($product['sku']) ?>" class="form-control"></div>
                    <div class="col-md-4"><label>Quantity</label><input type="number" name="quantity" value="<?= $product['quantity'] ?>" class="form-control"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 mb-3">
                <label>Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['draft','active','inactive','archived'] as $s): ?>
                        <option value="<?= $s ?>" <?= $product['status']===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="card p-4 mb-3">
                <label>Type</label>
                <select name="type" class="form-select">
                    <?php foreach (['simple','variable','digital','service'] as $t): ?>
                        <option value="<?= $t ?>" <?= $product['type']===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="card p-4 mb-3">
                <label>Category</label>
                <select name="category_id" class="form-select"><option value="">— None —</option>
                    <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $product['category_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg">Update</button>
        </div>
    </div>
</form>
<script>
function deleteImage(pid, iid, btn) {
    if (!confirm('Delete?')) return;
    fetch('/products/'+pid+'/images/'+iid, { method: 'DELETE', headers: { 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content } })
        .then(() => btn.parentElement.remove());
}
</script>
