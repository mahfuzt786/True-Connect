<div class="d-flex justify-content-between mb-3"><h2>My Addresses</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddr">Add Address</button></div>
<div class="row g-3">
<?php foreach ($addresses as $a): ?>
<div class="col-md-4"><div class="card p-3 <?= $a['is_default']?'border-primary':'' ?>">
    <?php if($a['is_default']): ?><span class="badge bg-primary mb-2 align-self-start">Default</span><?php endif; ?>
    <strong><?= e($a['first_name'].' '.$a['last_name']) ?></strong>
    <p class="mb-1"><?= e($a['address_line1']) ?><br><?= e($a['city']) ?>, <?= e($a['state']) ?> <?= e($a['zip_code']) ?><br><?= e($a['country']) ?></p>
    <p class="mb-2"><?= e($a['phone']) ?></p>
    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete?'))fetch('/account/addresses/<?= $a['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button>
</div></div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="addAddr"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="/account/addresses"><?= csrf_field() ?>
<div class="modal-header"><h5>Add Address</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="row g-2">
    <div class="col-6"><input name="first_name" class="form-control" placeholder="First Name" required></div>
    <div class="col-6"><input name="last_name" class="form-control" placeholder="Last Name" required></div>
    <div class="col-12"><input name="address_line1" class="form-control" placeholder="Street Address" required></div>
    <div class="col-6"><input name="city" class="form-control" placeholder="City" required></div>
    <div class="col-6"><input name="state" class="form-control" placeholder="State"></div>
    <div class="col-6"><input name="country" class="form-control" placeholder="Country" required></div>
    <div class="col-6"><input name="zip_code" class="form-control" placeholder="ZIP" required></div>
    <div class="col-12"><input name="phone" class="form-control" placeholder="Phone"></div>
</div></div>
<div class="modal-footer"><button class="btn btn-primary">Save</button></div>
</form></div></div></div>
