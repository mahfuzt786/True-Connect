<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#198754;padding:30px;text-align:center;color:#fff;"><h2>New Order!</h2></div>
    <div style="padding:30px;">
        <p>You have a new order!</p>
        <p>Order Number: <strong>#<?= e($order['order_number']) ?></strong></p>
        <p>Total: <strong><?= money($order['total'], $order['currency']) ?></strong></p>
        <p style="text-align:center;margin:20px 0;"><a href="<?= url("/orders/{$order['id']}") ?>" style="background:#0d6efd;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;">View Order</a></p>
    </div>
</div></body></html>
