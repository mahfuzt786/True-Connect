<?php
namespace Api;

use Auth;
use Database;

class TokenApiController extends ApiController {
    public function index(): void {
        $this->requireUser();
        $tokens = Database::fetchAll("SELECT id, name, abilities, last_used_at, expires_at, created_at FROM api_tokens WHERE user_id = ?", [$this->user['id']]);
        $this->ok($tokens);
    }
    public function create(): void {
        $this->requireUser();
        $data  = $this->validate(['name' => 'required|min:2']);
        $abilities = (array)$this->request->post('abilities', ['*']);
        $expires   = $this->request->post('expires_in_days') ? (int)$this->request->post('expires_in_days') : null;
        $token = Auth::createApiToken($this->user['id'], $data['name'], $abilities, $expires);
        $this->ok(['token' => $token, 'name' => $data['name']], 'Save this token — it will only be shown once');
    }
    public function destroy(int $id): void {
        $this->requireUser();
        Database::delete('api_tokens', 'id = ? AND user_id = ?', [$id, $this->user['id']]);
        $this->ok(null, 'Token revoked');
    }
}
