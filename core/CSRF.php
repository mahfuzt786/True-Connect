<?php

class CSRF {
    private static string $sessionKey = '_csrf_token';

    public static function token(): string {
        if (!Session::has(self::$sessionKey)) {
            Session::set(self::$sessionKey, bin2hex(random_bytes(32)));
        }
        return Session::get(self::$sessionKey);
    }

    public static function field(): string {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    public static function meta(): string {
        return '<meta name="csrf-token" content="' . self::token() . '">';
    }

    public static function verify(?string $token = null): bool {
        $token ??= $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored = Session::get(self::$sessionKey);
        if (!$stored || !$token) return false;
        return hash_equals($stored, $token);
    }

    public static function regenerate(): void {
        Session::set(self::$sessionKey, bin2hex(random_bytes(32)));
    }

    public static function validateOrFail(): void {
        if (!self::verify()) {
            http_response_code(419);
            if ((new Request())->isAjax()) {
                (new Response())->json(['error' => 'CSRF token mismatch'], 419);
            }
            die('CSRF token mismatch. Please go back and try again.');
        }
    }
}
