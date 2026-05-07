<div class="d-flex justify-content-between mb-3"><h2>Shipping Zones</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addZone">Add Zone</button></div>
<?php foreach ($zones as $zone): ?>
<div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between"><h5><?= e($zone['name']) ?></h5>
        <button class="btn btn-sm btn-danger" onclick="if(confirm('Delete?'))fetch('/store/shipping/zone/<?= $zone['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button></div>
    <table class="table mt-2"><thead><tr><th>Name</th><th>Type</th><th>Rate</th><th>ETA</th><th></th></tr></thead><tbody>
    <?php foreach ($zone['rates'] as $rate): ?>
        <tr><td><?= e($rate['name']) ?></td><td><?= e($rate['type']) ?></td><td><?= money($rate['rate']) ?></td>
        <td><?= $rate['estimated_days_min'] ?>-<?= $rate['estimated_days_max'] ?> days</td>
        <td><button class="btn btn-sm btn-outline-danger" onclick="fetch('/store/shipping/rate/<?= $rate['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">×</button></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <form method="POST" action="/store/shipping/rate" class="row g-2 mt-2">
        <?= csrf_field() ?>
        <input type="hidden" name="zone_id" value="<?= $zone['id'] ?>">
        <div class="col"><input name="name" placeholder="Rate name" class="form-control" required></div>
        <div class="col"><select name="type" class="form-select"><option>flat</option><option>weight</option><option>price</option><option>free</option></select></div>
        <div class="col"><input type="number" step="0.01" name="rate" placeholder="Rate" class="form-control" required></div>
        <div class="col-auto"><button class="btn btn-primary">Add Rate</button></div>
    </form>
</div></div>
<?php endforeach; ?>

<div class="modal fade" id="addZone"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="/store/shipping/zone"><?= csrf_field() ?>
    <div class="modal-header"><h5>Add Zone</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label>Name</label><input name="name" class="form-control" required></div>
        <div><label>Countries (comma-separated codes, e.g. US,CA)</label>
            <select name="countries[]" multiple class="form-select"><option value="*">All Countries</option><option>US</option><option>CA</option><option>GB</option><option>IN</option><option>BD</option><option>AU</option></select></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
</form></div></div></div>
