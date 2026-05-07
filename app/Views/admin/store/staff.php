<div class="d-flex justify-content-between mb-3"><h2>Staff</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inv">Invite Staff</button></div>
<div class="card"><table class="table mb-0">
<thead class="table-light"><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($staff as $s): ?>
<tr><td><?= e($s['name']) ?></td><td><?= e($s['email']) ?></td><td><?= e($s['role']) ?></td>
<td><?= e($s['status']) ?></td>
<td><button class="btn btn-sm btn-danger" onclick="if(confirm('Remove?'))fetch('/store/staff/<?= $s['id'] ?>',{method:'DELETE',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.reload())">Remove</button></td></tr>
<?php endforeach; ?>
</tbody></table></div>

<div class="modal fade" id="inv"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="/store/staff/invite"><?= csrf_field() ?>
    <div class="modal-header"><h5>Invite Staff</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label>Email</label><input name="email" type="email" class="form-control" required></div>
        <div><label>Role</label><select name="role" class="form-select"><option>admin</option><option>manager</option><option>staff</option></select></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Invite</button></div>
</form></div></div></div>
