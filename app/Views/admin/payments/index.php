<h2>Payment Methods</h2>
<div class="row g-3">
<?php
$gateways = [
    'stripe' => ['Stripe', 'bi-credit-card', ['public_key', 'secret_key']],
    'razorpay' => ['Razorpay', 'bi-credit-card-2-front', ['key_id', 'key_secret']],
    'paypal' => ['PayPal', 'bi-paypal', ['client_id', 'client_secret']],
    'cod' => ['Cash on Delivery', 'bi-cash', []],
    'bank_transfer' => ['Bank Transfer', 'bi-bank', ['bank_name', 'account_name', 'account_number']],
];
foreach ($gateways as $key => $g):
    $m = $byGateway[$key] ?? null;
    $creds = $m ? (json_decode($m['credentials'] ?? '{}', true) ?: []) : [];
?>
<div class="col-md-6">
<div class="card p-4">
    <div class="d-flex justify-content-between"><h5><i class="bi <?= $g[1] ?>"></i> <?= $g[0] ?></h5>
        <div class="form-check form-switch"><input type="checkbox" class="form-check-input" <?= ($m['is_enabled'] ?? 0)?'checked':'' ?> onchange="fetch('/store/payments/<?= $key ?>/toggle',{method:'POST',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}})"></div>
    </div>
    <form method="POST" action="/store/payments/<?= $key ?>"><?= csrf_field() ?>
        <div class="mb-2"><label>Display Name</label><input name="display_name" value="<?= e($m['display_name'] ?? $g[0]) ?>" class="form-control"></div>
        <?php foreach ($g[2] as $field): ?>
            <div class="mb-2"><label><?= ucwords(str_replace('_',' ',$field)) ?></label>
                <input name="<?= $field ?>" type="<?= str_contains($field,'secret')?'password':'text' ?>" value="<?= str_contains($field,'secret') ? '' : e($creds[$field] ?? '') ?>" class="form-control"></div>
        <?php endforeach; ?>
        <button class="btn btn-primary mt-2">Save</button>
    </form>
</div></div>
<?php endforeach; ?>
</div>
