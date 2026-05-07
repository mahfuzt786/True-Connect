<h2>Theme Customization</h2>
<div class="row g-3 mb-4">
<?php foreach ($themes as $t): ?>
<div class="col-md-4">
    <div class="card <?= $t['slug']===$current?'border-primary border-2':'' ?>">
        <img src="<?= e($t['preview']) ?>" class="card-img-top" onerror="this.src='https://via.placeholder.com/400x300?text=<?= urlencode($t['name']) ?>'">
        <div class="card-body">
            <h5><?= e($t['name']) ?></h5>
            <?php if ($t['slug']===$current): ?>
                <span class="badge bg-success">Active</span>
            <?php else: ?>
                <form method="POST" action="/theme/switch"><?= csrf_field() ?>
                    <input type="hidden" name="theme" value="<?= e($t['slug']) ?>">
                    <button class="btn btn-outline-primary btn-sm">Activate</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<form method="POST" action="/theme/customize" class="card p-4">
    <h5>Customize</h5>
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-3"><label>Primary Color</label><input type="color" name="primary_color" value="<?= e($settings['primary_color'] ?? '#0d6efd') ?>" class="form-control form-control-color"></div>
        <div class="col-md-3"><label>Secondary Color</label><input type="color" name="secondary_color" value="<?= e($settings['secondary_color'] ?? '#6c757d') ?>" class="form-control form-control-color"></div>
        <div class="col-md-3"><label>Accent Color</label><input type="color" name="accent_color" value="<?= e($settings['accent_color'] ?? '#ff6b6b') ?>" class="form-control form-control-color"></div>
        <div class="col-md-3"><label>Font</label><select name="font_family" class="form-select"><option>Inter</option><option>Roboto</option><option>Poppins</option></select></div>
        <div class="col-md-12">
            <div class="form-check"><input type="checkbox" name="show_announcement" value="1" <?= !empty($settings['show_announcement'])?'checked':'' ?> class="form-check-input" id="sa"><label for="sa">Show announcement bar</label></div>
            <input name="announcement_text" value="<?= e($settings['announcement_text'] ?? '') ?>" class="form-control mt-2" placeholder="e.g. Free shipping on orders over $50">
        </div>
    </div>
    <button class="btn btn-primary mt-3">Save Customization</button>
</form>
