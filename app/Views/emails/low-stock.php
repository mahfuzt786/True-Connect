<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#dc3545;padding:30px;text-align:center;color:#fff;"><h2>Low Stock Alert</h2></div>
    <div style="padding:30px;">
        <p>The product <strong><?= e($product['name']) ?></strong> is running low on stock (only <?= $product['quantity'] ?> left).</p>
        <p>Consider restocking soon to avoid running out.</p>
    </div>
</div></body></html>
