<?php $layoutWide = true; ?>
<h2 class="mb-1 fw-bold text-center">Become a seller</h2>
<p class="text-muted text-center mb-4">Pick how you want to sell on True Commerce</p>

<form method="POST" action="/sell/register" id="sellerForm" novalidate>
    <?= csrf_field() ?>

    <!-- Step 1: Seller type -->
    <div class="mb-4">
        <label class="form-label fw-semibold">How do you want to sell?</label>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="card seller-type-card h-100 p-3 border <?= old('seller_type') !== 'marketplace' ? 'border-primary bg-light' : '' ?>" style="cursor:pointer;">
                    <input type="radio" name="seller_type" value="ecommerce" required
                           <?= old('seller_type') !== 'marketplace' ? 'checked' : '' ?>
                           class="d-none">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-shop fs-3 text-primary"></i>
                        <div>
                            <div class="fw-semibold">Open my own E-commerce store</div>
                            <small class="text-muted">I sell my own products on a single-vendor store I control.</small>
                        </div>
                    </div>
                </label>
            </div>
            <div class="col-md-6">
                <label class="card seller-type-card h-100 p-3 border <?= old('seller_type') === 'marketplace' ? 'border-primary bg-light' : '' ?>" style="cursor:pointer;">
                    <input type="radio" name="seller_type" value="marketplace" required
                           <?= old('seller_type') === 'marketplace' ? 'checked' : '' ?>
                           class="d-none">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-shop-window fs-3 text-primary"></i>
                        <div>
                            <div class="fw-semibold">Sell on a Marketplace</div>
                            <small class="text-muted">I list as a third-party vendor on someone else's marketplace.</small>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Explainer text — swaps based on selection -->
        <div class="alert alert-light border mt-3 mb-0 small" id="seller-explainer" data-explain-ecommerce="<?= e('E-commerce is the electronic buying and selling of goods or services over the internet, handling everything from product listings to digital payments. It eliminates physical boundaries by allowing businesses and consumers to transact instantly through websites, mobile apps, and social media platforms.') ?>" data-explain-marketplace="<?= e('A marketplace is a platform or physical space where multiple third-party vendors registered as marketplace are the buyers and sellers congregate to facilitate the exchange of goods and services. Unlike an E-commerce, the marketplace operator does not usually own the inventory but instead provides the infrastructure and trust necessary for transactions to occur.') ?>">
            <span id="seller-explainer-text"></span>
        </div>
    </div>

    <!-- Step 2: Account fields -->
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" value="<?= e(old('name')) ?>" class="form-control form-control-lg" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" value="<?= e(old('email')) ?>" class="form-control form-control-lg" required>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="seller-password" class="form-control form-control-lg"
                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}"
                       title="At least 8 characters with uppercase, lowercase and a number"
                       required autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" id="seller-togglePassword" tabindex="-1" aria-label="Show password">
                    <i class="bi bi-eye" id="seller-togglePasswordIcon"></i>
                </button>
            </div>
            <small class="text-muted">8+ chars, with uppercase, lowercase and a number.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <div class="input-group">
                <input type="password" name="password_confirmation" id="seller-passwordConfirm" class="form-control form-control-lg" required autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" id="seller-toggleConfirm" tabindex="-1" aria-label="Show password">
                    <i class="bi bi-eye" id="seller-toggleConfirmIcon"></i>
                </button>
            </div>
            <small class="form-text" id="seller-matchLabel"></small>
        </div>
    </div>

    <!-- Step 3 (E-commerce only): plan picker -->
    <div class="mt-4 ecommerce-only">
        <label class="form-label fw-semibold">Choose a Plan</label>
        <div class="row g-2">
            <?php foreach ($plans as $plan): ?>
                <div class="col-6 col-md-3">
                    <label class="card plan-pick h-100 p-2 border <?= ($plan['slug'] === 'pro' && !old('plan_id')) || (int)old('plan_id') === (int)$plan['id'] ? 'border-primary bg-light' : '' ?>" style="cursor:pointer;">
                        <input type="radio" name="plan_id" value="<?= (int)$plan['id'] ?>"
                               <?= ($plan['slug'] === 'pro' && !old('plan_id')) || (int)old('plan_id') === (int)$plan['id'] ? 'checked' : '' ?>
                               class="d-none plan-pick-input">
                        <div class="text-center">
                            <div class="fw-semibold"><?= e($plan['name']) ?></div>
                            <div class="small">
                                <strong><?= money($plan['price_monthly']) ?></strong><span class="text-muted">/mo</span>
                            </div>
                            <small class="text-muted d-block"><?= (int)$plan['trial_days'] ?>-day trial</small>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <small class="text-muted d-block mt-1">You can change this on the next step.</small>
    </div>

    <!-- Marketplace next-step note -->
    <div class="alert alert-info mt-4 marketplace-only d-none">
        <i class="bi bi-info-circle me-1"></i>
        After creating your account you'll be able to apply to any active marketplace.
        Marketplace operators review and approve vendor applications.
    </div>

    <div class="form-check mt-4 mb-3">
        <input type="checkbox" name="terms" value="1" id="seller-terms" class="form-check-input" required>
        <label for="seller-terms" class="form-check-label">I agree to the Terms of Service and Privacy Policy</label>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Create Seller Account</button>

    <div class="text-center small text-muted mt-3">
        Just want to shop?
        <a href="/register" class="text-decoration-none fw-semibold">Sign up as a buyer →</a>
    </div>
    <p class="text-center mt-3 mb-0">
        Already have an account? <a href="/login" class="text-decoration-none">Sign in</a>
    </p>
</form>

<style>
    .seller-type-card, .plan-pick { transition: all .15s ease; }
    .seller-type-card:hover, .plan-pick:hover { border-color: var(--bs-primary) !important; }
</style>

<script>
(function () {
    const form        = document.getElementById('sellerForm');
    const explainer   = document.getElementById('seller-explainer');
    const explainText = document.getElementById('seller-explainer-text');
    const ecomBlocks  = document.querySelectorAll('.ecommerce-only');
    const mktBlocks   = document.querySelectorAll('.marketplace-only');
    const planInputs  = document.querySelectorAll('.plan-pick-input');

    function applyType(type) {
        if (type === 'marketplace') {
            ecomBlocks.forEach(b => b.classList.add('d-none'));
            mktBlocks.forEach(b => b.classList.remove('d-none'));
            planInputs.forEach(i => { i.required = false; i.checked = false; });
            explainText.textContent = explainer.dataset.explainMarketplace;
        } else {
            ecomBlocks.forEach(b => b.classList.remove('d-none'));
            mktBlocks.forEach(b => b.classList.add('d-none'));
            // Re-instate at least the Pro radio as required.
            planInputs.forEach(i => { i.required = true; });
            // If nothing checked (e.g. after switching back), default to Pro.
            const anyChecked = Array.from(planInputs).some(i => i.checked);
            if (!anyChecked) {
                planInputs.forEach(i => { if (i.value === '<?= (int)(array_filter($plans, fn($p) => $p['slug'] === 'pro')[0]['id'] ?? 0) ?>') i.checked = true; });
            }
            explainText.textContent = explainer.dataset.explainEcommerce;
        }
    }

    document.querySelectorAll('.seller-type-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.seller-type-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
            card.classList.add('border-primary', 'bg-light');
            const radio = card.querySelector('input[type=radio]');
            radio.checked = true;
            applyType(radio.value);
        });
    });

    document.querySelectorAll('.plan-pick').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.plan-pick').forEach(c => c.classList.remove('border-primary', 'bg-light'));
            card.classList.add('border-primary', 'bg-light');
            card.querySelector('input[type=radio]').checked = true;
        });
    });

    // Password confirm match
    const pw = document.getElementById('seller-password');
    const cf = document.getElementById('seller-passwordConfirm');
    const ml = document.getElementById('seller-matchLabel');
    function checkMatch() {
        if (!cf.value) { ml.textContent = ''; cf.setCustomValidity(''); return; }
        if (cf.value === pw.value) { ml.textContent = 'Passwords match'; ml.className = 'form-text text-success'; cf.setCustomValidity(''); }
        else { ml.textContent = 'Passwords do not match'; ml.className = 'form-text text-danger'; cf.setCustomValidity('Passwords do not match'); }
    }
    pw.addEventListener('input', checkMatch);
    cf.addEventListener('input', checkMatch);

    // Eye toggles
    function bindToggle(btnId, iconId, inputEl) {
        document.getElementById(btnId).addEventListener('click', function () {
            const showing = inputEl.type === 'text';
            inputEl.type = showing ? 'password' : 'text';
            const icon = document.getElementById(iconId);
            icon.classList.toggle('bi-eye', showing);
            icon.classList.toggle('bi-eye-slash', !showing);
        });
    }
    bindToggle('seller-togglePassword', 'seller-togglePasswordIcon', pw);
    bindToggle('seller-toggleConfirm',  'seller-toggleConfirmIcon',  cf);

    // Initial state
    const initial = document.querySelector('input[name="seller_type"]:checked');
    applyType(initial ? initial.value : 'ecommerce');
})();
</script>
