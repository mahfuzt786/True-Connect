<?php

class VendorMiddleware {
    public function handle(callable $next): void {
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
            exit;
        }
        if (!in_array($user['role'], ['vendor', 'store_owner', 'super_admin'])) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }
        $next();
    }
}
