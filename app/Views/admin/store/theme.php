<h2 class="mb-4">Choose Your Theme</h2>
<form method="POST" action="/store/setup/theme">
    <?= csrf_field() ?>
    <div class="row g-4">
        <?php foreach ($themes as $t): ?>
            <div class="col-md-4">
                <label class="card h-100" style="cursor:pointer;">
                    <input type="radio" name="theme" value="<?= e($t['slug']) ?>" class="d-none" <?= $t['slug'] === ($currentTheme ?? 'default') ? 'checked' : '' ?>>
                    <img src="<?= e($t['preview']) ?>" class="card-img-top" alt="<?= e($t['name']) ?>" onerror="this.src='https://via.placeholder.com/400x300?text=<?= urlencode($t['name']) ?>'">
                    <div class="card-body">
                        <h5><?= e($t['name']) ?></h5>
                        <p class="text-muted small"><?= e($t['description'] ?? '') ?></p>
                    </div>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary btn-lg mt-4">Save & Continue to Dashboard</button>
</form>
