<h2>New Page</h2>
<form method="POST" action="/store/pages" class="card p-4">
    <?= csrf_field() ?>
    <div class="mb-3"><label>Title</label><input name="title" class="form-control" required></div>
    <div class="mb-3"><label>Content</label><textarea name="content" class="form-control" rows="10"></textarea></div>
    <div class="mb-3"><label>Meta Title</label><input name="meta_title" class="form-control"></div>
    <div class="mb-3"><label>Meta Description</label><textarea name="meta_description" class="form-control"></textarea></div>
    <button class="btn btn-primary">Save Page</button>
</form>
