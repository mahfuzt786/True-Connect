<?php
/** @var array $logs   Paginate result */
/** @var array $actions */
/** @var string $action */
/** @var string $filterUrl Base URL for filter form */
$rows = $logs['data'] ?? [];

$actionMeta = [
    'page.view'         => ['icon' => 'bi-eye',           'tone' => 'secondary', 'label' => 'Page view'],
    'auth.login'        => ['icon' => 'bi-box-arrow-in-right', 'tone' => 'success', 'label' => 'Logged in'],
    'auth.logout'       => ['icon' => 'bi-box-arrow-left',     'tone' => 'secondary', 'label' => 'Logged out'],
    'auth.register'     => ['icon' => 'bi-person-plus',   'tone' => 'info',      'label' => 'Account created'],
    'product.create'    => ['icon' => 'bi-box-seam',      'tone' => 'success',   'label' => 'Created product'],
    'product.update'    => ['icon' => 'bi-pencil',        'tone' => 'warning',   'label' => 'Updated product'],
    'product.delete'    => ['icon' => 'bi-trash',         'tone' => 'danger',    'label' => 'Deleted product'],
    'order.create'      => ['icon' => 'bi-receipt',       'tone' => 'success',   'label' => 'Placed order'],
    'order.status_update' => ['icon' => 'bi-arrow-clockwise', 'tone' => 'info',  'label' => 'Order status changed'],
    'store.create'      => ['icon' => 'bi-shop',          'tone' => 'success',   'label' => 'Store created'],
    'store.suspend'     => ['icon' => 'bi-pause-circle',  'tone' => 'warning',   'label' => 'Store suspended'],
    'store.activate'    => ['icon' => 'bi-play-circle',   'tone' => 'success',   'label' => 'Store activated'],
];
function _meta(string $a): array {
    global $actionMeta;
    return $actionMeta[$a] ?? ['icon' => 'bi-circle-fill', 'tone' => 'secondary', 'label' => str_replace(['.', '_'], [' › ', ' '], $a)];
}
?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <div class="d-flex gap-2 flex-grow-1">
        <select id="activity-filter" class="form-select" style="max-width: 280px;"
                data-base="<?= e($filterUrl) ?>">
            <option value="">All activity (<?= number_format(array_sum(array_column($actions, 'c'))) ?>)</option>
            <?php foreach ($actions as $a): ?>
                <option value="<?= e($a['action']) ?>" <?= $action === $a['action'] ? 'selected' : '' ?>>
                    <?= e(_meta($a['action'])['label']) ?> (<?= number_format($a['c']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($action): ?><a href="<?= e($filterUrl) ?>" class="btn btn-light">Clear</a><?php endif; ?>
    </div>
    <span class="text-muted small ms-auto align-self-center">
        Showing <?= number_format(count($rows)) ?> of <?= number_format($logs['total'] ?? 0) ?>
    </span>
</div>

<script>
(function () {
    const sel = document.getElementById('activity-filter');
    if (!sel) return;
    sel.addEventListener('change', function () {
        const base = this.dataset.base;
        const v    = this.value;
        // Navigate without an empty query string when "All activity" is picked.
        window.location.href = v ? base + '?action=' + encodeURIComponent(v) : base;
    });
})();
</script>

<?php if (empty($rows)): ?>
    <div class="card p-5 text-center">
        <i class="bi bi-clock-history text-muted" style="font-size: 48px;"></i>
        <h5 class="mt-3">No activity yet</h5>
        <p class="text-muted mb-0">Logged-in actions, page views and login events will appear here.</p>
    </div>
<?php else: ?>
    <div class="card">
        <div class="list-group list-group-flush activity-list">
            <?php foreach ($rows as $r):
                $m = _meta($r['action']);
                $meta = $r['metadata'] ? json_decode($r['metadata'], true) : null;
            ?>
                <div class="list-group-item d-flex gap-3 py-3">
                    <div class="rounded-circle bg-<?= e($m['tone']) ?>-subtle text-<?= e($m['tone']) ?> d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;">
                        <i class="bi <?= e($m['icon']) ?>"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                            <div>
                                <strong><?= e($m['label']) ?></strong>
                                <?php if (!empty($r['description'])): ?>
                                    <span class="text-muted">— <?= e($r['description']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($r['u_name']) || !empty($r['u_email'])): ?>
                                    <small class="text-muted d-block">by <?= e($r['u_name'] ?? '') ?> &lt;<?= e($r['u_email'] ?? '') ?>&gt;</small>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted text-nowrap" title="<?= e($r['created_at']) ?>"><?= timeAgo($r['created_at']) ?></small>
                        </div>
                        <?php if (!empty($r['method']) || !empty($r['path'])): ?>
                            <small class="text-muted font-monospace text-truncate d-block">
                                <span class="badge bg-light text-dark border me-1"><?= e($r['method'] ?: 'GET') ?></span>
                                <?= e($r['path'] ?? '') ?>
                            </small>
                        <?php endif; ?>
                        <small class="text-muted">
                            <?php if (!empty($r['ip_address'])): ?><i class="bi bi-globe me-1"></i><?= e($r['ip_address']) ?><?php endif; ?>
                            <?php if (!empty($r['user_agent'])): ?>
                                <span class="ms-2" title="<?= e($r['user_agent']) ?>"><i class="bi bi-laptop me-1"></i><?= e(mb_strimwidth($r['user_agent'], 0, 80, '…')) ?></span>
                            <?php endif; ?>
                        </small>
                        <?php if ($meta): ?>
                            <details class="small mt-1">
                                <summary class="text-muted">metadata</summary>
                                <pre class="bg-light p-2 mt-1 mb-0 small"><?= e(json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                            </details>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="mt-3"><?= paginate($logs) ?></div>
<?php endif; ?>

<style>
    .activity-list .list-group-item:hover { background: rgba(0,0,0,.02); }
    .min-width-0 { min-width: 0; }
</style>
