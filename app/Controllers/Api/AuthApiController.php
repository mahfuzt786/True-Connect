<?php
namespace Api;

use Auth;
use Database;
use EmailService;
use JWT;
use RateLimit;

class AuthApiController extends ApiController {

    public function register(): void {
        $data = $this->validate([
            'name'                  => 'required|min:2',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|password_strength|confirmed',
            'password_confirmation' => 'required',
        ]);
        $data['role'] = $this->request->post('role', 'customer');
        $userId = Auth::register($data);
        $user   = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        try { (new EmailService())->sendEmailVerification($user); } catch (\Throwable) {}
        $this->ok(['user_id' => $userId], 'Registered. Please verify your email.');
    }

    public function login(): void {
        if (!RateLimit::forIp($this->request->ip(), 'api-login', 10, 900)) {
            $this->error('Too many attempts. Try again later.', 429);
        }
        $data = $this->validate(['email' => 'required|email', 'password' => 'required']);
        $user = Database::fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$data['email']]);
        if (!$user || !password_verify($data['password'], $user['password'])) $this->error('Invalid credentials', 401);
        if (!$user['email_verified_at']) $this->error('Please verify your email first', 403);

        $token = JWT::encode(['user_id' => $user['id'], 'role' => $user['role']], null, 86400 * 7);
        $apiToken = Auth::createApiToken($user['id'], 'api-' . substr(md5(uniqid()),0,6), ['*'], 30);
        $this->ok([
            'token'      => $token,
            'api_token'  => $apiToken,
            'expires_in' => 86400 * 7,
            'user'       => array_intersect_key($user, array_flip(['id','name','email','role','avatar'])),
        ], 'Login successful');
    }

    public function logout(): void {
        $this->requireUser();
        $token = $this->request->bearerToken();
        if ($token) Database::delete('api_tokens', 'token = ?', [hash('sha256', $token)]);
        $this->ok(null, 'Logged out');
    }

    public function refresh(): void {
        $token = $this->request->bearerToken();
        $newToken = JWT::refresh($token, 86400 * 7);
        if (!$newToken) $this->error('Invalid token', 401);
        $this->ok(['token' => $newToken, 'expires_in' => 86400 * 7]);
    }

    public function me(): void {
        $this->requireUser();
        $this->ok(['user' => array_intersect_key($this->user, array_flip(['id','name','email','role','avatar','phone']))]);
    }

    public function forgotPassword(): void {
        $data  = $this->validate(['email' => 'required|email']);
        $token = Auth::createPasswordReset($data['email']);
        if ($token) {
            $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$data['email']]);
            try { (new EmailService())->sendPasswordReset($user, $token); } catch (\Throwable) {}
        }
        $this->ok(null, 'If account exists, reset link sent.');
    }

    public function resetPassword(): void {
        $data = $this->validate(['token' => 'required', 'password' => 'required|password_strength']);
        if (Auth::resetPassword($data['token'], $data['password'])) {
            $this->ok(null, 'Password reset successfully');
        } else {
            $this->error('Invalid or expired token', 400);
        }
    }
}
