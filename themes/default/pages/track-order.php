<div class="container py-5">
<h2>Track Order #<?= e($order['order_number']) ?></h2>
<p>Status: <strong><?= e($order['status']) ?></strong></p>
<?php if($order['tracking_number']): ?>
<p>Tracking: <strong><?= e($order['tracking_number']) ?></strong>
<?php if($order['tracking_url']): ?> — <a href="<?= e($order['tracking_url']) ?>" target="_blank">Track on carrier site</a><?php endif; ?>
</p>
<?php endif; ?>
<div class="card p-3 mt-3">
<h5>Status History</h5>
<ul class="list-unstyled">
<?php foreach ($history as $h): ?>
    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong><?= e($h['status']) ?></strong> — <small><?= formatDateTime($h['created_at']) ?></small><?php if($h['comment']): ?><br><small class="text-muted"><?= e($h['comment']) ?></small><?php endif; ?></li>
<?php endforeach; ?>
</ul>
</div>
</div>
