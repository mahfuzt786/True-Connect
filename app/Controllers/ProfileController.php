<?php

class ProfileController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function show(): void {
        $this->view('admin.profile.index', ['user' => $this->currentUser]);
    }

    public function update(): void {
        CSRF::validateOrFail();
        $data = $this->validate(['name' => 'required|min:2', 'phone' => 'nullable|phone']);
        Database::update('users', $data, 'id = ?', [$this->currentUser['id']]);
        $this->success('Profile updated.');
    }

    public function changePassword(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'current_password'      => 'required',
            'password'              => 'required|password_strength|confirmed',
            'password_confirmation' => 'required',
        ]);
        if (!password_verify($data['current_password'], $this->currentUser['password'])) {
            $this->error('Current password is incorrect.');
            return;
        }
        Database::update('users', ['password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12])], 'id = ?', [$this->currentUser['id']]);
        $this->success('Password changed successfully.');
    }

    public function uploadAvatar(): void {
        CSRF::validateOrFail();
        if (!$this->request->hasFile('avatar')) { $this->error('No file provided.'); return; }
        $u = (new Upload())->disk('public')->to('uploads/avatars')->types(['image'])->maxSize(2048)->handle($this->request->file('avatar'));
        Database::update('users', ['avatar' => $u['url']], 'id = ?', [$this->currentUser['id']]);
        $this->success('Avatar updated.', ['url' => $u['url']]);
    }

    public function enable2FA(): void {
        CSRF::validateOrFail();
        $secret = $this->generateSecret();
        Session::set('2fa_secret_pending', $secret);
        $qr = $this->getQrUrl($this->currentUser['email'], $secret);
        $this->json(['qr_url' => $qr, 'secret' => $secret]);
    }

    public function verify2FA(): void {
        CSRF::validateOrFail();
        $code   = $this->request->post('code', '');
        $secret = Session::get('2fa_secret_pending');
        if (!$secret || !$this->verifyTotp($secret, $code)) {
            $this->error('Invalid verification code.');
            return;
        }
        $codes = [];
        for ($i = 0; $i < 8; $i++) $codes[] = strtoupper(bin2hex(random_bytes(4)));
        Database::update('users', [
            'two_factor_secret'         => $secret,
            'two_factor_enabled'        => 1,
            'two_factor_recovery_codes' => json_encode(array_map('password_hash', $codes, array_fill(0, count($codes), PASSWORD_BCRYPT))),
        ], 'id = ?', [$this->currentUser['id']]);
        Session::forget('2fa_secret_pending');
        $this->json(['success' => true, 'recovery_codes' => $codes]);
    }

    public function disable2FA(): void {
        CSRF::validateOrFail();
        Database::update('users', ['two_factor_enabled' => 0, 'two_factor_secret' => null, 'two_factor_recovery_codes' => null], 'id = ?', [$this->currentUser['id']]);
        $this->success('Two-factor authentication disabled.');
    }

    private function generateSecret(): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) $secret .= $chars[random_int(0, 31)];
        return $secret;
    }

    private function getQrUrl(string $email, string $secret): string {
        $issuer = urlencode(config('app.name', 'SaaS'));
        $label  = urlencode("$issuer:$email");
        $otpUrl = "otpauth://totp/$label?secret=$secret&issuer=$issuer";
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($otpUrl);
    }

    private function verifyTotp(string $secret, string $code): bool {
        $time = floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            if ($this->generateTotp($secret, $time + $i) === $code) return true;
        }
        return false;
    }

    private function generateTotp(string $secret, int $time): string {
        $secret = $this->base32Decode($secret);
        $time   = pack('N*', 0) . pack('N*', $time);
        $hash   = hash_hmac('sha1', $time, $secret, true);
        $offset = ord($hash[19]) & 0xf;
        $code   = (
            ((ord($hash[$offset+0]) & 0x7f) << 24) |
            ((ord($hash[$offset+1]) & 0xff) << 16) |
            ((ord($hash[$offset+2]) & 0xff) << 8) |
            (ord($hash[$offset+3]) & 0xff)
        ) % 1000000;
        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $b32): string {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split(strtoupper($b32)) as $c) {
            $i = strpos($alphabet, $c);
            if ($i === false) continue;
            $bits .= str_pad(decbin($i), 5, '0', STR_PAD_LEFT);
        }
        $output = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) $output .= chr(bindec($byte));
        }
        return $output;
    }
}
