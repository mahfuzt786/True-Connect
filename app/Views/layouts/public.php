<!DOCTYPE html>
<html lang="<?= e(config('app.locale', 'en')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <?= seo_meta($seo ?? ['title' => $title ?? null]) ?>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    <link rel="alternate icon" href="<?= asset('favicon.svg') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <?php include VIEWS_PATH . '/partials/lockdown.php'; ?>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,.05); }
        .hero { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: #fff; padding: 100px 0; }
        .feature-card { padding: 30px; border-radius: 12px; transition: transform .3s; }
        .feature-card:hover { transform: translateY(-5px); }
        .pricing-card {
            border: 2px solid #eee; border-radius: 12px; padding: 30px;
            transition: all .3s;
            display: flex; flex-direction: column; height: 100%;
        }
        .pricing-card.featured { border-color: #667eea; transform: scale(1.05); }
        .pricing-card .features-list { flex-grow: 1; }
        .pricing-card .pricing-cta { margin-top: auto; }
        footer { background: #1a1a2e; color: #fff; padding: 60px 0 30px; margin-top: 80px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
                <img src="<?= asset('images/logo.svg') ?>" alt="<?= e(config('app.name')) ?>" height="36">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/marketplace">Marketplace</a></li>
                    <li class="nav-item"><a class="nav-link" href="/features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
                </ul>
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item dropdown me-lg-2">
                        <a class="nav-link dropdown-toggle" href="#" id="currencyMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-currency-exchange me-1"></i><span data-currency-label>INR</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="currencyMenu">
                            <?php foreach (config('pricing.currencies', []) as $code => $meta): ?>
                                <li><a class="dropdown-item" href="#" data-currency-pick="<?= $code ?>"><?= e($meta['label']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php if (isAuth()): ?>
                        <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/sell/register">Sell with us</a></li>
                        <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                        <li class="nav-item"><a class="btn btn-primary ms-2" href="/register">Get Started</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?= $content ?>
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?= e(config('app.name')) ?></h5>
                    <p class="text-white-50">The all-in-one platform to launch your online store or marketplace.</p>
                </div>
                <div class="col-md-2 offset-md-2">
                    <h6>Product</h6>
                    <ul class="list-unstyled">
                        <li><a href="/features" class="text-white-50">Features</a></li>
                        <li><a href="/pricing" class="text-white-50">Pricing</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6>Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="/about" class="text-white-50">About</a></li>
                        <li><a href="/contact" class="text-white-50">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6>Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Privacy</a></li>
                        <li><a href="#" class="text-white-50">Terms</a></li>
                    </ul>
                </div>
            </div>
            <?php $siteSocial = social_links(); if (!empty(array_filter($siteSocial))): ?>
                <hr class="border-secondary mt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <small class="text-white-50 mb-0">Follow us</small>
                    <?php $links = $siteSocial; $size = 'sm'; $tone = 'light'; include VIEWS_PATH . '/partials/social-icons.php'; ?>
                </div>
            <?php endif; ?>
            <hr class="border-secondary mt-4">
            <div class="d-flex flex-wrap justify-content-between gap-2">
                <p class="text-white-50 mb-0 small">&copy; <?= date('Y') ?> <?= e(config('app.name')) ?>. All rights reserved.</p>
                <p class="text-white-50 mb-0 small">
                    Developed by
                    <a href="https://truecircle.in/" target="_blank" rel="noopener" class="text-white text-decoration-none fw-semibold">truecircle.in</a>
                </p>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global pricing currency state shared across home, pricing, etc.
        window.AppPricing = (function () {
            const cfg = {
                currencies: <?= json_encode(config('pricing.currencies', []), JSON_UNESCAPED_UNICODE) ?>,
                plans:      <?= json_encode(config('pricing.plans', []), JSON_UNESCAPED_UNICODE) ?>,
                defaultCurrency: <?= json_encode(config('pricing.default_currency', 'INR')) ?>,
            };

            function getCurrency() {
                const stored = localStorage.getItem('app_currency');
                return cfg.currencies[stored] ? stored : cfg.defaultCurrency;
            }

            function setCurrency(code) {
                if (!cfg.currencies[code]) return;
                localStorage.setItem('app_currency', code);
                applyAll();
            }

            function symbol(code) {
                return (cfg.currencies[code] || cfg.currencies[cfg.defaultCurrency]).symbol;
            }

            function priceFor(slug, period, code) {
                const p = cfg.plans[slug];
                if (!p || !p[period]) return null;
                return p[period][code] ?? null;
            }

            function fmt(amount, code) {
                if (amount === null || amount === undefined) return '';
                return symbol(code) + Number(amount).toLocaleString('en-US');
            }

            function applyAll() {
                const code = getCurrency();
                // Update label in nav
                document.querySelectorAll('[data-currency-label]').forEach(el => el.textContent = code);
                // Update plan-price elements: <span data-plan-slug="pro" data-period="monthly"></span>
                document.querySelectorAll('[data-plan-slug][data-period]').forEach(el => {
                    const slug   = el.dataset.planSlug;
                    const period = el.dataset.period;
                    const v = priceFor(slug, period, code);
                    el.textContent = v === null ? '' : fmt(v, code);
                });
                // Notify listeners that may need to re-render derived UI.
                document.dispatchEvent(new CustomEvent('app:currency-changed', { detail: { code } }));
            }

            document.addEventListener('click', (e) => {
                const t = e.target.closest('[data-currency-pick]');
                if (!t) return;
                e.preventDefault();
                setCurrency(t.dataset.currencyPick);
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyAll);
            } else {
                applyAll();
            }

            return { getCurrency, setCurrency, symbol, priceFor, fmt };
        })();
    </script>
</body>
</html>
