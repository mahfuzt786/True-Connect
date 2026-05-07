<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <?= seo_meta(['title' => $title ?? 'Welcome', 'robots' => 'noindex,nofollow']) ?>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    <?php include VIEWS_PATH . '/partials/lockdown.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif; }
        .auth-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
        .auth-brand { color: #fff; text-align: center; margin-bottom: 30px; }
        .auth-brand h1 { font-weight: 700; }
    </style>
</head>
<body class="d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="<?= !empty($layoutWide) ? 'col-md-11 col-lg-10 col-xl-9' : 'col-md-7 col-lg-5' ?>">
                <div class="auth-brand">
                    <a href="/" class="d-inline-block mb-2"><img src="<?= asset('images/logo-white.svg') ?>" alt="<?= e(config('app.name')) ?>" height="48"></a>
                    <p class="opacity-75 mb-0">Build your online store in minutes</p>
                </div>
                <div class="auth-card p-4 p-md-5">
                    <?php include VIEWS_PATH . '/partials/flash.php'; ?>
                    <?= $content ?>
                </div>
                <div class="text-center text-white-50 mt-4 small">
                    &copy; <?= date('Y') ?> <?= e(config('app.name')) ?>. All rights reserved.<br>
                    Developed by
                    <a href="https://truecircle.in/" target="_blank" rel="noopener" class="text-white text-decoration-none fw-semibold">truecircle.in</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
