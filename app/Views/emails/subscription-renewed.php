<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#0d6efd;padding:30px;text-align:center;color:#fff;"><h2>Subscription Renewed</h2></div>
    <div style="padding:30px;">
        <p>Hi <?= e($user['name']) ?>,</p>
        <p>Your subscription has been successfully renewed. Thank you for continuing with us!</p>
        <p>Next billing date: <strong><?= formatDate($subscription['current_period_end']) ?></strong></p>
    </div>
</div></body></html>
