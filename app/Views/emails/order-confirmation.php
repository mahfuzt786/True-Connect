<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#198754;padding:30px;text-align:center;color:#fff;"><h2>Order Confirmed!</h2></div>
    <div style="padding:30px;">
        <p>Hi <?= e($user['name']) ?>,</p>
        <p>Thank you for your order! Your order <strong>#<?= e($order['order_number']) ?></strong> has been received and is being processed.</p>
        <table style="width:100%;background:#f8f9fa;padding:15px;border-radius:6px;margin:20px 0;">
            <tr><td><strong>Order Number:</strong></td><td><?= e($order['order_number']) ?></td></tr>
            <tr><td><strong>Total:</strong></td><td><?= money($order['total'], $order['currency']) ?></td></tr>
            <tr><td><strong>Status:</strong></td><td><?= ucfirst($order['status']) ?></td></tr>
        </table>
        <p>You will receive another email when your order ships.</p>
    </div>
</div></body></html>
