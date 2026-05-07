<?php

class JWT {
    private static string $algorithm = 'HS256';

    public static function encode(array $payload, ?string $secret = null, int $expiresIn = 3600): string {
        $secret ??= config('app.jwt_secret', 'default_secret_change_this');
        $header  = static::base64Encode(json_encode(['typ' => 'JWT', 'alg' => static::$algorithm]));
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiresIn;
        $payload['jti'] = bin2hex(random_bytes(8));
        $body      = static::base64Encode(json_encode($payload));
        $signature = static::base64Encode(hash_hmac('sha256', "$header.$body", $secret, true));
        return "$header.$body.$signature";
    }

    public static function decode(string $token, ?string $secret = null): ?array {
        $secret ??= config('app.jwt_secret', 'default_secret_change_this');
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $signature] = $parts;
        $expected = static::base64Encode(hash_hmac('sha256', "$header.$body", $secret, true));

        if (!hash_equals($expected, $signature)) return null;

        $payload = json_decode(static::base64Decode($body), true);
        if (!$payload) return null;
        if (isset($payload['exp']) && $payload['exp'] < time()) return null;

        return $payload;
    }

    public static function refresh(string $token, int $expiresIn = 3600): ?string {
        $payload = static::decode($token);
        if (!$payload) return null;
        unset($payload['iat'], $payload['exp'], $payload['jti']);
        return static::encode($payload, null, $expiresIn);
    }

    private static function base64Encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64Decode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
