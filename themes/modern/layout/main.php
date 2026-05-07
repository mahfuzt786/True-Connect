<!DOCTYPE html>
<html lang="<?= e($store['language'] ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($store['meta_title'] ?: $store['name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $themeUrl ?>/css/style.css">
    <?php $ts = json_decode($store['theme_settings'] ?? '{}', true) ?: []; ?>
    <style>:root{--primary:<?= e($ts['primary_color'] ?? '#ff6b6b') ?>;--accent:<?= e($ts['accent_color'] ?? '#feca57') ?>;}</style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="/shop/<?= e($store['slug']) ?>">
            <?php if($store['logo']): ?><img src="<?= e($store['logo']) ?>" height="40"><?php else: ?><?= e($store['name']) ?><?php endif; ?>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto ms-4">
                <li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>/products">Shop</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link icon-btn" href="/shop/<?= e($store['slug']) ?>/cart"><i class="bi bi-bag fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link icon-btn" href="<?= isAuth()?'/account':'/login' ?>"><i class="bi bi-person fs-5"></i></a></li>
            </ul>
        </div>
    </div>
</nav>
<?php include VIEWS_PATH . '/partials/flash.php'; ?>
<?= $content ?>
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container text-center">
        <h4 class="fw-bold"><?= e($store['name']) ?></h4>
        <p class="text-white-50">&copy; <?= date('Y') ?> All rights reserved</p>
    </div>
</footer>
<script>window.STORE_BASE = '<?= BASE_PATH ?>/shop/<?= e($store['slug']) ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $themeUrl ?>/js/script.js"></script>
</body></html>
