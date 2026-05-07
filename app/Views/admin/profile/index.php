<h2>Profile</h2>
<div class="row g-3">
<div class="col-md-6">
<form method="POST" action="/dashboard/profile" class="card p-4">
    <?= csrf_field() ?>
    <h5>Personal Info</h5>
    <div class="mb-3"><label>Name</label><input name="name" value="<?= e($user['name']) ?>" class="form-control"></div>
    <div class="mb-3"><label>Email</label><input value="<?= e($user['email']) ?>" class="form-control" disabled></div>
    <div class="mb-3"><label>Phone</label><input name="phone" value="<?= e($user['phone'] ?? '') ?>" class="form-control"></div>
    <button class="btn btn-primary">Update</button>
</form>
</div>
<div class="col-md-6">
<form method="POST" action="/dashboard/profile/password" class="card p-4">
    <?= csrf_field() ?>
    <h5>Change Password</h5>
    <div class="mb-3"><label>Current Password</label><input type="password" name="current_password" class="form-control"></div>
    <div class="mb-3"><label>New Password</label><input type="password" name="password" class="form-control"></div>
    <div class="mb-3"><label>Confirm New Password</label><input type="password" name="password_confirmation" class="form-control"></div>
    <button class="btn btn-primary">Change Password</button>
</form>
<div class="card p-4 mt-3">
    <h5>Avatar</h5>
    <form method="POST" action="/dashboard/profile/avatar" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if($user['avatar']): ?><img src="<?= e($user['avatar']) ?>" class="rounded-circle mb-2" width="80" height="80"><?php endif; ?>
        <input type="file" name="avatar" class="form-control" accept="image/*">
        <button class="btn btn-primary mt-2">Upload</button>
    </form>
</div>
<div class="card p-4 mt-3">
    <h5>Two-Factor Auth</h5>
    <?php if ($user['two_factor_enabled']): ?>
        <p>2FA is enabled.</p>
        <form method="POST" action="/dashboard/profile/2fa/disable"><?= csrf_field() ?><button class="btn btn-outline-danger">Disable 2FA</button></form>
    <?php else: ?>
        <p>Add an extra layer of security to your account.</p>
        <button class="btn btn-primary" onclick="enable2FA()">Enable 2FA</button>
    <?php endif; ?>
</div>
</div>
</div>
<script>
function enable2FA() {
    fetch('/dashboard/profile/2fa/enable',{method:'POST',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}})
        .then(r=>r.json()).then(d=>{
            const code = prompt('Scan QR with authenticator and enter 6-digit code:\n\n' + d.qr_url);
            if (code) {
                const fd = new FormData();
                fd.append('code', code);
                fd.append('_csrf_token', document.querySelector('meta[name=csrf-token]').content);
                fetch('/dashboard/profile/2fa/verify',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
                    if (d.success) { alert('2FA enabled! Recovery codes:\n' + d.recovery_codes.join('\n')); location.reload(); }
                });
            }
        });
}
</script>
