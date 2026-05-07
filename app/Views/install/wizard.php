<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Install <?= e(config('app.name')) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>body{background:#f8f9fa;}.wizard{max-width:700px;margin:50px auto;}.step-circle{width:32px;height:32px;border-radius:50%;background:#dee2e6;color:#000;display:inline-flex;align-items:center;justify-content:center;font-weight:bold;}.step-circle.active{background:#0d6efd;color:#fff;}</style>
</head><body>
<script>window.BASE_PATH = <?= json_encode(defined('BASE_PATH') ? BASE_PATH : '') ?>;</script>
<div class="wizard">
<h1 class="text-center mb-4"><?= e(config('app.name', 'True Commerce')) ?> Installer</h1>
<p class="text-center text-muted small mb-4">Developed by <a href="https://truecircle.in/" target="_blank" rel="noopener">truecircle.in</a></p>
<div class="d-flex justify-content-between mb-4">
    <?php foreach (['Requirements','Database','Migrate','Admin'] as $i => $name): ?>
        <div class="text-center"><div class="step-circle <?= $step >= $i+1 ? 'active' : '' ?>"><?= $i+1 ?></div><div class="small mt-1"><?= $name ?></div></div>
    <?php endforeach; ?>
</div>

<div class="card p-4">
<?php if ($step === 1): ?>
    <h3>System Requirements</h3>
    <table class="table">
        <?php $allRequiredOk = true; foreach ($checks as $name => $check): if (!$check['ok'] && $check['required']) $allRequiredOk = false; ?>
            <tr>
                <td>
                    <?= e($name) ?>
                    <?php if (!$check['required']): ?><small class="text-muted ms-2">(optional)</small><?php endif; ?>
                </td>
                <td class="text-end">
                    <?php if ($check['ok']): ?>
                        <span class="badge bg-success">OK</span>
                    <?php elseif ($check['required']): ?>
                        <span class="badge bg-danger">MISSING</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">RECOMMENDED</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if ($allRequiredOk): ?>
        <a href="?step=2" class="btn btn-primary">Continue →</a>
    <?php else: ?>
        <div class="alert alert-danger mt-3">Please install the missing required extensions before continuing.</div>
        <a href="?step=1" class="btn btn-secondary">Re-check</a>
    <?php endif; ?>

<?php elseif ($step === 2): ?>
    <h3>Database Configuration</h3>
    <p>Edit your <code>.env</code> file (in the project root) and update <code>DB_HOST</code>, <code>DB_DATABASE</code>, <code>DB_USERNAME</code>, <code>DB_PASSWORD</code>. Then click below to test.</p>
    <p class="text-muted small">Make sure the database <code><?= e(env('DB_DATABASE', 'saas_ecommerce')) ?></code> exists in MySQL — create it via phpMyAdmin if needed.</p>
    <button class="btn btn-primary" id="testBtn" onclick="test()">Test Connection</button>
    <div id="result" class="mt-3"></div>

<?php elseif ($step === 3): ?>
    <h3>Run Migrations</h3>
    <p>This will create all required database tables and seed initial data (plans, default settings).</p>
    <button class="btn btn-primary" id="migrateBtn" onclick="migrate()">Run Migrations</button>
    <div id="result" class="mt-3"></div>

<?php elseif ($step === 4): ?>
    <h3>Create Super Admin Account</h3>
    <p class="text-muted small">This will overwrite the default <code>admin@saas.com</code> account.</p>
    <form id="adminForm">
        <div class="mb-3"><label>Name</label><input name="name" class="form-control" value="Super Admin" required></div>
        <div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input name="password" type="password" class="form-control" required minlength="8"></div>
        <button type="submit" class="btn btn-primary">Finish Installation</button>
    </form>
    <div id="result" class="mt-3"></div>
<?php endif; ?>
</div>
</div>

<script>
function url(path) { return (window.BASE_PATH || '') + path; }

async function callStep(step, body, btn) {
    const result = document.getElementById('result');
    result.innerHTML = '<div class="text-muted">⏳ Working...</div>';
    if (btn) { btn.disabled = true; btn.textContent = 'Processing...'; }
    try {
        const res = await fetch(url('/install/step/' + step), { method: 'POST', body: body || null });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) {
            result.innerHTML = '<div class="alert alert-danger"><strong>Server returned non-JSON response (HTTP ' + res.status + '):</strong><pre style="max-height:200px;overflow:auto;background:#f8d7da;padding:10px;margin-top:10px;font-size:12px;">' + escapeHtml(text.substring(0, 2000)) + '</pre></div>';
            return null;
        }
        return { res, data };
    } catch (e) {
        result.innerHTML = '<div class="alert alert-danger"><strong>Network error:</strong> ' + escapeHtml(e.message) + '</div>';
        return null;
    } finally {
        if (btn) { btn.disabled = false; }
    }
}
function escapeHtml(s) { return String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

async function test() {
    const btn = document.getElementById('testBtn');
    btn.textContent = 'Test Connection';
    const r = await callStep(2, null, btn);
    if (!r) return;
    const result = document.getElementById('result');
    if (r.data.success) {
        result.innerHTML = '<div class="alert alert-success">✓ ' + (r.data.message || 'Connected!') + '</div><a href="?step=3" class="btn btn-primary">Continue →</a>';
    } else {
        result.innerHTML = '<div class="alert alert-danger"><strong>Connection failed:</strong><br>' + escapeHtml(r.data.error || 'Unknown error') + '</div><div class="mt-2 small text-muted">Check your <code>.env</code> file. Common issues:<br>• MySQL not running (start in XAMPP)<br>• Wrong host/port/credentials<br>• Database doesn\'t exist (create <code><?= e(env('DB_DATABASE', 'saas_ecommerce')) ?></code> in phpMyAdmin)</div>';
    }
}

async function migrate() {
    const btn = document.getElementById('migrateBtn');
    btn.textContent = 'Run Migrations';
    const r = await callStep(3, null, btn);
    if (!r) return;
    const result = document.getElementById('result');
    if (r.data.success) {
        result.innerHTML = '<div class="alert alert-success">✓ ' + (r.data.message || 'Done') + '</div><a href="?step=4" class="btn btn-primary">Continue →</a>';
    } else {
        result.innerHTML = '<div class="alert alert-danger"><strong>Migration failed:</strong><br><pre style="white-space:pre-wrap;">' + escapeHtml(r.data.error || '') + '</pre></div>';
    }
}

const adminForm = document.getElementById('adminForm');
if (adminForm) {
    adminForm.addEventListener('submit', async e => {
        e.preventDefault();
        const r = await callStep(4, new FormData(adminForm), e.submitter);
        if (!r) return;
        const result = document.getElementById('result');
        if (r.data.success) {
            result.innerHTML = '<div class="alert alert-success">✓ ' + (r.data.message || 'Installed!') + '</div><a href="' + url('/login') + '" class="btn btn-success">Go to Login →</a>';
        } else {
            result.innerHTML = '<div class="alert alert-danger">' + escapeHtml(r.data.error || '') + '</div>';
        }
    });
}
</script>
</body></html>
