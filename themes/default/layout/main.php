<!DOCTYPE html>
<html lang="<?= e($store['language'] ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($store['meta_title'] ?: $store['name']) ?></title>
    <meta name="description" content="<?= e($store['meta_description'] ?? '') ?>">
    <?php if($store['favicon']): ?><link rel="icon" href="<?= e($store['favicon']) ?>"><?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $themeUrl ?>/css/style.css">
    <?php $ts = json_decode($store['theme_settings'] ?? '{}', true) ?: []; ?>
    <style>
        :root {
            --primary: <?= e($ts['primary_color'] ?? '#0d6efd') ?>;
            --secondary: <?= e($ts['secondary_color'] ?? '#6c757d') ?>;
        }
    </style>
    <?php if(!empty($store['google_analytics_id'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($store['google_analytics_id']) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($store['google_analytics_id']) ?>');</script>
    <?php endif; ?>
</head>
<body>
<?php if(!empty($ts['show_announcement']) && !empty($ts['announcement_text'])): ?>
<div class="bg-primary text-white text-center py-2 small"><?= e($ts['announcement_text']) ?></div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/shop/<?= e($store['slug']) ?>">
            <?php if($store['logo']): ?><img src="<?= e($store['logo']) ?>" height="40"><?php else: ?><strong><?= e($store['name']) ?></strong><?php endif; ?>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>/products">Shop</a></li>
                <?php if($store['type']==='marketplace'): ?>
                    <li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>/vendors">Vendors</a></li>
                <?php endif; ?>
            </ul>
            <form class="d-flex me-3" action="/shop/<?= e($store['slug']) ?>/search">
                <input name="q" class="form-control" placeholder="Search...">
            </form>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>/cart"><i class="bi bi-cart"></i></a></li>
                <?php if(isAuth()): ?>
                    <li class="nav-item"><a class="nav-link" href="/account">Account</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php include VIEWS_PATH . '/partials/flash.php'; ?>
<?= $content ?>

<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><?= e($store['name']) ?></h5>
                <p class="text-white-50"><?= e($store['description'] ?? '') ?></p>
            </div>
            <div class="col-md-4">
                <h6>Contact</h6>
                <p class="text-white-50">
                    <?= e($store['email'] ?? '') ?><br>
                    <?= e($store['phone'] ?? '') ?>
                </p>
            </div>
            <div class="col-md-4">
                <h6>Follow Us</h6>
                <?php $social = json_decode($store['social_links'] ?? '{}', true) ?: []; ?>
                <?php foreach (['facebook','twitter','instagram','youtube'] as $sn): ?>
                    <?php if (!empty($social[$sn])): ?><a href="<?= e($social[$sn]) ?>" class="text-white-50 me-2"><i class="bi bi-<?= $sn ?>"></i></a><?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="text-center text-white-50 mb-0">&copy; <?= date('Y') ?> <?= e($store['name']) ?>. Powered by <?= e(config('app.name')) ?>.</p>
    </div>
</footer>
<script>window.STORE_BASE = '<?= BASE_PATH ?>/shop/<?= e($store['slug']) ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $themeUrl ?>/js/script.js"></script>
</body></html>
