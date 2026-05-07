<?php

class DashboardController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $user = $this->currentUser;

        if ($user['role'] === 'super_admin') {
            // Super admin only sees a store dashboard when impersonating one;
            // otherwise the platform-level admin dashboard is the right home.
            if (!$this->currentStore) {
                redirect('/admin');
                return;
            }
            // Fall through and render store dashboard for the impersonated store.
        }

        if ($user['role'] === 'vendor') {
            redirect('/vendor/dashboard');
            return;
        }

        // Buyers (customers) — they don't have a store dashboard; send them
        // to their account area or to the marketplace based on their intent.
        if ($user['role'] === 'customer') {
            $sellerIntent = Session::get('intent_seller_type');
            if ($sellerIntent === 'ecommerce') {
                Session::forget('intent_seller_type');
                redirect('/store/setup');
                return;
            }
            if ($sellerIntent === 'marketplace') {
                Session::forget('intent_seller_type');
                redirect('/vendor/register');
                return;
            }
            redirect('/account');
            return;
        }

        $this->requireStore();
        $store = $this->currentStore;
        $svc   = new AnalyticsService($store['id']);

        $stats = [
            'total_orders'       => $svc->totalOrders(),
            'total_revenue'      => $svc->totalRevenue(),
            'total_customers'    => $svc->totalCustomers(),
            'total_products'     => $svc->totalProducts(),
            'orders_today'       => $svc->ordersToday(),
            'revenue_today'      => $svc->revenueToday(),
            'pending_orders'     => $svc->pendingOrders(),
            'low_stock_products' => $svc->lowStockProducts(),
        ];

        $recentOrders    = $svc->recentOrders(8);
        $topProducts     = $svc->topProducts(5);
        $revenueChart    = $svc->revenueChart(30);
        $orderStatusData = $svc->orderStatusDistribution();
        $trafficSources  = $svc->trafficSources(7);

        $this->view('admin.dashboard', compact(
            'store', 'stats', 'recentOrders', 'topProducts',
            'revenueChart', 'orderStatusData', 'trafficSources'
        ));
    }

    public function analytics(): void {
        $this->requireAuth();
        $this->requireStore();
        $store = $this->currentStore;
        $svc   = new AnalyticsService($store['id']);

        $period   = $this->request->get('period', '30');
        $data     = $svc->getAnalyticsDashboard((int)$period);

        $this->view('admin.analytics', compact('store', 'data', 'period'));
    }

    public function salesReport(): void {
        $this->requireAuth();
        $this->requireStore();
        $store = $this->currentStore;

        $from = $this->request->get('from', date('Y-m-01'));
        $to   = $this->request->get('to', date('Y-m-d'));

        $svc  = new AnalyticsService($store['id']);
        $data = $svc->salesReport($from, $to);

        $this->view('admin.reports.sales', compact('store', 'data', 'from', 'to'));
    }

    public function exportReport(): void {
        $this->requireAuth();
        $this->requireStore();
        $store  = $this->currentStore;
        $type   = $this->request->get('type', 'sales');
        $format = $this->request->get('format', 'csv');
        $from   = $this->request->get('from', date('Y-m-01'));
        $to     = $this->request->get('to', date('Y-m-d'));

        $svc    = new AnalyticsService($store['id']);
        $data   = $svc->exportData($type, $from, $to);

        if ($format === 'csv') {
            $csv = $this->arrayToCsv($data['headers'], $data['rows']);
            (new Response())->stream($csv, 'text/csv', "{$type}-report-{$from}-to-{$to}.csv");
        } else {
            $this->json($data);
        }
    }

    private function arrayToCsv(array $headers, array $rows): string {
        ob_start();
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $headers);
        foreach ($rows as $row) fputcsv($fp, $row);
        fclose($fp);
        return ob_get_clean();
    }
}
