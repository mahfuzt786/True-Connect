<?php
$layoutWide = true; // setup needs more horizontal room for the plan-details panel
$defaultCurrency = config('app.currency', 'INR');
$defaultTimezone = config('app.timezone', 'Asia/Kolkata');

// Carry-overs from the registration form, if present.
$intentStoreType = Session::get('intent_store_type');
$intentPlanId    = (int)Session::get('intent_plan_id', 0);

// Single source of truth — same data the public site uses.
$currencyMeta = config('pricing.currencies', []);
$planPrices   = config('pricing.plans', []);

// Build a JS-ready details map from each plan row.
$planDetails = [];
foreach ($plans as $p) {
    $features = [];
    if (!empty($p['features'])) {
        $decoded = json_decode($p['features'], true);
        if (is_array($decoded)) $features = array_values(array_filter(array_map(fn($v) => trim((string)$v), $decoded), fn($v) => $v !== ''));
    }
    $planDetails[$p['id']] = [
        'name'        => $p['name'],
        'slug'        => $p['slug'],
        'description' => $p['description'] ?? '',
        'trial_days'  => (int)$p['trial_days'],
        'limits' => [
            'products' => $p['products_limit'] !== null ? (int)$p['products_limit'] : null,
            'vendors'  => $p['vendors_limit']  !== null ? (int)$p['vendors_limit']  : null,
            'storage'  => $p['storage_limit_mb'] !== null ? (int)$p['storage_limit_mb'] : null,
        ],
        'capabilities' => [
            'marketplace' => !empty($p['marketplace_enabled']),
            'custom_domain' => !empty($p['custom_domain']),
            'api_access' => !empty($p['api_access']),
            'analytics' => !empty($p['analytics']),
        ],
        'features' => $features,
    ];
}
?>
<h2 class="mb-4 fw-bold">Set up your store</h2>
<p class="text-muted mb-4">Tell us about your store and pick a plan to get started.</p>
<form method="POST" action="/store/setup">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Store Name *</label>
        <input type="text" name="name" class="form-control form-control-lg" required>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Store Type *</label>
            <select name="type" class="form-select form-select-lg" required>
                <option value="ecommerce"   <?= $intentStoreType === 'ecommerce'   ? 'selected' : '' ?>>E-commerce (single vendor)</option>
                <option value="marketplace" <?= $intentStoreType === 'marketplace' ? 'selected' : '' ?>>Marketplace (multi-vendor)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Currency *</label>
            <select name="currency" id="currency-select" class="form-select form-select-lg" required>
                <?php foreach ($currencyMeta as $code => $meta): ?>
                    <option value="<?= $code ?>" <?= $code === $defaultCurrency ? 'selected' : '' ?>><?= e($meta['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Language *</label>
            <select name="language" class="form-select" required>
                <option value="en">English</option><option value="es">Spanish</option><option value="fr">French</option>
                <option value="de">German</option><option value="ar">Arabic</option><option value="bn">Bengali</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Timezone *</label>
            <select name="timezone" class="form-select" required>
                <?php foreach (timezone_identifiers_list() as $tz): ?>
                    <option value="<?= $tz ?>" <?= $tz === $defaultTimezone ? 'selected' : '' ?>><?= $tz ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Choose your plan *</label>
        <div class="row g-2">
            <?php foreach ($plans as $plan):
                $isSelected = $intentPlanId
                    ? (int)$plan['id'] === $intentPlanId
                    : $plan['slug'] === 'pro';
            ?>
                <div class="col-6 col-lg-3">
                    <label class="card h-100 plan-option <?= $isSelected ? 'border-primary bg-light' : '' ?>"
                           data-plan-id="<?= (int)$plan['id'] ?>">
                        <input type="radio" name="plan_id" value="<?= $plan['id'] ?>" class="d-none" required <?= $isSelected ? 'checked' : '' ?>>
                        <div class="card-body text-center px-2 py-3">
                            <h6 class="plan-name mb-2"><?= e($plan['name']) ?></h6>
                            <div class="plan-price mb-2" data-plan-slug="<?= e($plan['slug']) ?>">
                                <strong class="js-price"></strong><small class="text-muted">/mo</small>
                            </div>
                            <small class="text-muted d-block"><?= (int)$plan['trial_days'] ?> day free trial</small>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Selected plan details -->
    <div class="mb-4">
        <div id="plan-details" class="card border-primary-subtle bg-light-subtle">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
                    <div>
                        <h5 class="mb-1" id="pd-name"></h5>
                        <small class="text-muted" id="pd-trial"></small>
                    </div>
                    <div class="text-end">
                        <div><strong id="pd-monthly"></strong> <small class="text-muted">/mo</small></div>
                        <small class="text-muted">or <span id="pd-yearly"></span> <span class="text-muted">/yr</span></small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <h6 class="text-uppercase small text-muted mb-2">Limits</h6>
                        <ul class="list-unstyled mb-0 small" id="pd-limits"></ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-uppercase small text-muted mb-2">Capabilities</h6>
                        <ul class="list-unstyled mb-0 small" id="pd-capabilities"></ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-uppercase small text-muted mb-2">Features</h6>
                        <ul class="list-unstyled mb-0 small" id="pd-features"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">Create Store</button>
</form>

<style>
    .plan-option { cursor: pointer; transition: all .15s ease; }
    .plan-option:hover { border-color: var(--bs-primary); }
    .plan-option .plan-name,
    .plan-option .plan-price { word-break: keep-all; overflow-wrap: normal; white-space: nowrap; }
    .plan-option .plan-name { font-size: 1rem; }
    .plan-option .plan-price { font-size: 1.05rem; }
    #plan-details ul li { margin-bottom: .25rem; }
    #plan-details .bi-check-circle-fill { color: var(--bs-success); }
    #plan-details .bi-x-circle { color: var(--bs-secondary); opacity: .5; }
</style>

<script>
(function () {
    const meta    = <?= json_encode($currencyMeta, JSON_UNESCAPED_UNICODE) ?>;
    const prices  = <?= json_encode($planPrices) ?>;
    const details = <?= json_encode($planDetails, JSON_UNESCAPED_UNICODE) ?>;

    function fmt(value, code) {
        const m = meta[code] || meta.INR;
        return m.symbol + Number(value).toLocaleString('en-US');
    }

    function priceFor(slug, period, code) {
        return (prices[slug] && prices[slug][period] && prices[slug][period][code]) ?? 0;
    }

    function updateCardPrices(code) {
        document.querySelectorAll('.plan-price').forEach(el => {
            const slug = el.dataset.planSlug;
            const target = el.querySelector('.js-price');
            if (target && slug) target.textContent = fmt(priceFor(slug, 'monthly', code), code);
        });
    }

    function fmtLimit(n, unit) {
        // Always include the unit so "Unlimited" never appears bare.
        const num = (n === null || n === undefined) ? 'Unlimited' : n.toLocaleString('en-US');
        return unit ? num + ' ' + unit : num;
    }

    function bytesLabel(mb) {
        if (mb === null || mb === undefined) return 'Unlimited storage';
        if (mb >= 1024) return (mb / 1024).toFixed(mb % 1024 === 0 ? 0 : 1) + ' GB storage';
        return mb + ' MB storage';
    }

    function capRow(label, on) {
        const icon = on
            ? '<i class="bi bi-check-circle-fill me-2"></i>'
            : '<i class="bi bi-x-circle me-2"></i>';
        const cls = on ? '' : 'text-muted';
        return '<li class="' + cls + '">' + icon + label + '</li>';
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function renderDetails(planId, code) {
        const d = details[planId];
        if (!d) return;
        document.getElementById('pd-name').textContent    = d.name;
        document.getElementById('pd-trial').textContent   = d.trial_days + ' day free trial';
        document.getElementById('pd-monthly').textContent = fmt(priceFor(d.slug, 'monthly', code), code);
        document.getElementById('pd-yearly').textContent  = fmt(priceFor(d.slug, 'yearly', code), code);

        document.getElementById('pd-limits').innerHTML =
            '<li><i class="bi bi-box-seam me-2 text-muted"></i>'    + fmtLimit(d.limits.products, 'products') + '</li>' +
            '<li><i class="bi bi-people me-2 text-muted"></i>'      + fmtLimit(d.limits.vendors,  'vendors')  + '</li>' +
            '<li><i class="bi bi-hdd-stack me-2 text-muted"></i>'   + bytesLabel(d.limits.storage)            + '</li>';

        document.getElementById('pd-capabilities').innerHTML =
            capRow('Multi-vendor marketplace', d.capabilities.marketplace) +
            capRow('Custom domain',            d.capabilities.custom_domain) +
            capRow('API access',               d.capabilities.api_access) +
            capRow('Advanced analytics',       d.capabilities.analytics);

        const featuresEl = document.getElementById('pd-features');
        if (!d.features || d.features.length === 0) {
            featuresEl.innerHTML = '<li class="text-muted">No additional features listed.</li>';
        } else {
            featuresEl.innerHTML = d.features.map(f =>
                '<li><i class="bi bi-check2 me-2 text-success"></i>' + escapeHtml(f) + '</li>'
            ).join('');
        }
    }

    function selectedPlanId() {
        const checked = document.querySelector('input[name="plan_id"]:checked');
        return checked ? Number(checked.value) : null;
    }

    function refreshAll() {
        const code = document.getElementById('currency-select').value;
        updateCardPrices(code);
        const pid = selectedPlanId();
        if (pid !== null) renderDetails(pid, code);
    }

    document.getElementById('currency-select').addEventListener('change', refreshAll);

    document.querySelectorAll('.plan-option').forEach(el => {
        el.addEventListener('click', () => {
            document.querySelectorAll('.plan-option').forEach(p => p.classList.remove('border-primary','bg-light'));
            el.classList.add('border-primary','bg-light');
            el.querySelector('input').checked = true;
            refreshAll();
        });
    });

    refreshAll();
})();
</script>
