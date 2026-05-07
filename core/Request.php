<?php

class Request {
    private array $data;

    public function __construct() {
        $this->data = array_merge($_GET, $_POST);
        if ($this->isJson()) {
            $json = json_decode(file_get_contents('php://input'), true) ?? [];
            $this->data = array_merge($this->data, $json);
        }
    }

    public function all(): array { return $this->data; }

    public function get(string $key, mixed $default = null): mixed {
        return $this->data[$key] ?? $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed {
        return $this->data[$key] ?? $default;
    }

    public function only(array $keys): array {
        return array_intersect_key($this->data, array_flip($keys));
    }

    public function except(array $keys): array {
        return array_diff_key($this->data, array_flip($keys));
    }

    public function has(string $key): bool {
        return isset($this->data[$key]) && $this->data[$key] !== '';
    }

    public function file(string $key): ?array {
        return $_FILES[$key] ?? null;
    }

    public function files(string $key): array {
        if (!isset($_FILES[$key])) return [];
        $files = [];
        foreach ($_FILES[$key]['name'] as $i => $name) {
            $files[] = [
                'name'     => $name,
                'type'     => $_FILES[$key]['type'][$i],
                'tmp_name' => $_FILES[$key]['tmp_name'][$i],
                'error'    => $_FILES[$key]['error'][$i],
                'size'     => $_FILES[$key]['size'][$i],
            ];
        }
        return $files;
    }

    public function hasFile(string $key): bool {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function method(): string {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        return strtoupper($method);
    }

    public function isMethod(string $method): bool {
        return $this->method() === strtoupper($method);
    }

    public function isGet(): bool  { return $this->isMethod('GET'); }
    public function isPost(): bool { return $this->isMethod('POST'); }

    public function isAjax(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || $this->wantsJson();
    }

    public function isJson(): bool {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($ct, 'application/json');
    }

    public function wantsJson(): bool {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    public function ip(): string {
        foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return explode(',', $_SERVER[$key])[0];
            }
        }
        return '0.0.0.0';
    }

    public function userAgent(): string {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function url(): string {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    public function path(): string {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public function fullUrl(): string {
        return $this->url();
    }

    public function host(): string {
        return $_SERVER['HTTP_HOST'] ?? '';
    }

    public function bearerToken(): ?string {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return $m[1];
        }
        return null;
    }

    public function sanitize(string $key): string {
        return htmlspecialchars(strip_tags($this->data[$key] ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public function integer(string $key, int $default = 0): int {
        return (int)($this->data[$key] ?? $default);
    }

    public function boolean(string $key, bool $default = false): bool {
        $val = $this->data[$key] ?? null;
        if ($val === null) return $default;
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }
}
