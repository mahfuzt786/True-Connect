<?php
$path = $_SERVER['REQUEST_URI'];
$active = fn($prefix) => str_starts_with($path, $prefix) ? 'active' : '';
$user = auth();
$role = $user['role'] ?? '';
?>
<aside class="sidebar bg-dark text-white" style="width:260px;min-height:100vh;position:sticky;top:0;">
    <div class="p-3 border-bottom border-secondary">
        <a href="/dashboard" class="d-block">
            <img src="<?= asset('images/logo-white.svg') ?>" alt="<?= e(config('app.name')) ?>" style="height:38px;">
        </a>
    </div>
    <nav class="p-2">
        <?php if ($role === 'super_admin'): ?>
            <a href="/admin" class="nav-link text-white <?= $active('/admin') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="/admin/stores" class="nav-link text-white <?= $active('/admin/stores') ?>"><i class="bi bi-shop me-2"></i>Stores</a>
            <a href="/admin/users" class="nav-link text-white <?= $active('/admin/users') ?>"><i class="bi bi-people me-2"></i>Users</a>
            <a href="/admin/subscriptions" class="nav-link text-white <?= $active('/admin/subscriptions') ?>"><i class="bi bi-credit-card me-2"></i>Subscriptions</a>
            <a href="/admin/plans" class="nav-link text-white <?= $active('/admin/plans') ?>"><i class="bi bi-stars me-2"></i>Plans</a>
            <a href="/admin/revenue" class="nav-link text-white <?= $active('/admin/revenue') ?>"><i class="bi bi-cash-stack me-2"></i>Revenue</a>
            <a href="/admin/analytics" class="nav-link text-white <?= $active('/admin/analytics') ?>"><i class="bi bi-graph-up me-2"></i>Analytics</a>
            <a href="/admin/audit-log" class="nav-link text-white <?= $active('/admin/audit') ?>"><i class="bi bi-shield-check me-2"></i>Audit Log</a>
            <a href="/admin/settings" class="nav-link text-white <?= $active('/admin/settings') ?>"><i class="bi bi-gear me-2"></i>Settings</a>
        <?php elseif ($role === 'vendor'): ?>
            <a href="/vendor/dashboard" class="nav-link text-white <?= $active('/vendor/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="/vendor/products" class="nav-link text-white <?= $active('/vendor/products') ?>"><i class="bi bi-box me-2"></i>My Products</a>
            <a href="/vendor/orders" class="nav-link text-white <?= $active('/vendor/orders') ?>"><i class="bi bi-receipt me-2"></i>Orders</a>
            <a href="/vendor/earnings" class="nav-link text-white <?= $active('/vendor/earnings') ?>"><i class="bi bi-wallet2 me-2"></i>Earnings</a>
            <a href="/vendor/settings" class="nav-link text-white <?= $active('/vendor/settings') ?>"><i class="bi bi-gear me-2"></i>Settings</a>
        <?php else: ?>
            <a href="/dashboard" class="nav-link text-white <?= $active('/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="/products" class="nav-link text-white <?= $active('/products') ?>"><i class="bi bi-box me-2"></i>Products</a>
            <a href="/categories" class="nav-link text-white <?= $active('/categories') ?>"><i class="bi bi-tags me-2"></i>Categories</a>
            <a href="/orders" class="nav-link text-white <?= $active('/orders') ?>"><i class="bi bi-receipt me-2"></i>Orders</a>
            <a href="/customers" class="nav-link text-white <?= $active('/customers') ?>"><i class="bi bi-people me-2"></i>Customers</a>
            <a href="/coupons" class="nav-link text-white <?= $active('/coupons') ?>"><i class="bi bi-ticket-perforated me-2"></i>Coupons</a>
            <a href="/reviews" class="nav-link text-white <?= $active('/reviews') ?>"><i class="bi bi-star me-2"></i>Reviews</a>
            <a href="/vendors" class="nav-link text-white <?= $active('/vendors') ?>"><i class="bi bi-shop-window me-2"></i>Vendors</a>
            <a href="/analytics" class="nav-link text-white <?= $active('/analytics') ?>"><i class="bi bi-graph-up me-2"></i>Analytics</a>
            <hr class="border-secondary">
            <a href="/store/settings" class="nav-link text-white <?= $active('/store/settings') ?>"><i class="bi bi-gear me-2"></i>Store Settings</a>
            <a href="/store/shipping" class="nav-link text-white <?= $active('/store/shipping') ?>"><i class="bi bi-truck me-2"></i>Shipping</a>
            <a href="/store/tax" class="nav-link text-white <?= $active('/store/tax') ?>"><i class="bi bi-percent me-2"></i>Tax</a>
            <a href="/store/payments" class="nav-link text-white <?= $active('/store/payments') ?>"><i class="bi bi-credit-card me-2"></i>Payments</a>
            <a href="/store/pages" class="nav-link text-white <?= $active('/store/pages') ?>"><i class="bi bi-file-earmark me-2"></i>Pages</a>
            <a href="/theme" class="nav-link text-white <?= $active('/theme') ?>"><i class="bi bi-palette me-2"></i>Theme</a>
            <a href="/subscription" class="nav-link text-white <?= $active('/subscription') ?>"><i class="bi bi-stars me-2"></i>Subscription</a>
        <?php endif; ?>
    </nav>
    <div class="px-3 py-3 mt-auto small text-white-50 border-top border-secondary sidebar-footer">
        Developed by
        <a href="https://truecircle.in/" target="_blank" rel="noopener" class="text-white text-decoration-none fw-semibold">truecircle.in</a>
    </div>
</aside>
<style>
.sidebar { display: flex; flex-direction: column; }
.sidebar .nav-link { padding: 10px 15px; border-radius: 6px; margin-bottom: 2px; transition: all .2s; }
.sidebar .nav-link:hover { background: rgba(255,255,255,.1); }
.sidebar .nav-link.active { background: #0d6efd; }
.sidebar-footer { background: rgba(0,0,0,.2); }
</style>
