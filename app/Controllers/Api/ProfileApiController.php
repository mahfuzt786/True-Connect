<?php
namespace Api;

use Database;
use Upload;

class ProfileApiController extends ApiController {
    public function show(): void {
        $this->requireUser();
        $this->ok(array_intersect_key($this->user, array_flip(['id','name','email','phone','avatar','role'])));
    }
    public function update(): void {
        $this->requireUser();
        $data = $this->validate(['name' => 'required|min:2', 'phone' => 'nullable']);
        Database::update('users', $data, 'id = ?', [$this->user['id']]);
        $this->ok(null, 'Profile updated');
    }
    public function uploadAvatar(): void {
        $this->requireUser();
        if (!$this->request->hasFile('avatar')) $this->error('No file');
        $u = (new Upload())->disk('public')->to('uploads/avatars')->types(['image'])->maxSize(2048)->handle($this->request->file('avatar'));
        Database::update('users', ['avatar' => $u['url']], 'id = ?', [$this->user['id']]);
        $this->ok(['avatar' => $u['url']]);
    }
    public function changePassword(): void {
        $this->requireUser();
        $data = $this->validate(['current_password' => 'required', 'password' => 'required|password_strength']);
        if (!password_verify($data['current_password'], $this->user['password'])) $this->error('Current password incorrect');
        Database::update('users', ['password' => password_hash($data['password'], PASSWORD_BCRYPT)], 'id = ?', [$this->user['id']]);
        $this->ok(null, 'Password changed');
    }
    public function addresses(): void {
        $this->requireUser();
        $this->ok(Database::fetchAll("SELECT * FROM addresses WHERE user_id = ?", [$this->user['id']]));
    }
    public function storeAddress(): void {
        $this->requireUser();
        $data = $this->validate([
            'first_name' => 'required', 'last_name' => 'required',
            'address_line1' => 'required', 'city' => 'required',
            'country' => 'required', 'zip_code' => 'required',
        ]);
        $data['user_id'] = $this->user['id'];
        $data['type']    = $this->request->post('type', 'shipping');
        $id = Database::insert('addresses', $data);
        $this->ok(['id' => $id], 'Address added');
    }
    public function updateAddress(int $id): void {
        $this->requireUser();
        $a = Database::fetch("SELECT id FROM addresses WHERE id = ? AND user_id = ?", [$id, $this->user['id']]);
        if (!$a) $this->error('Not found', 404);
        Database::update('addresses', $this->request->only(['first_name','last_name','address_line1','address_line2','city','state','country','zip_code','phone']), 'id = ?', [$id]);
        $this->ok(null, 'Updated');
    }
    public function deleteAddress(int $id): void {
        $this->requireUser();
        Database::delete('addresses', 'id = ? AND user_id = ?', [$id, $this->user['id']]);
        $this->ok(null, 'Deleted');
    }
}
