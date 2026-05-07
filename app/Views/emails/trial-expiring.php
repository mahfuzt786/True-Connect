<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#ffc107;padding:30px;text-align:center;color:#000;"><h2>Trial ending in <?= $daysLeft ?> days</h2></div>
    <div style="padding:30px;">
        <p>Hi <?= e($user['name']) ?>,</p>
        <p>Your free trial expires in <strong><?= $daysLeft ?> day<?= $daysLeft > 1 ? 's' : '' ?></strong>. Subscribe now to keep your store running.</p>
        <p style="text-align:center;margin:25px 0;"><a href="<?= url('/subscription/plans') ?>" style="background:#0d6efd;color:#fff;padding:12px 25px;text-decoration:none;border-radius:6px;">Choose a Plan</a></p>
    </div>
</div></body></html>
