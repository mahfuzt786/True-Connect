<?php

class AdminMiddleware {
    public function handle(callable $next): void {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'super_admin') {
            http_response_code(403);
            if ((new Request())->isAjax()) {
                (new Response())->json(['error' => 'Forbidden'], 403);
            }
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }
        $next();
    }
}
