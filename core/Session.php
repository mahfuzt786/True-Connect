<?php

class Session {
    private static bool $started = false;

    public static function start(): void {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            $cfg = config('session', []);
            session_set_cookie_params([
                'lifetime' => $cfg['lifetime'] ?? 7200,
                'path'     => '/',
                'domain'   => $cfg['domain'] ?? '',
                'secure'   => $cfg['secure'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_name($cfg['name'] ?? 'saas_session');
            session_start();
            self::$started = true;
        }
    }

    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }

    public static function all(): array {
        self::start();
        return $_SESSION;
    }

    public static function flush(): void {
        self::start();
        $_SESSION = [];
    }

    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }

    public static function regenerate(bool $deleteOld = true): void {
        self::start();
        session_regenerate_id($deleteOld);
    }

    public static function id(): string {
        self::start();
        return session_id();
    }
}
