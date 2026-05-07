<div class="hero py-5"><div class="container text-center"><h1 class="display-5 fw-bold">Powerful Features</h1><p class="lead">Everything you need to run a successful online business</p></div></div>
<section class="py-5"><div class="container"><div class="row g-4">
<?php foreach ([
    ['bi-cart-check','Product Management','Unlimited products, variants, categories, bulk imports'],
    ['bi-receipt','Order Management','Complete order lifecycle from pending to delivered'],
    ['bi-credit-card-2-front','Payments','Stripe, PayPal, Razorpay, COD - all integrated'],
    ['bi-truck','Shipping','Multi-zone, weight-based, flat rate shipping'],
    ['bi-percent','Tax','Configurable tax rates per region'],
    ['bi-people','Multi-Vendor','Marketplace mode with vendor accounts and commissions'],
    ['bi-palette','Themes','Beautiful, customizable storefront themes'],
    ['bi-graph-up','Analytics','Real-time dashboards and exportable reports'],
    ['bi-shield-check','Secure','SSL, CSRF, 2FA, encrypted storage'],
    ['bi-globe','Multi-Currency','Sell in any currency worldwide'],
    ['bi-translate','Multi-Language','Translate your store into multiple languages'],
    ['bi-code-slash','REST API','Full API for mobile apps and integrations'],
] as $f): ?>
    <div class="col-md-4"><div class="feature-card text-center bg-white shadow-sm h-100">
        <i class="bi <?= $f[0] ?> text-primary" style="font-size:48px;"></i>
        <h5 class="mt-3"><?= e($f[1]) ?></h5>
        <p class="text-muted"><?= e($f[2]) ?></p>
    </div></div>
<?php endforeach; ?>
</div></div></section>
