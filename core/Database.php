<?php

class Database {
    private static ?PDO $instance = null;
    private static array $queryLog = [];
    private static int $queryCount = 0;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $cfg = require CONFIG_PATH . '/database.php';
            // Allow connecting without database name (used during install before DB is created)
            $dbPart = !empty($cfg['database']) ? "dbname={$cfg['database']};" : '';
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};{$dbPart}charset={$cfg['charset']}";
            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                error_log("DB Connection failed: " . $e->getMessage());
                // Re-throw so callers (e.g. install wizard) can surface the actual error message
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
            }
        }
        return self::$instance;
    }

    /** Reset singleton — used by install wizard to re-test after .env changes */
    public static function reset(): void {
        self::$instance = null;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $pdo  = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $start = microtime(true);
        $stmt->execute($params);
        if (defined('APP_DEBUG') && APP_DEBUG) {
            self::$queryLog[] = [
                'sql'    => $sql,
                'params' => $params,
                'time'   => round((microtime(true) - $start) * 1000, 2),
            ];
            self::$queryCount++;
        }
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        return self::query($sql, $params)->fetch() ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): int {
        $cols    = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $holders = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO `$table` ($cols) VALUES ($holders)", array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $stmt = self::query("UPDATE `$table` SET $set WHERE $where", [...array_values($data), ...$whereParams]);
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        return self::query("DELETE FROM `$table` WHERE $where", $params)->rowCount();
    }

    public static function paginate(string $sql, array $params, int $page, int $perPage = 20): array {
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as sub";
        $total    = (int) self::fetch($countSql, $params)['total'];
        $offset   = ($page - 1) * $perPage;
        $items    = self::fetchAll("$sql LIMIT $perPage OFFSET $offset", $params);
        return [
            'data'          => $items,
            'total'         => $total,
            'per_page'      => $perPage,
            'current_page'  => $page,
            'last_page'     => max(1, (int) ceil($total / $perPage)),
            'from'          => $total ? $offset + 1 : 0,
            'to'            => min($offset + $perPage, $total),
        ];
    }

    public static function transaction(callable $callback): mixed {
        $pdo = self::getInstance();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function tableExists(string $table): bool {
        $result = self::fetch("SHOW TABLES LIKE ?", [$table]);
        return $result !== null;
    }

    public static function getQueryLog(): array { return self::$queryLog; }
    public static function getQueryCount(): int { return self::$queryCount; }
}
