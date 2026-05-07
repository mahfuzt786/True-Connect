<h2><?= e($vendor['business_name']) ?></h2>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Products</div><h4><?= $stats['products'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Orders</div><h4><?= $stats['orders'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Revenue</div><h4><?= money($stats['revenue']) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Balance</div><h4><?= money($vendor['balance']) ?></h4></div></div>
</div>
<div class="row g-3">
    <div class="col-md-8">
        <div class="card p-3 mb-3">
            <h5>Vendor Info</h5>
            <p>Email: <?= e($vendor['user_email']) ?><br>Phone: <?= e($vendor['phone']) ?><br>Status: <span class="badge bg-info"><?= e($vendor['status']) ?></span></p>
            <p><?= e($vendor['description']) ?></p>
        </div>
        <div class="card p-3">
            <h5>Recent Payouts</h5>
            <table class="table"><thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($payouts as $p): ?>
                <tr><td><?= formatDate($p['created_at']) ?></td><td><?= money($p['amount']) ?></td><td><?= e($p['method']) ?></td><td><?= e($p['status']) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
    <div class="col-md-4">
        <?php if ($vendor['status']==='pending'): ?>
        <div class="card p-3 mb-3">
            <form method="POST" action="/vendors/<?= $vendor['id'] ?>/approve"><?= csrf_field() ?>
                <button class="btn btn-success w-100">Approve Vendor</button></form>
            <form method="POST" action="/vendors/<?= $vendor['id'] ?>/reject" class="mt-2"><?= csrf_field() ?>
                <input name="reason" class="form-control mb-2" placeholder="Rejection reason"><button class="btn btn-danger w-100">Reject</button></form>
        </div>
        <?php elseif ($vendor['status']==='active'): ?>
        <div class="card p-3 mb-3">
            <form method="POST" action="/vendors/<?= $vendor['id'] ?>/payout"><?= csrf_field() ?>
                <h6>Process Payout</h6>
                <input type="number" step="0.01" name="amount" class="form-control mb-2" placeholder="Amount" max="<?= $vendor['balance'] ?>">
                <select name="method" class="form-select mb-2"><option>bank_transfer</option><option>paypal</option><option>manual</option></select>
                <button class="btn btn-primary w-100">Pay</button></form>
        </div>
        <form method="POST" action="/vendors/<?= $vendor['id'] ?>/suspend"><?= csrf_field() ?>
            <button class="btn btn-outline-warning w-100">Suspend Vendor</button></form>
        <?php endif; ?>
    </div>
</div>
