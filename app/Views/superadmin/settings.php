<h2 class="mb-4">Platform Settings</h2>
<form method="POST" action="/admin/settings"><?= csrf_field() ?>

    <div class="card p-4 mb-3">
        <h5 class="mb-3">General</h5>
        <?php foreach ($settings as $group => $items): ?>
            <h6 class="text-muted text-uppercase small mt-3"><?= e(ucfirst($group)) ?></h6>
            <?php foreach ($items as $key => $s): ?>
                <div class="mb-3">
                    <label class="form-label"><?= e(ucwords(str_replace('_',' ', $key))) ?></label>
                    <?php if ($s['type'] === 'boolean'): ?>
                        <select name="settings[<?= e($key) ?>]" class="form-select">
                            <option value="1" <?= $s['value'] ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= !$s['value'] ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    <?php else: ?>
                        <input name="settings[<?= e($key) ?>]" value="<?= e($s['value']) ?>" class="form-control">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Social Media -->
    <div class="card p-4 mb-3">
        <h5 class="mb-1">Social Media</h5>
        <p class="text-muted small mb-3">Links shown in the public footer. Leave blank to hide. Must start with <code>https://</code>.</p>
        <div class="row g-3">
            <?php foreach ($platforms as $key => $meta): ?>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi <?= e($meta['icon']) ?> me-1"></i><?= e($meta['label']) ?></label>
                    <input type="url"
                           name="social_links[<?= e($key) ?>]"
                           value="<?= e($socialLinks[$key] ?? '') ?>"
                           placeholder="https://…"
                           pattern="https?://.+"
                           class="form-control">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button class="btn btn-primary">Save Settings</button>
</form>
