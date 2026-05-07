<?php

// ============================================================
// Global Helper Functions
// ============================================================

function env(string $key, mixed $default = null): mixed {
    static $env = null;
    if ($env === null) {
        $env = [];
        $envFile = ROOT_PATH . '/.env';
        if (file_exists($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\n\r\0\x0B\"'");
                $env[$k] = $v;
                putenv("$k=$v");
            }
        }
    }
    $value = $env[$key] ?? getenv($key);
    if ($value === false) return $default;
    if (strtolower($value) === 'null')  return null;
    if (strtolower($value) === 'true')  return true;
    if (strtolower($value) === 'false') return false;
    return $value;
}

function config(string $key, mixed $default = null): mixed {
    static $configs = [];
    $parts = explode('.', $key);
    $file  = $parts[0];

    if (!isset($configs[$file])) {
        $path = CONFIG_PATH . "/$file.php";
        $configs[$file] = file_exists($path) ? require $path : [];

        // Also load from 'config.php' for nested keys
        $mainPath = CONFIG_PATH . '/config.php';
        if ($file !== 'config' && file_exists($mainPath)) {
            $main = require $mainPath;
            if (isset($main[$file])) {
                $configs[$file] = array_merge($configs[$file], $main[$file]);
            }
        }
    }

    $data = $configs[$file];
    foreach (array_slice($parts, 1) as $part) {
        if (!is_array($data) || !array_key_exists($part, $data)) return $default;
        $data = $data[$part];
    }
    return $data ?? $default;
}

function url(string $path = '', array $params = []): string {
    $base = rtrim(config('app.url', ''), '/');
    $url  = $base . '/' . ltrim($path, '/');
    if ($params) $url .= '?' . http_build_query($params);
    return $url;
}

function asset(string $path): string {
    // Public assets live directly under the document root (public/), not /assets/.
    return url(ltrim($path, '/'));
}

function route(string $name, array $params = []): string {
    global $router;
    return $router?->url($name, $params) ?? url($name);
}

function redirect(string $url): void {
    if (defined('BASE_PATH') && BASE_PATH !== '' && str_starts_with($url, '/')
        && !str_starts_with($url, '//') && !str_starts_with($url, BASE_PATH . '/')
        && $url !== BASE_PATH) {
        $url = BASE_PATH . $url;
    }
    header("Location: $url");
    exit;
}

function back(): void {
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
}

function abort(int $code, string $message = ''): never {
    http_response_code($code);
    die("<h1>$code - $message</h1>");
}

function view(string $name, array $data = [], ?string $layout = 'main'): void {
    extract($data);
    $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $name) . '.php';
    if (!file_exists($viewFile)) {
        abort(500, "View [$name] not found");
    }
    ob_start();
    include $viewFile;
    $content = ob_get_clean();
    if ($layout) {
        $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
            return;
        }
    }
    echo $content;
}

function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string {
    return CSRF::field();
}

function csrf_token(): string {
    return CSRF::token();
}

function old(string $key, mixed $default = ''): mixed {
    return Session::getFlash('old')[$key] ?? $default;
}

function flash(string $key, mixed $default = null): mixed {
    return Session::getFlash($key, $default);
}

function hasError(string $field): bool {
    $errors = Session::get('_flash')['errors'] ?? [];
    return isset($errors[$field]);
}

function error(string $field): string {
    $errors = Session::getFlash('errors') ?? [];
    return $errors[$field] ?? '';
}

function auth(): ?array {
    return Auth::user();
}

function isAuth(): bool {
    return Auth::check();
}

function isGuest(): bool {
    return Auth::guest();
}

function money(float $amount, string $currency = 'INR', string $symbol = '₹'): string {
    return $symbol . number_format($amount, 2);
}

function formatCurrency(float $amount, ?array $store = null): string {
    $symbol = $store['currency_symbol'] ?? '₹';
    return $symbol . number_format($amount, 2);
}

function truncate(string $text, int $length = 100, string $suffix = '...'): string {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length - mb_strlen($suffix)) . $suffix;
}

function slugify(string $text): string {
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $text), '-'));
}

function generateOrderNumber(): string {
    return 'ORD-' . strtoupper(substr(uniqid(), -8)) . '-' . date('Ymd');
}

function generateInvoiceNumber(): string {
    return 'INV-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

function formatDate(string $datetime, string $format = 'M d, Y'): string {
    return (new DateTime($datetime))->format($format);
}

function formatDateTime(string $datetime): string {
    return formatDate($datetime, 'M d, Y h:i A');
}

function paginate(array $pagination): string {
    if ($pagination['last_page'] <= 1) return '';
    $current = $pagination['current_page'];
    $last    = $pagination['last_page'];
    $html    = '<nav aria-label="Pagination"><ul class="pagination">';

    if ($current > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="?page=' . ($current - 1) . '">‹</a></li>';
    }
    for ($i = max(1, $current - 2); $i <= min($last, $current + 2); $i++) {
        $active = $i === $current ? ' active' : '';
        $html  .= "<li class='page-item$active'><a class='page-link' href='?page=$i'>$i</a></li>";
    }
    if ($current < $last) {
        $html .= '<li class="page-item"><a class="page-link" href="?page=' . ($current + 1) . '">›</a></li>';
    }
    return $html . '</ul></nav>';
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function isValidEmail(string $email): bool {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateRandomString(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

function encryptData(string $data, ?string $key = null): string {
    $key    = $key ?? config('app.key');
    $iv     = random_bytes(16);
    $cipher = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $cipher);
}

function decryptData(string $data, ?string $key = null): ?string {
    $key     = $key ?? config('app.key');
    $decoded = base64_decode($data);
    $iv      = substr($decoded, 0, 16);
    $cipher  = substr($decoded, 16);
    $result  = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $result !== false ? $result : null;
}

function dd(mixed ...$values): never {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;font-size:13px;overflow:auto;">';
    foreach ($values as $v) {
        var_dump($v);
        echo "\n---\n";
    }
    echo '</pre>';
    exit;
}

function dump(mixed ...$values): void {
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;font-size:13px;">';
    foreach ($values as $v) var_dump($v);
    echo '</pre>';
}

function logError(string $message, array $context = []): void {
    $logFile = STORAGE_PATH . '/logs/error.log';
    $entry   = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) $entry .= ' | Context: ' . json_encode($context);
    file_put_contents($logFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function logInfo(string $message, array $context = []): void {
    $logFile = STORAGE_PATH . '/logs/app.log';
    $entry   = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) $entry .= ' | Context: ' . json_encode($context);
    file_put_contents($logFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function response(): Response {
    return new Response();
}

function request(): Request {
    return new Request();
}

function now(string $format = 'Y-m-d H:i:s'): string {
    return date($format);
}

function storageUrl(string $path): string {
    return url('storage/' . ltrim($path, '/'));
}

function notFound(): never {
    abort(404, 'Not Found');
}

function setting(string $key, mixed $default = null, ?int $storeId = null): mixed {
    $record = Database::fetch(
        "SELECT value, type FROM settings WHERE `key` = ? AND (store_id = ? OR store_id IS NULL) ORDER BY store_id DESC LIMIT 1",
        [$key, $storeId]
    );
    if (!$record) return $default;
    return match ($record['type']) {
        'boolean' => (bool)$record['value'],
        'integer' => (int)$record['value'],
        'json', 'array' => json_decode($record['value'], true),
        default => $record['value'],
    };
}

/** Production lockdown — disables text selection, image saving, context menu, devtools shortcuts. */
function is_lockdown(): bool {
    static $cached = null;
    if ($cached !== null) return $cached;
    $val = env('APP_LOCKDOWN', false);
    if (is_string($val)) $val = in_array(strtolower($val), ['1','true','yes','on'], true);
    return $cached = (bool)$val;
}

/** Site-wide social links from settings; per-store fallbacks decoded from stores.social_links. */
function social_links(?array $store = null): array {
    if ($store && !empty($store['social_links'])) {
        $decoded = is_string($store['social_links']) ? json_decode($store['social_links'], true) : $store['social_links'];
        if (is_array($decoded)) return $decoded;
    }
    $site = setting('social_links', null);
    if (is_string($site)) $site = json_decode($site, true);
    return is_array($site) ? $site : [];
}

/** Bootstrap-icons name + label for the supported social platforms. */
function social_platforms(): array {
    return [
        'facebook'  => ['icon' => 'bi-facebook',   'label' => 'Facebook'],
        'twitter'   => ['icon' => 'bi-twitter-x',  'label' => 'X / Twitter'],
        'instagram' => ['icon' => 'bi-instagram',  'label' => 'Instagram'],
        'linkedin'  => ['icon' => 'bi-linkedin',   'label' => 'LinkedIn'],
        'youtube'   => ['icon' => 'bi-youtube',    'label' => 'YouTube'],
        'tiktok'    => ['icon' => 'bi-tiktok',     'label' => 'TikTok'],
        'pinterest' => ['icon' => 'bi-pinterest',  'label' => 'Pinterest'],
        'whatsapp'  => ['icon' => 'bi-whatsapp',   'label' => 'WhatsApp'],
        'telegram'  => ['icon' => 'bi-telegram',   'label' => 'Telegram'],
    ];
}

/** Render meta tags. Returns rendered HTML; pages can pass title/desc/og/etc. */
function seo_meta(array $opts = []): string {
    $appName = config('app.name', 'True Commerce');
    $title   = isset($opts['title']) ? $opts['title'] . ' · ' . $appName : $appName;
    $desc    = $opts['description'] ?? env('APP_SEO_DESCRIPTION', 'Launch your e-commerce store or marketplace in minutes.');
    $kw      = $opts['keywords']    ?? env('APP_SEO_KEYWORDS', '');
    $url     = $opts['url']         ?? url(ltrim($_SERVER['REQUEST_URI'] ?? '/', '/'));
    $image   = $opts['image']       ?? url('images/logo.svg');
    $type    = $opts['type']        ?? 'website';
    $robots  = $opts['robots']      ?? 'index,follow';

    $h = '';
    $h .= '<title>' . e($title) . '</title>' . "\n";
    $h .= '<meta name="description" content="' . e($desc) . '">' . "\n";
    if ($kw) $h .= '<meta name="keywords" content="' . e($kw) . '">' . "\n";
    $h .= '<meta name="robots" content="' . e($robots) . '">' . "\n";
    $h .= '<meta name="theme-color" content="#667eea">' . "\n";
    $h .= '<link rel="canonical" href="' . e($url) . '">' . "\n";

    // Open Graph
    $h .= '<meta property="og:site_name" content="' . e($appName) . '">' . "\n";
    $h .= '<meta property="og:type" content="' . e($type) . '">' . "\n";
    $h .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
    $h .= '<meta property="og:description" content="' . e($desc) . '">' . "\n";
    $h .= '<meta property="og:url" content="' . e($url) . '">' . "\n";
    $h .= '<meta property="og:image" content="' . e($image) . '">' . "\n";

    // Twitter
    $h .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $h .= '<meta name="twitter:title" content="' . e($title) . '">' . "\n";
    $h .= '<meta name="twitter:description" content="' . e($desc) . '">' . "\n";
    $h .= '<meta name="twitter:image" content="' . e($image) . '">' . "\n";

    // JSON-LD
    if (!empty($opts['jsonld'])) {
        $h .= '<script type="application/ld+json">' . json_encode($opts['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }

    return $h;
}

function trackEvent(string $event, ?int $storeId = null, array $extra = []): void {
    if (!$storeId) return;
    try {
        $req = new Request();
        Database::insert('analytics_events', array_merge([
            'store_id'   => $storeId,
            'event'      => $event,
            'user_id'    => Auth::id(),
            'session_id' => Session::id(),
            'ip_address' => $req->ip(),
            'user_agent' => substr($req->userAgent(), 0, 500),
            'page'       => $req->path(),
            'referrer'   => $_SERVER['HTTP_REFERER'] ?? null,
        ], $extra));
    } catch (Throwable) {}
}
