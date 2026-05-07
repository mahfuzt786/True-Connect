<?php $u = auth(); $isImpersonating = ($u['role'] ?? '') === 'super_admin' && Session::get('super_admin_active_store_id'); ?>
<?php if ($isImpersonating && isset($store) && $store): ?>
    <div class="bg-warning-subtle border-bottom border-warning px-4 py-2 d-flex justify-content-between align-items-center">
        <div class="small">
            <i class="bi bi-shield-exclamation text-warning me-1"></i>
            You're viewing <strong><?= e($store['name']) ?></strong> as super admin. Changes you make affect this store.
        </div>
        <form method="POST" action="/admin/stores/leave-impersonation" class="d-inline">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-dark"><i class="bi bi-box-arrow-left me-1"></i>Leave store</button>
        </form>
    </div>
<?php endif; ?>
<nav class="navbar navbar-light bg-white border-bottom px-4">
    <div class="d-flex w-100 justify-content-between align-items-center">
        <div>
            <?php if (isset($store) && $store): ?>
                <a href="/shop/<?= e($store['slug']) ?>" target="_blank" class="text-decoration-none">
                    <i class="bi bi-shop"></i> <?= e($store['name']) ?> <i class="bi bi-arrow-up-right-square small"></i>
                </a>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="/dashboard/notifications" class="text-dark position-relative">
                <i class="bi bi-bell fs-5"></i>
            </a>
            <div class="dropdown">
                <a class="dropdown-toggle text-dark text-decoration-none" data-bs-toggle="dropdown" href="#">
                    <?php if (!empty($u['avatar'])): ?>
                        <img src="<?= e($u['avatar']) ?>" class="rounded-circle me-2" width="32" height="32">
                    <?php else: ?>
                        <i class="bi bi-person-circle fs-4 me-1"></i>
                    <?php endif; ?>
                    <?= e($u['name']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/dashboard/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="/subscription"><i class="bi bi-stars me-2"></i>Subscription</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="/logout" class="d-inline">
                            <?= csrf_field() ?>
                            <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
