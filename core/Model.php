<?php

abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $hidden = ['password', 'remember_token', 'email_verify_token'];
    protected static array $casts = [];
    protected static bool $timestamps = true;

    public static function getTable(): string {
        if (static::$table) return static::$table;
        $class = basename(str_replace('\\', '/', static::class));
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
    }

    public static function find(int $id): ?array {
        return Database::fetch("SELECT * FROM `" . static::getTable() . "` WHERE `" . static::$primaryKey . "` = ?", [$id]);
    }

    public static function findOrFail(int $id): array {
        $record = static::find($id);
        if (!$record) {
            throw new RuntimeException(static::class . " #$id not found");
        }
        return $record;
    }

    public static function where(string $column, mixed $value, string $operator = '='): array {
        return Database::fetchAll("SELECT * FROM `" . static::getTable() . "` WHERE `$column` $operator ?", [$value]);
    }

    public static function whereFirst(string $column, mixed $value, string $operator = '='): ?array {
        return Database::fetch("SELECT * FROM `" . static::getTable() . "` WHERE `$column` $operator ?", [$value]);
    }

    public static function all(string $orderBy = ''): array {
        $sql = "SELECT * FROM `" . static::getTable() . "`";
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        return Database::fetchAll($sql);
    }

    public static function create(array $data): int {
        $data = static::filterFillable($data);
        if (static::$timestamps) {
            $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
            $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');
        }
        return Database::insert(static::getTable(), $data);
    }

    public static function update(int $id, array $data): int {
        $data = static::filterFillable($data);
        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return Database::update(static::getTable(), $data, static::$primaryKey . " = ?", [$id]);
    }

    public static function delete(int $id): int {
        return Database::delete(static::getTable(), static::$primaryKey . " = ?", [$id]);
    }

    public static function count(string $where = '', array $params = []): int {
        $sql = "SELECT COUNT(*) as cnt FROM `" . static::getTable() . "`";
        if ($where) $sql .= " WHERE $where";
        return (int)(Database::fetch($sql, $params)['cnt'] ?? 0);
    }

    public static function exists(string $column, mixed $value, ?int $exceptId = null): bool {
        $sql    = "SELECT COUNT(*) as cnt FROM `" . static::getTable() . "` WHERE `$column` = ?";
        $params = [$value];
        if ($exceptId !== null) {
            $sql    .= " AND `" . static::$primaryKey . "` != ?";
            $params[] = $exceptId;
        }
        return (int)(Database::fetch($sql, $params)['cnt'] ?? 0) > 0;
    }

    public static function paginate(string $where = '', array $params = [], int $page = 1, int $perPage = 20, string $orderBy = 'created_at DESC'): array {
        $sql = "SELECT * FROM `" . static::getTable() . "`";
        if ($where) $sql .= " WHERE $where";
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        return Database::paginate($sql, $params, $page, $perPage);
    }

    protected static function filterFillable(array $data): array {
        if (empty(static::$fillable)) return $data;
        return array_intersect_key($data, array_flip(static::$fillable));
    }

    public static function hide(array $record): array {
        foreach (static::$hidden as $field) {
            unset($record[$field]);
        }
        return $record;
    }

    public static function generateSlug(string $name, ?int $exceptId = null): string {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        $base = $slug;
        $i    = 1;
        while (static::exists('slug', $slug, $exceptId)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
