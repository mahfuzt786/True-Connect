<h2>My Account</h2>
<p>Welcome back, <?= e($user['name']) ?>!</p>
<div class="row g-3 mt-3">
    <div class="col-md-3"><a href="/account/orders" class="card p-4 text-center text-decoration-none"><i class="bi bi-receipt fs-1"></i><div class="mt-2">Orders</div></a></div>
    <div class="col-md-3"><a href="/account/wishlist" class="card p-4 text-center text-decoration-none"><i class="bi bi-heart fs-1"></i><div class="mt-2">Wishlist</div></a></div>
    <div class="col-md-3"><a href="/account/addresses" class="card p-4 text-center text-decoration-none"><i class="bi bi-geo-alt fs-1"></i><div class="mt-2">Addresses</div></a></div>
    <div class="col-md-3"><a href="/account/profile" class="card p-4 text-center text-decoration-none"><i class="bi bi-person fs-1"></i><div class="mt-2">Profile</div></a></div>
</div>
