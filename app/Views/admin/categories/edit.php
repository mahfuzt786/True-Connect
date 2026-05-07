<h2>Edit Category</h2>
<form method="POST" action="/categories/<?= $category['id'] ?>" enctype="multipart/form-data" class="card p-4">
    <?= csrf_field() ?>
    <div class="mb-3"><label>Name</label><input name="name" value="<?= e($category['name']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Parent</label><select name="parent_id" class="form-select"><option value="">— None —</option>
        <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $category['parent_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select></div>
    <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"><?= e($category['description']) ?></textarea></div>
    <div class="mb-3"><label>Image</label><input type="file" name="image" class="form-control"><?php if($category['image']): ?><img src="<?= e($category['image']) ?>" class="mt-2" style="max-height:80px;"><?php endif; ?></div>
    <div class="form-check mb-3"><input type="checkbox" name="is_active" value="1" <?= $category['is_active']?'checked':'' ?> id="ia" class="form-check-input"><label for="ia">Active</label></div>
    <button class="btn btn-primary">Update</button>
</form>
