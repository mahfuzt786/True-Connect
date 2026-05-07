<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
    <div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.05);">
        <div style="background:linear-gradient(135deg,#667eea,#764ba2);padding:40px;text-align:center;color:#fff;">
            <h1 style="margin:0;"><?= e(config('app.name')) ?></h1>
        </div>
        <div style="padding:40px;">
            <h2>Hi <?= e($user['name']) ?>,</h2>
            <p>Welcome to <?= e(config('app.name')) ?>! Please verify your email address to activate your account.</p>
            <p style="text-align:center;margin:30px 0;">
                <a href="<?= e($link) ?>" style="background:#0d6efd;color:#fff;padding:12px 30px;text-decoration:none;border-radius:6px;display:inline-block;">Verify Email</a>
            </p>
            <p style="color:#666;font-size:14px;">Or copy this link: <?= e($link) ?></p>
            <p style="color:#666;font-size:14px;">If you didn't create this account, you can safely ignore this email.</p>
        </div>
        <div style="background:#f8f9fa;padding:20px;text-align:center;color:#999;font-size:12px;">
            &copy; <?= date('Y') ?> <?= e(config('app.name')) ?>
        </div>
    </div>
</body></html>
