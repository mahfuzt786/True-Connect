<h2 class="mb-3">Choose a Plan</h2>
<?php if ($expired): ?><div class="alert alert-danger">Your subscription has expired. Please choose a plan to continue.</div><?php endif; ?>

<div class="row g-4">
<?php foreach ($plans as $plan): ?>
    <div class="col-md-3 d-flex">
        <div class="card w-100 plan-subscribe-card <?= $plan['slug']==='pro' ? 'border-primary' : '' ?>">
            <div class="card-body text-center d-flex flex-column">
                <h4 class="fw-bold"><?= e($plan['name']) ?></h4>
                <div class="my-3">
                    <span class="display-5 fw-bold"><?= money($plan['price_monthly']) ?></span>
                    <small class="text-muted">/mo</small>
                </div>
                <ul class="list-unstyled text-start small flex-grow-1">
                    <?php foreach ((json_decode($plan['features'] ?? '[]', true) ?: []) as $f): ?>
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i><?= e($f) ?></li>
                    <?php endforeach; ?>
                </ul>
                <form method="POST" action="/subscription/subscribe/<?= (int)$plan['id'] ?>" class="mt-auto">
                    <?= csrf_field() ?>
                    <input type="hidden" name="cycle" value="monthly">
                    <button class="btn btn-primary w-100">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
