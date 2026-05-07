<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Create Store</h2>
    <a href="/admin/stores" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to Stores</a>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    The store admin is a <strong>separate user</strong> from your super-admin account.
    They will sign in with their own email and password to manage this store.
</div>

<form method="POST" action="/admin/stores"><?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Store</h5>
                    <div class="mb-3">
                        <label class="form-label">Store Name *</label>
                        <input name="store_name" class="form-control" required value="<?= e(old('store_name')) ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="ecommerce">E-commerce (single vendor)</option>
                                <option value="marketplace">Marketplace (multi-vendor)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency *</label>
                            <select name="currency" class="form-select" required>
                                <?php foreach (['INR'=>'INR (₹)','USD'=>'USD ($)','EUR'=>'EUR (€)','AED'=>'AED'] as $code => $label): ?>
                                    <option value="<?= $code ?>" <?= $code === 'INR' ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Language *</label>
                            <select name="language" class="form-select" required>
                                <option value="en">English</option>
                                <option value="bn">Bengali</option>
                                <option value="es">Spanish</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Timezone *</label>
                            <select name="timezone" class="form-select" required>
                                <?php foreach (timezone_identifiers_list() as $tz): ?>
                                    <option value="<?= $tz ?>" <?= $tz === 'Asia/Kolkata' ? 'selected' : '' ?>><?= $tz ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Store Admin (login)</h5>
                    <p class="text-muted small">Different from your super-admin email. The store owner will sign in with these credentials.</p>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input name="admin_name" class="form-control" required value="<?= e(old('admin_name')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="admin_email" class="form-control" required value="<?= e(old('admin_email')) ?>" placeholder="store-admin@example.com">
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Temporary Password *</label>
                        <input type="text" name="admin_password" class="form-control" required minlength="8"
                               pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}"
                               title="At least 8 characters with uppercase, lowercase and a number">
                        <small class="text-muted">Min 8 chars · 1 uppercase · 1 lowercase · 1 number</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Plan</h5>
                    <p class="text-muted small mb-3">Default <strong>Custom</strong> = no subscription fee on setup. Edit later from <a href="/admin/subscriptions">Subscriptions</a>.</p>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($plans as $plan):
                            $isCustom = $plan['slug'] === 'custom';
                            $checked  = $isCustom; // default selection
                        ?>
                            <label class="border rounded p-3 plan-pick d-flex align-items-start <?= $isCustom ? 'border-success bg-success-subtle' : '' ?>" style="cursor: pointer;">
                                <input type="radio" name="plan_id" value="<?= (int)$plan['id'] ?>" required <?= $checked ? 'checked' : '' ?> class="mt-1 me-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <strong><?= e($plan['name']) ?></strong>
                                        <?php if ($isCustom): ?>
                                            <span class="badge bg-success">Free · Recommended</span>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-link p-0 ms-auto"
                                                data-bs-toggle="modal"
                                                data-bs-target="#planInfo-<?= (int)$plan['id'] ?>"
                                                title="View plan details">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </div>
                                    <div class="small mt-1">
                                        <?php if ((float)$plan['price_monthly'] === 0.0): ?>
                                            <span class="text-success fw-semibold">Free</span>
                                        <?php else: ?>
                                            <?= money($plan['price_monthly']) ?>/mo
                                        <?php endif; ?>
                                        <?php if ((int)$plan['trial_days'] > 0): ?>
                                            <span class="text-muted">· <?= (int)$plan['trial_days'] ?> day trial</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Create Store</button>
        <a href="/admin/stores" class="btn btn-light">Cancel</a>
    </div>
</form>

<!-- Plan detail modals -->
<?php foreach ($plans as $plan):
    $features = json_decode($plan['features'] ?? '[]', true) ?: [];
    // Always append the unit so "Unlimited" never appears bare. Empty value falls back to "Unlimited".
    $fmtLimit = fn($v, $unit = '') => (($v === null || $v === '') ? 'Unlimited' : number_format((int)$v)) . ($unit ? ' ' . $unit : '');
?>
<div class="modal fade" id="planInfo-<?= (int)$plan['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= e($plan['name']) ?> plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($plan['description'])): ?>
                    <p class="text-muted small"><?= e($plan['description']) ?></p>
                <?php endif; ?>

                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <?php if ((float)$plan['price_monthly'] === 0.0): ?>
                        <span class="display-6 fw-bold text-success">Free</span>
                    <?php else: ?>
                        <span class="display-6 fw-bold"><?= money($plan['price_monthly']) ?></span>
                        <small class="text-muted">/mo</small>
                        <span class="text-muted small ms-2">or <?= money($plan['price_yearly']) ?>/yr</span>
                    <?php endif; ?>
                </div>

                <h6 class="text-uppercase small text-muted mb-2">Limits</h6>
                <ul class="list-unstyled small mb-3">
                    <li><i class="bi bi-box-seam me-2 text-muted"></i><?= $fmtLimit($plan['products_limit'], 'products') ?></li>
                    <li><i class="bi bi-people me-2 text-muted"></i><?= $fmtLimit($plan['vendors_limit'], 'vendors') ?></li>
                    <li><i class="bi bi-hdd-stack me-2 text-muted"></i>
                        <?= $plan['storage_limit_mb'] === null ? 'Unlimited storage' : (
                            (int)$plan['storage_limit_mb'] >= 1024
                                ? number_format((int)$plan['storage_limit_mb'] / 1024, 1) . ' GB storage'
                                : (int)$plan['storage_limit_mb'] . ' MB storage'
                        ) ?>
                    </li>
                    <li><i class="bi bi-receipt me-2 text-muted"></i><?= $fmtLimit($plan['orders_limit'], 'orders / month') ?></li>
                    <li><i class="bi bi-person-badge me-2 text-muted"></i><?= $fmtLimit($plan['staff_limit'], 'staff accounts') ?></li>
                </ul>

                <h6 class="text-uppercase small text-muted mb-2">Capabilities</h6>
                <ul class="list-unstyled small mb-3">
                    <?php foreach ([
                        'marketplace_enabled' => 'Multi-vendor marketplace',
                        'custom_domain'       => 'Custom domain',
                        'api_access'          => 'API access',
                        'analytics'           => 'Advanced analytics',
                        'priority_support'    => 'Priority support',
                    ] as $key => $label):
                        $on = !empty($plan[$key]);
                    ?>
                        <li class="<?= $on ? '' : 'text-muted' ?>">
                            <i class="bi <?= $on ? 'bi-check-circle-fill text-success' : 'bi-x-circle' ?> me-2"></i><?= e($label) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty($features)): ?>
                    <h6 class="text-uppercase small text-muted mb-2">Features</h6>
                    <ul class="list-unstyled small mb-0">
                        <?php foreach ($features as $f): ?>
                            <li><i class="bi bi-check2 text-success me-2"></i><?= e($f) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ((int)$plan['trial_days'] > 0): ?>
                    <div class="alert alert-light border mt-3 mb-0 small">
                        Includes a <strong><?= (int)$plan['trial_days'] ?>-day free trial</strong>.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
document.querySelectorAll('.plan-pick').forEach(el => {
    el.addEventListener('click', function (e) {
        // Don't toggle when clicking the info button.
        if (e.target.closest('button')) return;
        document.querySelectorAll('.plan-pick').forEach(p => p.classList.remove('border-success', 'bg-success-subtle'));
        this.classList.add('border-success', 'bg-success-subtle');
        this.querySelector('input[type=radio]').checked = true;
    });
});
</script>
