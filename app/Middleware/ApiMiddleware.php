<?php

class ApiMiddleware {
    private static array $publicRoutes = [
        '/api/v1/auth/register',
        '/api/v1/auth/login',
        '/api/v1/auth/forgot-password',
        '/api/v1/auth/reset-password',
    ];

    public function handle(callable $next): void {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Rate limiting
        $ip  = (new Request())->ip();
        if (!RateLimit::forIp($ip, 'api', 200, 60)) {
            (new Response())->json(['error' => 'Too Many Requests'], 429);
        }

        // Attempt token auth
        $token = (new Request())->bearerToken();
        if ($token) {
            $user = Auth::authenticateByToken($token);
            if ($user) {
                $_SESSION['auth_user_id'] = $user['id'];
            }
        }

        // Check if route requires auth
        if (!$this->isPublicRoute($uri) && !Auth::check()) {
            $jwtPayload = null;
            if ($token) {
                $jwtPayload = JWT::decode($token);
                if ($jwtPayload && isset($jwtPayload['user_id'])) {
                    $user = Database::fetch("SELECT * FROM users WHERE id = ? AND status = 'active'", [$jwtPayload['user_id']]);
                    if ($user) {
                        Session::set('auth_user_id', $user['id']);
                        $next();
                        return;
                    }
                }
            }
            (new Response())->json(['error' => 'Unauthenticated'], 401);
        }

        // CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $next();
    }

    private function isPublicRoute(string $uri): bool {
        foreach (self::$publicRoutes as $route) {
            if (str_starts_with($uri, $route)) return true;
        }
        // Product browsing routes are public
        if (preg_match('#^/api/v1/stores/[^/]+/(products|categories|search|featured)#', $uri)) return true;
        if (preg_match('#^/api/v1/products/[^/]+/reviews$#', $uri) && $_SERVER['REQUEST_METHOD'] === 'GET') return true;
        if (preg_match('#^/api/v1/stores/[^/]+/(cart|checkout)#', $uri)) return true;
        return false;
    }
}
