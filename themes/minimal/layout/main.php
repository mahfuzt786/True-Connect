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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $themeUrl ?>/css/style.css">
    <?php $ts = json_decode($store['theme_settings'] ?? '{}', true) ?: []; ?>
    <style>:root{--primary:<?= e($ts['primary_color'] ?? '#000000') ?>;}</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom">
    <div class="container">
        <a class="navbar-brand fw-light" href="/shop/<?= e($store['slug']) ?>"><?= e($store['name']) ?></a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>/products">Shop</a></li>
                <?php if($store['type']==='marketplace'): ?><li class="nav-item"><a class="nav-link" href="/shop/<?= e($store['slug']) ?>/vendors">Sellers</a></li><?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li><a class="nav-link" href="/shop/<?= e($store['slug']) ?>/cart"><i class="bi bi-bag"></i></a></li>
                <li><a class="nav-link" href="<?= isAuth()?'/account':'/login' ?>"><i class="bi bi-person"></i></a></li>
            </ul>
        </div>
    </div>
</nav>
<?php include VIEWS_PATH . '/partials/flash.php'; ?>
<?= $content ?>
<footer class="border-top py-4 mt-5">
    <div class="container text-center text-muted small">&copy; <?= date('Y') ?> <?= e($store['name']) ?></div>
</footer>
<script>window.STORE_BASE = '<?= BASE_PATH ?>/shop/<?= e($store['slug']) ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $themeUrl ?>/js/script.js"></script>
</body></html>
