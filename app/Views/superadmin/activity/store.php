<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Activity Log</h2>
        <small class="text-muted">
            Store: <?= e($store['name']) ?> · slug <code><?= e($store['slug']) ?></code>
            · status <code><?= e($store['status']) ?></code>
        </small>
    </div>
    <a href="/admin/stores/<?= (int)$store['id'] ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to store</a>
</div>

<?php
$filterUrl = '/admin/stores/' . (int)$store['id'] . '/activity';
include __DIR__ . '/_timeline.php';
?>
