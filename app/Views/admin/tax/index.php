<div class="d-flex justify-content-between mb-3"><h2>Tax Rates</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTax">Add Tax</button></div>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Name</th><th>Country</th><th>State</th><th>Rate</th><th>Class</th><th></th></tr></thead><tbody>
<?php foreach ($rates as $r): ?>
<tr><td><?= e($r['name']) ?></td><td><?= e($r['country']) ?></td><td><?= e($r['state']) ?></td>
<td><?= $r['rate'] ?>%</td><td><?= e($r['tax_class']) ?></td>
<td><button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete?'))fetch('/store/tax/<?= $r['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button></td></tr>
<?php endforeach; ?>
</tbody></table></div>

<div class="modal fade" id="addTax"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="/store/tax"><?= csrf_field() ?>
    <div class="modal-header"><h5>Add Tax Rate</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label>Name</label><input name="name" class="form-control" required></div>
        <div class="mb-2"><label>Country</label><input name="country" class="form-control" required placeholder="US"></div>
        <div class="mb-2"><label>State (optional)</label><input name="state" class="form-control"></div>
        <div class="mb-2"><label>Rate (%)</label><input type="number" step="0.01" name="rate" class="form-control" required></div>
        <div><label>Tax Class</label><input name="tax_class" class="form-control" value="standard"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
</form></div></div></div>
