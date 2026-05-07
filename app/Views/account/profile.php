<h2>Profile</h2>
<form method="POST" action="/account/profile" class="card p-4 mb-3"><?= csrf_field() ?>
    <div class="mb-3"><label>Name</label><input name="name" value="<?= e($user['name']) ?>" class="form-control"></div>
    <div class="mb-3"><label>Email</label><input value="<?= e($user['email']) ?>" class="form-control" disabled></div>
    <div class="mb-3"><label>Phone</label><input name="phone" value="<?= e($user['phone']??'') ?>" class="form-control"></div>
    <button class="btn btn-primary">Update</button>
</form>
<form method="POST" action="/account/password" class="card p-4"><?= csrf_field() ?>
    <h5>Change Password</h5>
    <div class="mb-3"><label>Current Password</label><input type="password" name="current_password" class="form-control"></div>
    <div class="mb-3"><label>New Password</label><input type="password" name="password" class="form-control"></div>
    <div class="mb-3"><label>Confirm Password</label><input type="password" name="password_confirmation" class="form-control"></div>
    <button class="btn btn-primary">Change</button>
</form>
