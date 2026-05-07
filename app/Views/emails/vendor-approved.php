<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#198754;padding:30px;text-align:center;color:#fff;"><h2>You're approved! 🎉</h2></div>
    <div style="padding:30px;">
        <p>Hi <?= e($user['name']) ?>,</p>
        <p>Great news! Your vendor application for <strong><?= e($vendor['business_name']) ?></strong> has been approved.</p>
        <p>You can now start adding products and selling on our marketplace.</p>
        <p style="text-align:center;margin:25px 0;"><a href="<?= url('/vendor/dashboard') ?>" style="background:#198754;color:#fff;padding:12px 25px;text-decoration:none;border-radius:6px;">Go to Vendor Dashboard</a></p>
    </div>
</div></body></html>
