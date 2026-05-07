<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Activity Log</h2>
        <small class="text-muted">
            <?= e($user['name']) ?> &lt;<?= e($user['email']) ?>&gt;
            · role <code><?= e($user['role']) ?></code>
            · status <code><?= e($user['status']) ?></code>
        </small>
    </div>
    <a href="/admin/users/<?= (int)$user['id'] ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to user</a>
</div>

<?php
$filterUrl = '/admin/users/' . (int)$user['id'] . '/activity';
include __DIR__ . '/_timeline.php';
?>
