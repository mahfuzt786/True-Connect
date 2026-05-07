<h2>Edit Page</h2>
<form method="POST" action="/store/pages/<?= $page['id'] ?>" class="card p-4">
    <?= csrf_field() ?>
    <div class="mb-3"><label>Title</label><input name="title" value="<?= e($page['title']) ?>" class="form-control"></div>
    <div class="mb-3"><label>Content</label><textarea name="content" class="form-control" rows="10"><?= e($page['content']) ?></textarea></div>
    <div class="mb-3"><label>Meta Title</label><input name="meta_title" value="<?= e($page['meta_title']) ?>" class="form-control"></div>
    <div class="mb-3"><label>Meta Description</label><textarea name="meta_description" class="form-control"><?= e($page['meta_description']) ?></textarea></div>
    <div class="form-check mb-3"><input type="checkbox" name="is_active" value="1" <?= $page['is_active']?'checked':'' ?> class="form-check-input" id="ia"><label for="ia">Active</label></div>
    <button class="btn btn-primary">Update</button>
</form>
