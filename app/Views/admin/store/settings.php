<h2>Store Settings</h2>
<ul class="nav nav-tabs my-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general">General</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#appearance">Appearance</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#domain">Domain</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#seo">SEO</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#social">Social</a></li>
</ul>
<div class="tab-content">
    <div id="general" class="tab-pane fade show active">
        <form method="POST" action="/store/settings/general" class="card p-4">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label>Store Name</label><input name="name" class="form-control" value="<?= e($store['name']) ?>"></div>
                <div class="col-md-6"><label>Email</label><input name="email" type="email" class="form-control" value="<?= e($store['email'] ?? '') ?>"></div>
                <div class="col-md-6"><label>Phone</label><input name="phone" class="form-control" value="<?= e($store['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label>Currency</label><input name="currency" class="form-control" value="<?= e($store['currency']) ?>"></div>
                <div class="col-md-12"><label>Description</label><textarea name="description" class="form-control" rows="3"><?= e($store['description'] ?? '') ?></textarea></div>
                <div class="col-md-12"><label>Address</label><textarea name="address" class="form-control" rows="2"><?= e($store['address'] ?? '') ?></textarea></div>
                <div class="col-md-6"><label>Language</label><input name="language" class="form-control" value="<?= e($store['language']) ?>"></div>
                <div class="col-md-6"><label>Timezone</label><input name="timezone" class="form-control" value="<?= e($store['timezone']) ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
        </form>
    </div>
    <div id="appearance" class="tab-pane fade">
        <form method="POST" action="/store/settings/appearance" enctype="multipart/form-data" class="card p-4">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4"><label>Logo</label><input type="file" name="logo" class="form-control"><?php if($store['logo']): ?><img src="<?= e($store['logo']) ?>" class="mt-2" style="max-height:60px;"><?php endif; ?></div>
                <div class="col-md-4"><label>Favicon</label><input type="file" name="favicon" class="form-control"></div>
                <div class="col-md-4"><label>Banner</label><input type="file" name="banner" class="form-control"></div>
                <?php $ts = json_decode($store['theme_settings'] ?? '{}', true) ?: []; ?>
                <div class="col-md-4"><label>Primary Color</label><input type="color" name="primary_color" class="form-control form-control-color" value="<?= e($ts['primary_color'] ?? '#0d6efd') ?>"></div>
                <div class="col-md-4"><label>Secondary Color</label><input type="color" name="secondary_color" class="form-control form-control-color" value="<?= e($ts['secondary_color'] ?? '#6c757d') ?>"></div>
                <div class="col-md-4"><label>Font Family</label>
                    <select name="font_family" class="form-select">
                        <option>Inter</option><option>Roboto</option><option>Open Sans</option><option>Poppins</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save</button>
        </form>
    </div>
    <div id="domain" class="tab-pane fade">
        <form method="POST" action="/store/settings/domain" class="card p-4">
            <?= csrf_field() ?>
            <div class="mb-3"><label>Subdomain</label>
                <div class="input-group"><input name="subdomain" class="form-control" value="<?= e($store['subdomain']) ?>"><span class="input-group-text">.yourapp.com</span></div>
            </div>
            <div class="mb-3"><label>Custom Domain</label>
                <input name="custom_domain" class="form-control" value="<?= e($store['custom_domain'] ?? '') ?>" placeholder="yourstore.com">
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
    <div id="seo" class="tab-pane fade">
        <form method="POST" action="/store/settings/seo" class="card p-4">
            <?= csrf_field() ?>
            <div class="mb-3"><label>Meta Title</label><input name="meta_title" class="form-control" value="<?= e($store['meta_title'] ?? '') ?>"></div>
            <div class="mb-3"><label>Meta Description</label><textarea name="meta_description" class="form-control" rows="3"><?= e($store['meta_description'] ?? '') ?></textarea></div>
            <div class="mb-3"><label>Google Analytics ID</label><input name="google_analytics_id" class="form-control" value="<?= e($store['google_analytics_id'] ?? '') ?>"></div>
            <div class="mb-3"><label>Facebook Pixel ID</label><input name="facebook_pixel_id" class="form-control" value="<?= e($store['facebook_pixel_id'] ?? '') ?>"></div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
    <div id="social" class="tab-pane fade">
        <form method="POST" action="/store/settings/social" class="card p-4">
            <?= csrf_field() ?>
            <?php $sl = json_decode($store['social_links'] ?? '{}', true) ?: []; ?>
            <p class="text-muted small mb-3">Links shown on your storefront. Leave blank to hide. Must start with <code>https://</code>.</p>
            <div class="row g-3">
                <?php foreach (social_platforms() as $key => $meta): ?>
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi <?= e($meta['icon']) ?> me-1"></i><?= e($meta['label']) ?></label>
                        <input type="url"
                               name="<?= e($key) ?>"
                               value="<?= e($sl[$key] ?? '') ?>"
                               placeholder="https://…"
                               pattern="https?://.+"
                               class="form-control">
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save</button>
        </form>
    </div>
</div>
