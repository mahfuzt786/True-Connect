<?php

class Auth {
    private static ?array $user = null;

    public static function attempt(string $email, string $password, bool $remember = false): bool {
        $user = Database::fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        if (!$user['email_verified_at']) {
            Session::flash('error', 'Please verify your email before logging in.');
            return false;
        }

        static::login($user, $remember);
        return true;
    }

    public static function login(array $user, bool $remember = false): void {
        Session::regenerate();
        Session::set('auth_user_id', $user['id']);
        self::$user = $user;

        Database::update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ], 'id = ?', [$user['id']]);

        ActivityLogger::log('auth.login', 'Signed in', (int)$user['id']);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $hash  = hash('sha256', $token);
            Database::update('users', ['remember_token' => $hash], 'id = ?', [$user['id']]);
            setcookie('remember_token', $token, [
                'expires'  => time() + 86400 * 30,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    public static function logout(): void {
        if ($user = static::user()) {
            ActivityLogger::log('auth.logout', 'Signed out', (int)$user['id']);
            Database::update('users', ['remember_token' => null], 'id = ?', [$user['id']]);
        }
        setcookie('remember_token', '', time() - 3600, '/');
        Session::destroy();
        self::$user = null;
    }

    public static function user(): ?array {
        if (self::$user !== null) {
            return self::$user;
        }

        $userId = Session::get('auth_user_id');
        if ($userId) {
            self::$user = Database::fetch("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId]);
            return self::$user;
        }

        // Check remember-me cookie
        if (isset($_COOKIE['remember_token'])) {
            $hash = hash('sha256', $_COOKIE['remember_token']);
            $user = Database::fetch("SELECT * FROM users WHERE remember_token = ? AND status = 'active'", [$hash]);
            if ($user) {
                Session::set('auth_user_id', $user['id']);
                self::$user = $user;
                return self::$user;
            }
        }

        return null;
    }

    public static function id(): ?int {
        return static::user()['id'] ?? null;
    }

    public static function check(): bool {
        return static::user() !== null;
    }

    public static function guest(): bool {
        return !static::check();
    }

    public static function is(string ...$roles): bool {
        $user = static::user();
        return $user && in_array($user['role'], $roles);
    }

    public static function isSuperAdmin(): bool { return static::is('super_admin'); }
    public static function isStoreOwner(): bool  { return static::is('store_owner', 'super_admin'); }
    public static function isVendor(): bool       { return static::is('vendor'); }
    public static function isCustomer(): bool     { return static::is('customer'); }

    public static function register(array $data): int {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['email_verify_token'] = bin2hex(random_bytes(32));
        $data['status'] = 'pending';
        // Default to customer — sellers are promoted to store_owner / vendor
        // only after completing store setup or marketplace approval.
        $data['role'] = $data['role'] ?? 'customer';
        $userId = Database::insert('users', array_intersect_key($data, array_flip([
            'name', 'email', 'password', 'phone', 'role', 'status', 'email_verify_token'
        ])));
        ActivityLogger::log('auth.register', 'Account created', $userId);
        return $userId;
    }

    public static function verifyEmail(string $token): bool {
        $user = Database::fetch("SELECT * FROM users WHERE email_verify_token = ? AND email_verified_at IS NULL", [$token]);
        if (!$user) return false;
        Database::update('users', [
            'email_verified_at'  => date('Y-m-d H:i:s'),
            'email_verify_token' => null,
            'status'             => 'active',
        ], 'id = ?', [$user['id']]);
        return true;
    }

    public static function createPasswordReset(string $email): ?string {
        $user = Database::fetch("SELECT id FROM users WHERE email = ? AND status = 'active'", [$email]);
        if (!$user) return null;

        Database::query("DELETE FROM password_resets WHERE email = ?", [$email]);
        $token = bin2hex(random_bytes(32));
        Database::insert('password_resets', [
            'email'      => $email,
            'token'      => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ]);
        return $token;
    }

    public static function resetPassword(string $token, string $newPassword): bool {
        $hash  = hash('sha256', $token);
        $reset = Database::fetch("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used_at IS NULL", [$hash]);
        if (!$reset) return false;

        $user = Database::fetch("SELECT id FROM users WHERE email = ?", [$reset['email']]);
        if (!$user) return false;

        Database::update('users', ['password' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12])], 'id = ?', [$user['id']]);
        Database::update('password_resets', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$reset['id']]);
        return true;
    }

    // API token auth
    public static function authenticateByToken(string $token): ?array {
        $hash = hash('sha256', $token);
        $apiToken = Database::fetch(
            "SELECT t.*, u.* FROM api_tokens t
             JOIN users u ON u.id = t.user_id
             WHERE t.token = ? AND (t.expires_at IS NULL OR t.expires_at > NOW()) AND u.status = 'active'",
            [$hash]
        );
        if ($apiToken) {
            Database::update('api_tokens', ['last_used_at' => date('Y-m-d H:i:s')], 'id = ?', [$apiToken['id']]);
        }
        return $apiToken;
    }

    public static function createApiToken(int $userId, string $name, array $abilities = ['*'], ?int $expiresInDays = null): string {
        $token = bin2hex(random_bytes(40));
        Database::insert('api_tokens', [
            'user_id'    => $userId,
            'name'       => $name,
            'token'      => hash('sha256', $token),
            'abilities'  => json_encode($abilities),
            'expires_at' => $expiresInDays ? date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days")) : null,
        ]);
        return $token;
    }
}
