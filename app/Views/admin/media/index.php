<h2>Media Library</h2>
<form id="uploadForm" class="card p-3 mb-3"><?= csrf_field() ?>
    <input type="file" name="file" class="form-control" onchange="upload(this)" multiple accept="image/*,application/pdf">
</form>
<div class="row g-3" id="grid">
<?php foreach ($media['data'] as $m): ?>
<div class="col-md-2">
    <div class="card p-2 text-center">
        <?php if (str_starts_with($m['mime_type'],'image/')): ?>
            <img src="<?= e($m['url']) ?>" class="img-fluid rounded">
        <?php else: ?>
            <i class="bi bi-file-earmark" style="font-size:48px;"></i>
        <?php endif; ?>
        <small class="d-block text-truncate mt-1"><?= e($m['name']) ?></small>
        <button class="btn btn-sm btn-outline-danger mt-1" onclick="if(confirm('Delete?'))fetch('/store/media/<?= $m['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button>
    </div>
</div>
<?php endforeach; ?>
</div>
<div class="mt-3"><?= paginate($media) ?></div>
<script>
function upload(input) {
    const fd = new FormData();
    fd.append('file', input.files[0]);
    fd.append('_csrf_token', document.querySelector('meta[name=csrf-token]').content);
    fetch('/store/media/upload', { method: 'POST', body: fd })
        .then(r => r.json()).then(() => location.reload());
}
</script>
