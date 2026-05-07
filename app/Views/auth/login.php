<h2 class="mb-1 fw-bold text-center">Welcome back</h2>
<p class="text-muted text-center mb-4">Sign in to your account</p>
<form method="POST" action="/login">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" value="<?= e(old('email')) ?>" class="form-control form-control-lg" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
            <input type="password" name="password" id="password" class="form-control form-control-lg" required>
            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1" aria-label="Show password">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-between mb-3">
        <div class="form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1">
            <label for="remember" class="form-check-label">Remember me</label>
        </div>
        <a href="/forgot-password" class="text-decoration-none">Forgot password?</a>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Sign In</button>
    <div class="text-center my-3 text-muted">or</div>
    <div class="d-grid gap-2">
        <a href="/auth/google" class="btn btn-outline-dark"><i class="bi bi-google me-2"></i>Continue with Google</a>
        <a href="/auth/facebook" class="btn btn-outline-primary"><i class="bi bi-facebook me-2"></i>Continue with Facebook</a>
    </div>
    <p class="text-center mt-4 mb-0">
        Don't have an account? <a href="/register" class="text-decoration-none">Sign up</a>
    </p>
</form>
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        icon.classList.toggle('bi-eye', showing);
        icon.classList.toggle('bi-eye-slash', !showing);
        this.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    });
</script>
