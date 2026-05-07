<?php
// CLI: process queued jobs
// Usage: php cli/queue.php
require_once dirname(__DIR__) . '/core/bootstrap.php';

$jobs = Database::fetchAll("SELECT * FROM jobs WHERE reserved_at IS NULL AND available_at <= NOW() ORDER BY id LIMIT 20");
foreach ($jobs as $job) {
    Database::update('jobs', ['reserved_at' => date('Y-m-d H:i:s'), 'attempts' => $job['attempts'] + 1], 'id = ?', [$job['id']]);
    try {
        $payload = json_decode($job['payload'], true);
        $class   = $payload['class'] ?? null;
        $method  = $payload['method'] ?? 'handle';
        $args    = $payload['args'] ?? [];
        if ($class && class_exists($class)) {
            $instance = new $class();
            call_user_func_array([$instance, $method], $args);
        }
        Database::delete('jobs', 'id = ?', [$job['id']]);
        echo "[OK] Job #{$job['id']} ($class::$method)\n";
    } catch (Throwable $e) {
        if ($job['attempts'] >= 3) {
            Database::insert('failed_jobs', ['queue' => $job['queue'], 'payload' => $job['payload'], 'exception' => $e->getMessage()]);
            Database::delete('jobs', 'id = ?', [$job['id']]);
            echo "[FAIL] Job #{$job['id']}: " . $e->getMessage() . "\n";
        } else {
            Database::update('jobs', ['reserved_at' => null, 'available_at' => date('Y-m-d H:i:s', strtotime('+5 min'))], 'id = ?', [$job['id']]);
            echo "[RETRY] Job #{$job['id']}\n";
        }
    }
}
