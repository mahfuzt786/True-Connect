<?php
namespace Api;

use AnalyticsService;
use Database;

class StoreAdminApiController extends ApiController {
    private function store(): array {
        $this->requireUser();
        $s = Database::fetch("SELECT * FROM stores WHERE user_id = ? LIMIT 1", [$this->user['id']]);
        if (!$s) $this->error('No store', 404);
        return $s;
    }
    public function dashboard(): void {
        $store = $this->store();
        $svc = new AnalyticsService($store['id']);
        $this->ok([
            'total_orders'   => $svc->totalOrders(),
            'total_revenue'  => $svc->totalRevenue(),
            'total_customers'=> $svc->totalCustomers(),
            'total_products' => $svc->totalProducts(),
            'pending_orders' => $svc->pendingOrders(),
            'low_stock'      => $svc->lowStockProducts(),
            'recent_orders'  => $svc->recentOrders(8),
            'top_products'   => $svc->topProducts(5),
            'revenue_chart'  => $svc->revenueChart(30),
        ]);
    }
    public function analytics(): void {
        $store = $this->store();
        $svc = new AnalyticsService($store['id']);
        $period = (int)$this->request->get('period', 30);
        $this->ok($svc->getAnalyticsDashboard($period));
    }
    public function customers(): void {
        $store = $this->store();
        $sql = "SELECT u.*, COUNT(o.id) orders, SUM(o.total) spent
                FROM users u JOIN orders o ON o.user_id = u.id
                WHERE o.store_id = ? GROUP BY u.id ORDER BY spent DESC";
        $this->ok($this->paginated($sql, [$store['id']], 25));
    }
    public function reports(): void {
        $store = $this->store();
        $svc = new AnalyticsService($store['id']);
        $from = $this->request->get('from', date('Y-m-01'));
        $to   = $this->request->get('to', date('Y-m-d'));
        $this->ok($svc->salesReport($from, $to));
    }
}
