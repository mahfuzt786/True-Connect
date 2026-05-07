<h2>Notifications</h2>
<button class="btn btn-sm btn-link" onclick="fetch('/dashboard/notifications/read-all',{method:'POST',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Mark all read</button>
<div class="card mt-2">
<ul class="list-group list-group-flush">
<?php foreach ($notifications as $n): $data = json_decode($n['data'], true) ?: []; ?>
<li class="list-group-item <?= $n['read_at']?'':'bg-light' ?>">
    <div class="d-flex justify-content-between">
        <div>
            <strong><?= e($data['title'] ?? $n['type']) ?></strong>
            <p class="mb-1"><?= e($data['message'] ?? '') ?></p>
            <small class="text-muted"><?= timeAgo($n['created_at']) ?></small>
        </div>
        <?php if (!$n['read_at']): ?>
            <button class="btn btn-sm btn-outline-secondary" onclick="fetch('/dashboard/notifications/<?= $n['id'] ?>/read',{method:'POST',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Mark read</button>
        <?php endif; ?>
    </div>
</li>
<?php endforeach; ?>
<?php if (empty($notifications)): ?><li class="list-group-item text-center text-muted">No notifications.</li><?php endif; ?>
</ul>
</div>
