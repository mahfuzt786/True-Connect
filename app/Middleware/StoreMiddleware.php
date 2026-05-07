<?php

class StoreMiddleware {
    public function handle(callable $next): void {
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
            exit;
        }

        // Super admin can bypass
        if ($user['role'] === 'super_admin') {
            $next();
            return;
        }

        $store = Database::fetch(
            "SELECT s.*, sub.status as sub_status FROM stores s
             LEFT JOIN subscriptions sub ON sub.store_id = s.id AND sub.status IN ('active','trialing')
             WHERE s.user_id = ? LIMIT 1",
            [$user['id']]
        );

        if (!$store) {
            redirect('/store/setup');
            exit;
        }

        if ($store['status'] === 'suspended') {
            http_response_code(403);
            include VIEWS_PATH . '/errors/suspended.php';
            exit;
        }

        // Check subscription expiry
        if ($store['sub_status'] === null || !in_array($store['sub_status'], ['active','trialing'])) {
            if (!in_array($_SERVER['REQUEST_URI'] ?? '/', ['/subscription', '/subscription/plans'])) {
                redirect('/subscription/plans?expired=1');
                exit;
            }
        }

        $next();
    }
}
