<h2 class="mb-3 text-center fw-bold">Forgot Password</h2>
<p class="text-muted text-center mb-4">Enter your email to receive a reset link</p>
<form method="POST" action="/forgot-password">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control form-control-lg" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Send Reset Link</button>
    <p class="text-center mt-3"><a href="/login">← Back to login</a></p>
</form>
