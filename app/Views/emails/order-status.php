<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#0d6efd;padding:30px;text-align:center;color:#fff;"><h2>Order Update</h2></div>
    <div style="padding:30px;">
        <p>Hi <?= e($user['name']) ?>,</p>
        <p>Your order <strong>#<?= e($order['order_number']) ?></strong> status has been updated to <strong><?= ucfirst(str_replace('_',' ',$status)) ?></strong>.</p>
        <?php if (!empty($note)): ?><p><em><?= e($note) ?></em></p><?php endif; ?>
        <?php if (!empty($order['tracking_number'])): ?>
            <p>Tracking Number: <strong><?= e($order['tracking_number']) ?></strong></p>
        <?php endif; ?>
    </div>
</div></body></html>
