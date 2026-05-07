<!DOCTYPE html>
<html lang="<?= e(config('app.locale','en')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <?= seo_meta(['title' => $title ?? null, 'robots' => 'noindex,nofollow']) ?>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    <?php include VIEWS_PATH . '/partials/lockdown.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
</head>
<body>
<?php if (isAuth()): ?>
<div class="d-flex">
    <?php include VIEWS_PATH . '/partials/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <?php include VIEWS_PATH . '/partials/topbar.php'; ?>
        <div class="container-fluid p-4">
            <?php include VIEWS_PATH . '/partials/flash.php'; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php else: ?>
    <?= $content ?>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
