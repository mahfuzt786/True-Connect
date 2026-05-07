<?php

// ============================================================
// Bootstrap - Application Initialization
// ============================================================

define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('VIEWS_PATH',   APP_PATH  . '/Views');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('THEMES_PATH',  ROOT_PATH . '/themes');

// Load helpers
require_once __DIR__ . '/helpers.php';

// Set timezone
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

// Error reporting
$debug = (bool)env('APP_DEBUG', false);
define('APP_DEBUG', $debug);

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Set error handler
set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return false;
    logError("[$errno] $errstr in $errfile:$errline");
    return false;
});

set_exception_handler(function(Throwable $e): void {
    logError($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
    if (APP_DEBUG) {
        echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;">';
        echo '<strong>' . e(get_class($e)) . ': ' . e($e->getMessage()) . '</strong>' . PHP_EOL;
        echo e($e->getFile()) . ':' . $e->getLine() . PHP_EOL . PHP_EOL;
        echo e($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        include VIEWS_PATH . '/errors/500.php';
    }
    exit;
});

// Autoloader
spl_autoload_register(function(string $class): void {
    $relative = str_replace('\\', '/', $class) . '.php';
    $paths = [
        ROOT_PATH  . '/core/' . $relative,
        APP_PATH   . '/Controllers/' . $relative,
        APP_PATH   . '/Models/' . $relative,
        APP_PATH   . '/Services/' . $relative,
        APP_PATH   . '/Middleware/' . $relative,
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Define BASE_PATH if not already (for CLI calls)
if (!defined('BASE_PATH')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    define('BASE_PATH', rtrim(str_replace('\\', '/', dirname($scriptName)), '/'));
}

// Force HTTPS in non-local environments when APP_FORCE_HTTPS is set.
$forceHttps = (bool)env('APP_FORCE_HTTPS', false);
if ($forceHttps && !$debug) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
    if (!$isHttps && PHP_SAPI !== 'cli') {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: https://' . $host . $uri, true, 301);
        exit;
    }
}

// Hardened session config — must run before Session::start().
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $secureCookie = !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($secureCookie) ini_set('session.cookie_secure', '1');
}

// Start session
Session::start();

// Output buffer that auto-prefixes BASE_PATH to all root-relative URLs in HTML output
// so the app works at any subdirectory (e.g. /mahfuz/crm/public)
if (BASE_PATH !== '' && !defined('SKIP_URL_REWRITE')) {
    ob_start(function(string $output): string {
        $base = BASE_PATH;
        // Skip JSON or non-HTML responses
        foreach (headers_list() as $h) {
            if (stripos($h, 'Content-Type:') === 0 && stripos($h, 'text/html') === false) return $output;
        }
        // Rewrite href="/...", action="/...", src="/..." (but not //... or /BASE/...)
        $output = preg_replace_callback(
            '#\b(href|action|src|data-url)=([\'"])(/(?!/|' . preg_quote(ltrim($base, '/'), '#') . '|http)[^\'"]*)\2#i',
            fn($m) => $m[1] . '=' . $m[2] . $base . $m[3] . $m[2],
            $output
        );
        return $output;
    });
}

// Security headers — applied early so every response benefits.
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(self), usb=()');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-site');

    if (!$debug) {
        // Tight CSP: only allow trusted CDNs we use plus our payment partners.
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://js.stripe.com https://checkout.razorpay.com https://www.paypal.com; "
             . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
             . "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; "
             . "img-src 'self' data: blob: https:; "
             . "connect-src 'self' https://api.stripe.com https://api.razorpay.com https://api.paypal.com; "
             . "frame-src https://js.stripe.com https://checkout.razorpay.com https://www.paypal.com; "
             . "frame-ancestors 'self'; "
             . "object-src 'none'; "
             . "base-uri 'self'; "
             . "form-action 'self';";
        header('Content-Security-Policy: ' . $csp);
        // 1-year HSTS with preload
        if ($forceHttps) header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

// Create router
$router = new Router();

// Load routes
require_once ROOT_PATH . '/routes/web.php';
require_once ROOT_PATH . '/routes/api.php';

return $router;
