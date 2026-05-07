<h2 class="mb-4 text-center fw-bold">Reset Password</h2>
<form method="POST" action="/reset-password">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control form-control-lg" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="password_confirmation" class="form-control form-control-lg" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Reset Password</button>
</form>
