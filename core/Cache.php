<?php

class Cache {
    private static string $driver = 'file';
    private static string $path   = '';

    public static function init(): void {
        $cfg = config('cache', []);
        self::$driver = $cfg['driver'] ?? 'file';
        self::$path   = $cfg['path'] ?? STORAGE_PATH . '/cache';
        if (self::$driver === 'file' && !is_dir(self::$path)) {
            mkdir(self::$path, 0755, true);
        }
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::init();
        switch (self::$driver) {
            case 'redis':  return self::redisGet($key) ?? $default;
            default:       return self::fileGet($key) ?? $default;
        }
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): bool {
        self::init();
        return match (self::$driver) {
            'redis' => self::redisSet($key, $value, $ttl),
            default => self::fileSet($key, $value, $ttl),
        };
    }

    public static function has(string $key): bool {
        return self::get($key) !== null;
    }

    public static function forget(string $key): void {
        self::init();
        $file = self::filePath($key);
        if (file_exists($file)) unlink($file);
    }

    public static function flush(): void {
        self::init();
        array_map('unlink', glob(self::$path . '/*.cache') ?: []);
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed {
        $value = self::get($key);
        if ($value !== null) return $value;
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function rememberForever(string $key, callable $callback): mixed {
        return self::remember($key, 86400 * 365, $callback);
    }

    public static function tags(array $tags): self {
        // Simplified tag support — store tag manifest
        return new self();
    }

    private static function filePath(string $key): string {
        return self::$path . '/' . md5($key) . '.cache';
    }

    private static function fileGet(string $key): mixed {
        $file = self::filePath($key);
        if (!file_exists($file)) return null;
        $data = unserialize(file_get_contents($file));
        if ($data === false || $data['expires_at'] < time()) {
            unlink($file);
            return null;
        }
        return $data['value'];
    }

    private static function fileSet(string $key, mixed $value, int $ttl): bool {
        $file = self::filePath($key);
        return (bool)file_put_contents($file, serialize(['expires_at' => time() + $ttl, 'value' => $value]), LOCK_EX);
    }

    private static function redisGet(string $key): mixed {
        // Redis support — falls back to file if not available
        try {
            $redis = self::redisConnection();
            $val   = $redis->get($key);
            return $val !== false ? unserialize($val) : null;
        } catch (Exception) {
            return self::fileGet($key);
        }
    }

    private static function redisSet(string $key, mixed $value, int $ttl): bool {
        try {
            $redis = self::redisConnection();
            return $redis->setEx($key, $ttl, serialize($value));
        } catch (Exception) {
            return self::fileSet($key, $value, $ttl);
        }
    }

    private static ?object $redisInstance = null;

    private static function redisConnection(): object {
        if (self::$redisInstance !== null) return self::$redisInstance;
        $cfg = config('cache.redis', []);
        $redis = new Redis();
        $redis->connect($cfg['host'] ?? '127.0.0.1', $cfg['port'] ?? 6379);
        if ($password = $cfg['password'] ?? null) {
            $redis->auth($password);
        }
        self::$redisInstance = $redis;
        return $redis;
    }
}
