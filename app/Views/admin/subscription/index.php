<h2>Subscription</h2>
<?php if ($sub): ?>
<div class="row g-3">
    <div class="col-md-8">
        <div class="card p-4">
            <h4><?= e($sub['plan_name']) ?> <span class="badge bg-<?= $sub['status']==='active'?'success':($sub['status']==='trialing'?'info':'warning') ?>"><?= e($sub['status']) ?></span></h4>
            <p class="mb-2"><strong>Billing:</strong> <?= money($sub['amount'], $sub['currency']) ?> / <?= e($sub['billing_cycle']) ?></p>
            <p class="mb-2"><strong>Period:</strong> <?= formatDate($sub['current_period_start']) ?> → <?= formatDate($sub['current_period_end']) ?></p>
            <?php if ($sub['trial_ends_at'] && $sub['status']==='trialing'): ?>
                <p><strong>Trial ends:</strong> <?= formatDate($sub['trial_ends_at']) ?></p>
            <?php endif; ?>
            <div class="mt-3">
                <a href="/subscription/plans" class="btn btn-primary">Change Plan</a>
                <form method="POST" action="/subscription/cancel" class="d-inline" onsubmit="return confirm('Cancel subscription?')"><?= csrf_field() ?>
                    <button class="btn btn-outline-danger">Cancel Subscription</button></form>
            </div>
        </div>
        <div class="card p-4 mt-3">
            <h5>Recent Invoices</h5>
            <table class="table"><thead><tr><th>Invoice#</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
            <?php foreach ($invoices as $inv): ?>
                <tr><td><?= e($inv['invoice_number']) ?></td><td><?= formatDate($inv['created_at']) ?></td>
                <td><?= money($inv['amount'], $inv['currency']) ?></td>
                <td><span class="badge bg-<?= $inv['status']==='paid'?'success':'warning' ?>"><?= e($inv['status']) ?></span></td>
                <td><a href="/subscription/invoices/<?= $inv['id'] ?>/download" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <h5>Usage</h5>
            <div class="mb-2">Products: <strong><?= $usage['products'] ?></strong> / <?= $sub['products_limit'] ?? '∞' ?></div>
            <div class="mb-2">Vendors: <strong><?= $usage['vendors'] ?></strong> / <?= $sub['vendors_limit'] ?? '∞' ?></div>
            <div>Orders (30d): <strong><?= $usage['orders'] ?></strong></div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">No active subscription. <a href="/subscription/plans">Choose a plan →</a></div>
<?php endif; ?>
