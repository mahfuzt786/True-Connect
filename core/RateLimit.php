<?php

class RateLimit {
    public static function check(string $key, int $maxAttempts = 60, int $decaySeconds = 60): bool {
        self::cleanup();
        $record = Database::fetch("SELECT * FROM rate_limits WHERE `key` = ?", [$key]);
        if (!$record) {
            Database::insert('rate_limits', [
                'key'      => $key,
                'attempts' => 1,
                'reset_at' => date('Y-m-d H:i:s', time() + $decaySeconds),
            ]);
            return true;
        }

        if ($record['attempts'] >= $maxAttempts) {
            return false;
        }

        Database::update('rate_limits', ['attempts' => $record['attempts'] + 1], '`key` = ?', [$key]);
        return true;
    }

    public static function tooManyAttempts(string $key, int $maxAttempts = 60, int $decaySeconds = 60): bool {
        return !self::check($key, $maxAttempts, $decaySeconds);
    }

    public static function remaining(string $key, int $maxAttempts = 60): int {
        $record = Database::fetch("SELECT attempts FROM rate_limits WHERE `key` = ? AND reset_at > NOW()", [$key]);
        if (!$record) return $maxAttempts;
        return max(0, $maxAttempts - (int)$record['attempts']);
    }

    public static function clear(string $key): void {
        Database::delete('rate_limits', '`key` = ?', [$key]);
    }

    public static function forIp(string $ip, string $action = 'request', int $max = 60, int $decay = 60): bool {
        return self::check("$action:$ip", $max, $decay);
    }

    private static function cleanup(): void {
        // Cleanup expired records periodically
        if (rand(1, 100) === 1) {
            Database::query("DELETE FROM rate_limits WHERE reset_at < NOW()");
        }
    }
}
