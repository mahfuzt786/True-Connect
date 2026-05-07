<div class="d-flex justify-content-between mb-3"><h2>Plans</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlan">New Plan</button></div>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Name</th><th>Monthly</th><th>Yearly</th><th>Trial</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php foreach ($plans as $p): ?>
<tr>
    <td><?= e($p['name']) ?></td>
    <td><?= money($p['price_monthly']) ?></td>
    <td><?= money($p['price_yearly']) ?></td>
    <td><?= (int)$p['trial_days'] ?> days</td>
    <td><span class="badge bg-<?= $p['is_active']?'success':'secondary' ?>"><?= $p['is_active']?'Active':'Off' ?></span></td>
    <td class="text-end">
        <button class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal"
                data-bs-target="#editPlan-<?= (int)$p['id'] ?>">Edit</button>
        <button class="btn btn-sm btn-outline-danger"
                onclick="if(confirm('Delete?'))fetch('/admin/plans/<?= (int)$p['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>

<?php
// Render the feature checkbox grid + "add new" inline control.
$renderFeatureFields = function (string $idScope, array $selected) use ($allFeatures) {
    // Anything in $selected not present in $allFeatures gets included too (defensive).
    $features = $allFeatures;
    foreach ($selected as $s) {
        if (!in_array($s, $features, true)) $features[] = $s;
    }
    ?>
    <label class="form-label">Features</label>
    <div class="row g-1" data-features-grid="<?= e($idScope) ?>">
        <?php foreach ($features as $i => $feature): $cid = $idScope . '-feat-' . $i; ?>
            <div class="col-md-6 col-lg-4">
                <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           id="<?= e($cid) ?>"
                           name="features[]"
                           value="<?= e($feature) ?>"
                           <?= in_array($feature, $selected, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= e($cid) ?>"><?= e($feature) ?></label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="input-group input-group-sm mt-2" style="max-width: 420px;">
        <input type="text" class="form-control" placeholder="Add a new feature (e.g. Custom branding)"
               data-add-feature-input="<?= e($idScope) ?>">
        <button type="button" class="btn btn-outline-primary"
                data-add-feature-btn="<?= e($idScope) ?>">+ Add</button>
    </div>
    <?php
};
?>

<!-- New Plan Modal -->
<div class="modal fade" id="addPlan"><div class="modal-dialog modal-lg"><div class="modal-content">
<form method="POST" action="/admin/plans"><?= csrf_field() ?>
<div class="modal-header"><h5 class="modal-title">New Plan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="row g-2">
    <div class="col-12"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Monthly (₹)</label><input type="number" step="0.01" min="0" name="price_monthly" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Yearly (₹)</label><input type="number" step="0.01" min="0" name="price_yearly" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Trial Days</label><input type="number" min="0" name="trial_days" class="form-control" value="14" required></div>
    <div class="col-md-4"><label class="form-label">Products Limit</label><input type="number" min="0" name="products_limit" class="form-control" placeholder="empty = unlimited"></div>
    <div class="col-md-4"><label class="form-label">Vendors Limit</label><input type="number" min="0" name="vendors_limit" class="form-control" placeholder="empty = unlimited"></div>
    <div class="col-md-4"><label class="form-label">Storage Limit (MB)</label><input type="number" min="0" name="storage_limit_mb" class="form-control" placeholder="empty = unlimited"></div>
    <div class="col-12"><?php $renderFeatureFields('new', []); ?></div>
    <div class="col-12 mt-2">
        <div class="form-check"><input type="checkbox" name="marketplace_enabled" value="1" id="me-new" class="form-check-input"><label class="form-check-label" for="me-new">Marketplace Enabled</label></div>
        <div class="form-check"><input type="checkbox" name="custom_domain" value="1" id="cd-new" class="form-check-input"><label class="form-check-label" for="cd-new">Custom Domain</label></div>
        <div class="form-check"><input type="checkbox" name="api_access" value="1" id="api-new" class="form-check-input"><label class="form-check-label" for="api-new">API Access</label></div>
        <div class="form-check"><input type="checkbox" name="analytics" value="1" id="an-new" class="form-check-input"><label class="form-check-label" for="an-new">Advanced Analytics</label></div>
    </div>
</div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
</form></div></div></div>

<!-- Edit Plan Modals -->
<?php foreach ($plans as $p): ?>
<?php
    $selected = [];
    if (!empty($p['features'])) {
        $decoded = json_decode($p['features'], true);
        if (is_array($decoded)) $selected = array_values(array_filter(array_map(fn($v) => trim((string)$v), $decoded), fn($v) => $v !== ''));
    }
?>
<div class="modal fade" id="editPlan-<?= (int)$p['id'] ?>"><div class="modal-dialog modal-lg"><div class="modal-content">
<form method="POST" action="/admin/plans/<?= (int)$p['id'] ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">
<div class="modal-header"><h5 class="modal-title">Edit Plan — <?= e($p['name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="row g-2">
    <div class="col-12"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($p['name']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Monthly (₹)</label><input type="number" step="0.01" min="0" name="price_monthly" class="form-control" value="<?= e($p['price_monthly']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Yearly (₹)</label><input type="number" step="0.01" min="0" name="price_yearly" class="form-control" value="<?= e($p['price_yearly']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Trial Days</label><input type="number" min="0" name="trial_days" class="form-control" value="<?= (int)$p['trial_days'] ?>" required></div>
    <div class="col-md-4"><label class="form-label">Products Limit</label><input type="number" min="0" name="products_limit" class="form-control" value="<?= e($p['products_limit'] ?? '') ?>" placeholder="empty = unlimited"></div>
    <div class="col-md-4"><label class="form-label">Vendors Limit</label><input type="number" min="0" name="vendors_limit" class="form-control" value="<?= e($p['vendors_limit'] ?? '') ?>" placeholder="empty = unlimited"></div>
    <div class="col-md-4"><label class="form-label">Storage Limit (MB)</label><input type="number" min="0" name="storage_limit_mb" class="form-control" value="<?= e($p['storage_limit_mb'] ?? '') ?>" placeholder="empty = unlimited"></div>
    <div class="col-12"><?php $renderFeatureFields('e' . (int)$p['id'], $selected); ?></div>
    <div class="col-12 mt-2">
        <div class="form-check"><input type="checkbox" name="marketplace_enabled" value="1" id="me-<?= (int)$p['id'] ?>" class="form-check-input" <?= !empty($p['marketplace_enabled']) ? 'checked' : '' ?>><label class="form-check-label" for="me-<?= (int)$p['id'] ?>">Marketplace Enabled</label></div>
        <div class="form-check"><input type="checkbox" name="custom_domain" value="1" id="cd-<?= (int)$p['id'] ?>" class="form-check-input" <?= !empty($p['custom_domain']) ? 'checked' : '' ?>><label class="form-check-label" for="cd-<?= (int)$p['id'] ?>">Custom Domain</label></div>
        <div class="form-check"><input type="checkbox" name="api_access" value="1" id="api-<?= (int)$p['id'] ?>" class="form-check-input" <?= !empty($p['api_access']) ? 'checked' : '' ?>><label class="form-check-label" for="api-<?= (int)$p['id'] ?>">API Access</label></div>
        <div class="form-check"><input type="checkbox" name="analytics" value="1" id="an-<?= (int)$p['id'] ?>" class="form-check-input" <?= !empty($p['analytics']) ? 'checked' : '' ?>><label class="form-check-label" for="an-<?= (int)$p['id'] ?>">Advanced Analytics</label></div>
        <div class="form-check"><input type="checkbox" name="is_active" value="1" id="ia-<?= (int)$p['id'] ?>" class="form-check-input" <?= !empty($p['is_active']) ? 'checked' : '' ?>><label class="form-check-label" for="ia-<?= (int)$p['id'] ?>">Active</label></div>
    </div>
</div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div>
</form></div></div></div>
<?php endforeach; ?>

<script>
(function () {
    function escapeHtml(s) {
        return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    document.querySelectorAll('[data-add-feature-btn]').forEach(function (btn) {
        const scope = btn.getAttribute('data-add-feature-btn');
        const input = document.querySelector('[data-add-feature-input="' + CSS.escape(scope) + '"]');
        const grid  = document.querySelector('[data-features-grid="' + CSS.escape(scope) + '"]');
        function add() {
            const v = (input.value || '').trim();
            if (!v) return;
            // Avoid duplicates within this grid.
            const exists = Array.from(grid.querySelectorAll('input[name="features[]"]'))
                .some(c => c.value.toLowerCase() === v.toLowerCase());
            if (exists) {
                // Just check the existing one if found.
                grid.querySelectorAll('input[name="features[]"]').forEach(c => {
                    if (c.value.toLowerCase() === v.toLowerCase()) c.checked = true;
                });
                input.value = '';
                return;
            }
            const id = scope + '-feat-custom-' + Date.now();
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';
            col.innerHTML =
                '<div class="form-check">' +
                    '<input type="checkbox" class="form-check-input" id="' + id + '" name="features[]" value="' + escapeHtml(v) + '" checked>' +
                    '<label class="form-check-label" for="' + id + '">' + escapeHtml(v) + '</label>' +
                '</div>';
            grid.appendChild(col);
            input.value = '';
        }
        btn.addEventListener('click', add);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); add(); }
        });
    });
})();
</script>
