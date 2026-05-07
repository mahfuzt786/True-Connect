<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#0d6efd;padding:30px;text-align:center;color:#fff;"><h2>Password Reset</h2></div>
    <div style="padding:30px;">
        <p>Hi <?= e($user['name']) ?>,</p>
        <p>You requested a password reset. Click the button below to set a new password:</p>
        <p style="text-align:center;margin:25px 0;"><a href="<?= e($link) ?>" style="background:#0d6efd;color:#fff;padding:12px 25px;text-decoration:none;border-radius:6px;">Reset Password</a></p>
        <p style="color:#666;font-size:14px;">This link expires in 1 hour. If you didn't request this, ignore this email.</p>
    </div>
</div></body></html>
