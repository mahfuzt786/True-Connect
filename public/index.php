<?php
// ============================================================
// Application Entry Point
// ============================================================

define('ENTRY_POINT', true);

// Compute the base path (the directory the app is served from)
// e.g. /mahfuz/crm/public for http://localhost/mahfuz/crm/public/install
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
define('BASE_PATH', $basePath);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Strip the base path so routes like '/install' work regardless of subdirectory deployment
if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}
if ($uri === '' || $uri === false) $uri = '/';

// Serve theme static assets directly (CSS/JS/images)
if (preg_match('#(?:^|/)themes/([a-z0-9_-]+)/(css|js|images|fonts)/(.+)$#i', $uri, $m)) {
    $theme = $m[1]; $type = $m[2]; $file = $m[3];
    $path = dirname(__DIR__) . "/themes/$theme/$type/$file";
    if (file_exists($path) && is_file($path)) {
        $mimes = ['css'=>'text/css', 'js'=>'application/javascript', 'png'=>'image/png',
                  'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'gif'=>'image/gif',
                  'svg'=>'image/svg+xml', 'webp'=>'image/webp', 'woff'=>'font/woff', 'woff2'=>'font/woff2'];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=86400');
        readfile($path);
        exit;
    }
}

$router = require_once dirname(__DIR__) . '/core/bootstrap.php';

// Log this page view (best-effort, won't throw).
ActivityLogger::logPageView();

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $uri);
