<h2>Add Product</h2>
<form method="POST" action="/products" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card p-4 mb-3">
                <div class="mb-3"><label>Name *</label><input name="name" class="form-control" required></div>
                <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="6"></textarea></div>
                <div class="mb-3"><label>Short Description</label><textarea name="short_description" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="card p-4 mb-3">
                <h5>Images</h5>
                <input type="file" name="images[]" multiple class="form-control" accept="image/*">
            </div>
            <div class="card p-4 mb-3">
                <h5>Pricing & Inventory</h5>
                <div class="row g-3">
                    <div class="col-md-4"><label>Price *</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                    <div class="col-md-4"><label>Compare Price</label><input type="number" step="0.01" name="compare_price" class="form-control"></div>
                    <div class="col-md-4"><label>Cost Price</label><input type="number" step="0.01" name="cost_price" class="form-control"></div>
                    <div class="col-md-4"><label>SKU</label><input name="sku" class="form-control"></div>
                    <div class="col-md-4"><label>Quantity</label><input type="number" name="quantity" class="form-control" value="0"></div>
                    <div class="col-md-4"><label>Weight (kg)</label><input type="number" step="0.01" name="weight" class="form-control"></div>
                </div>
                <div class="mt-3">
                    <div class="form-check"><input type="checkbox" name="track_inventory" value="1" checked class="form-check-input" id="ti"><label for="ti">Track inventory</label></div>
                    <div class="form-check"><input type="checkbox" name="featured" value="1" class="form-check-input" id="ft"><label for="ft">Featured product</label></div>
                </div>
            </div>
            <div class="card p-4 mb-3">
                <h5>SEO</h5>
                <div class="mb-2"><label>Meta Title</label><input name="meta_title" class="form-control"></div>
                <div class="mb-2"><label>Meta Description</label><textarea name="meta_description" class="form-control"></textarea></div>
                <div><label>Tags (comma separated)</label><input name="tags" class="form-control"></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 mb-3">
                <label>Status *</label>
                <select name="status" class="form-select"><option value="draft">Draft</option><option value="active" selected>Active</option><option value="inactive">Inactive</option></select>
            </div>
            <div class="card p-4 mb-3">
                <label>Type *</label>
                <select name="type" class="form-select"><option value="simple">Simple</option><option value="variable">Variable</option><option value="digital">Digital</option><option value="service">Service</option></select>
            </div>
            <div class="card p-4 mb-3">
                <label>Category</label>
                <select name="category_id" class="form-select"><option value="">— None —</option>
                    <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?php if ($vendors): ?>
            <div class="card p-4 mb-3">
                <label>Vendor</label>
                <select name="vendor_id" class="form-select"><option value="">— None —</option>
                    <?php foreach ($vendors as $v): ?><option value="<?= $v['id'] ?>"><?= e($v['business_name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary w-100 btn-lg">Save Product</button>
        </div>
    </div>
</form>
