<?php $pricingCfg = config('pricing.plans', []); $defaultCcy = config('pricing.default_currency', 'INR'); $currencies = config('pricing.currencies', []); $sym = $currencies[$defaultCcy]['symbol'] ?? ''; ?>
<div class="hero py-5">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Simple, transparent pricing</h1>
        <p class="lead">Start free. Upgrade as you grow. <span class="opacity-75 small">Use the currency switcher in the top right to change currency.</span></p>
    </div>
</div>
<section class="py-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-3 d-flex">
                    <div class="pricing-card bg-white text-center w-100 <?= $plan['slug'] === 'pro' ? 'featured' : '' ?>">
                        <h4 class="fw-bold"><?= e($plan['name']) ?></h4>
                        <p class="text-muted small"><?= e($plan['description']) ?></p>
                        <div class="my-3">
                            <?php $iM = $pricingCfg[$plan['slug']]['monthly'][$defaultCcy] ?? null; ?>
                            <span class="display-5 fw-bold" data-plan-slug="<?= e($plan['slug']) ?>" data-period="monthly"><?= $iM !== null ? e($sym . number_format($iM)) : '' ?></span>
                            <small class="text-muted">/mo</small>
                        </div>
                        <p class="small text-muted">
                            <?php $iY = $pricingCfg[$plan['slug']]['yearly'][$defaultCcy] ?? null; ?>
                            <span data-plan-slug="<?= e($plan['slug']) ?>" data-period="yearly"><?= $iY !== null ? e($sym . number_format($iY)) : '' ?></span> billed yearly (save 17%)
                        </p>
                        <ul class="list-unstyled text-start features-list">
                            <?php foreach ((json_decode($plan['features'] ?? '[]', true) ?: []) as $feat): ?>
                                <li class="py-1"><i class="bi bi-check-circle text-success me-2"></i><?= e($feat) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="/register" class="btn btn-primary w-100 pricing-cta">Start <?= (int)$plan['trial_days'] ?>-day trial</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
