<h2>Edit Coupon</h2>
<form method="POST" action="/coupons/<?= $coupon['id'] ?>" class="card p-4">
    <?= csrf_field() ?>
    <div class="mb-2"><label>Code</label><input name="code" value="<?= e($coupon['code']) ?>" class="form-control"></div>
    <div class="row g-2 mb-2">
        <div class="col-6"><label>Type</label><select name="type" class="form-select">
            <?php foreach (['percentage','fixed','free_shipping'] as $t): ?><option value="<?= $t ?>" <?= $coupon['type']===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?>
        </select></div>
        <div class="col-6"><label>Value</label><input type="number" step="0.01" name="value" value="<?= $coupon['value'] ?>" class="form-control"></div>
    </div>
    <div class="form-check mb-3"><input type="checkbox" name="is_active" value="1" <?= $coupon['is_active']?'checked':'' ?> class="form-check-input" id="ia"><label for="ia">Active</label></div>
    <button class="btn btn-primary">Update</button>
</form>
