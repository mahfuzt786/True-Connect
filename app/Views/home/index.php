<section class="hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Build Your Online Store in Minutes</h1>
        <p class="lead my-4">All-in-one e-commerce + marketplace platform for everyone.</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="/register" class="btn btn-light btn-lg px-5">Start Free Trial</a>
            <a href="/pricing" class="btn btn-outline-light btn-lg px-5">View Pricing</a>
        </div>
        <p class="mt-3 small opacity-75">14-day free trial • No credit card required</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Everything you need to sell online</h2>
        <div class="row g-4">
            <?php foreach ([
                ['bi-cart','E-commerce Store','Sell physical & digital products with full inventory management.'],
                ['bi-shop','Marketplace Mode','Allow vendors to sell on your platform, with commissions.'],
                ['bi-credit-card','Multiple Payments','Stripe, PayPal, Razorpay, COD and more out of the box.'],
                ['bi-truck','Shipping & Tax','Configure zones, rates, and tax classes for any region.'],
                ['bi-graph-up','Analytics','Real-time sales, customer and traffic dashboards.'],
                ['bi-palette','Themes','Multiple themes, fully customizable with your branding.'],
            ] as $f): ?>
                <div class="col-md-4">
                    <div class="feature-card text-center bg-white shadow-sm">
                        <i class="bi <?= $f[0] ?> text-primary" style="font-size:48px;"></i>
                        <h4 class="mt-3"><?= e($f[1]) ?></h4>
                        <p class="text-muted"><?= e($f[2]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php $pricingCfg = config('pricing.plans', []); $defaultCcy = config('pricing.default_currency', 'INR'); $currencies = config('pricing.currencies', []); ?>
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Pick a plan that fits your business</h2>
        <div class="row g-4 justify-content-center">
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-3 d-flex">
                    <div class="pricing-card bg-white text-center w-100 <?= $plan['slug'] === 'pro' ? 'featured' : '' ?>">
                        <h4 class="fw-bold"><?= e($plan['name']) ?></h4>
                        <div class="my-3">
                            <?php $initial = $pricingCfg[$plan['slug']]['monthly'][$defaultCcy] ?? null; ?>
                            <span class="display-5 fw-bold" data-plan-slug="<?= e($plan['slug']) ?>" data-period="monthly"><?= $initial !== null ? e(($currencies[$defaultCcy]['symbol'] ?? '') . number_format($initial)) : '' ?></span>
                            <small class="text-muted">/mo</small>
                        </div>
                        <ul class="list-unstyled text-start small features-list">
                            <?php foreach ((json_decode($plan['features'] ?? '[]', true) ?: []) as $feat): ?>
                                <li><i class="bi bi-check-circle text-success me-2"></i><?= e($feat) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="/register" class="btn btn-primary w-100 pricing-cta">Start Trial</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
