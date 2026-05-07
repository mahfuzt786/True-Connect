<h2 class="mb-1 fw-bold text-center">Create your account</h2>
<p class="text-muted text-center mb-4">Shop from any store on the platform</p>
<form method="POST" action="/register" id="registerForm" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" value="<?= e(old('name')) ?>" class="form-control form-control-lg" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" value="<?= e(old('email')) ?>" class="form-control form-control-lg" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
            <input type="password" name="password" id="password" class="form-control form-control-lg"
                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}"
                   title="At least 8 characters with uppercase, lowercase and a number"
                   required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1" aria-label="Show password">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
        </div>

        <div class="progress mt-2" style="height: 6px;">
            <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
        <small id="strengthLabel" class="text-muted d-block mt-1">Enter a password</small>

        <ul class="list-unstyled small mt-2 mb-0" id="pwRules">
            <li id="rule-length"><i class="bi bi-circle text-muted"></i> At least 8 characters</li>
            <li id="rule-upper"><i class="bi bi-circle text-muted"></i> One uppercase letter (A-Z)</li>
            <li id="rule-lower"><i class="bi bi-circle text-muted"></i> One lowercase letter (a-z)</li>
            <li id="rule-number"><i class="bi bi-circle text-muted"></i> One number (0-9)</li>
            <li id="rule-symbol" class="text-muted"><i class="bi bi-circle text-muted"></i> One symbol <span class="text-muted">(optional, recommended)</span></li>
        </ul>
    </div>
    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary" id="toggleConfirm" tabindex="-1" aria-label="Show password">
                <i class="bi bi-eye" id="toggleConfirmIcon"></i>
            </button>
        </div>
        <small id="matchLabel" class="form-text"></small>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="terms" value="1" id="terms" class="form-check-input" required>
        <label for="terms" class="form-check-label">I agree to the Terms of Service and Privacy Policy</label>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg" id="submitBtn">Create Account</button>
    <div class="text-center small text-muted mt-3">
        Want to sell on the platform?
        <a href="/sell/register" class="text-decoration-none fw-semibold">Become a seller →</a>
    </div>
    <p class="text-center mt-4 mb-0">
        Already have an account? <a href="/login" class="text-decoration-none">Sign in</a>
    </p>
</form>

<script>
(function () {
    const pw      = document.getElementById('password');
    const confirm = document.getElementById('password_confirmation');
    const bar     = document.getElementById('strengthBar');
    const label   = document.getElementById('strengthLabel');
    const matchEl = document.getElementById('matchLabel');
    const form    = document.getElementById('registerForm');

    function setRule(id, ok) {
        const li = document.getElementById('rule-' + id);
        const icon = li.querySelector('i');
        icon.className = ok ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted';
        li.classList.toggle('text-success', ok);
        li.classList.toggle('text-muted', !ok);
    }

    function evaluate() {
        const v = pw.value;
        const checks = {
            length: v.length >= 8,
            upper:  /[A-Z]/.test(v),
            lower:  /[a-z]/.test(v),
            number: /\d/.test(v),
            symbol: /[^A-Za-z0-9]/.test(v),
        };
        Object.entries(checks).forEach(([k, ok]) => setRule(k, ok));

        const score = Object.values(checks).filter(Boolean).length;
        const levels = [
            { w: 0,   c: 'bg-secondary', t: 'Enter a password' },
            { w: 20,  c: 'bg-danger',    t: 'Very weak' },
            { w: 40,  c: 'bg-danger',    t: 'Weak' },
            { w: 60,  c: 'bg-warning',   t: 'Fair' },
            { w: 80,  c: 'bg-info',      t: 'Good' },
            { w: 100, c: 'bg-success',   t: 'Strong' },
        ];
        const lvl = levels[v.length === 0 ? 0 : score];
        bar.style.width = lvl.w + '%';
        bar.className = 'progress-bar ' + lvl.c;
        label.textContent = lvl.t;

        checkMatch();
    }

    function checkMatch() {
        if (!confirm.value) {
            matchEl.textContent = '';
            matchEl.className = 'form-text';
            confirm.setCustomValidity('');
            return;
        }
        if (confirm.value === pw.value) {
            matchEl.textContent = 'Passwords match';
            matchEl.className = 'form-text text-success';
            confirm.setCustomValidity('');
        } else {
            matchEl.textContent = 'Passwords do not match';
            matchEl.className = 'form-text text-danger';
            confirm.setCustomValidity('Passwords do not match');
        }
    }

    pw.addEventListener('input', evaluate);
    confirm.addEventListener('input', checkMatch);

    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            form.classList.add('was-validated');
        }
    });

    function bindToggle(btnId, iconId, inputEl) {
        document.getElementById(btnId).addEventListener('click', function () {
            const showing = inputEl.type === 'text';
            inputEl.type = showing ? 'password' : 'text';
            const icon = document.getElementById(iconId);
            icon.classList.toggle('bi-eye', showing);
            icon.classList.toggle('bi-eye-slash', !showing);
            this.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    }
    bindToggle('togglePassword', 'togglePasswordIcon', pw);
    bindToggle('toggleConfirm',  'toggleConfirmIcon',  confirm);
})();
</script>
