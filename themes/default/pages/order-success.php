<div class="container py-5 text-center">
<i class="bi bi-check-circle text-success" style="font-size:80px;"></i>
<h1 class="mt-3">Thank You!</h1>
<p class="lead">Your order #<?= e($order['order_number']) ?> has been placed.</p>
<p>Total: <strong><?= money($order['total'], $order['currency']) ?></strong></p>
<a href="/shop/<?= e($store['slug']) ?>" class="btn btn-primary">Continue Shopping</a>
<a href="/account/orders" class="btn btn-outline-primary">View Order</a>
</div>
