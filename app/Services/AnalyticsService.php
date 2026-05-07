<?php

class AnalyticsService {
    private int $storeId;

    public function __construct(int $storeId) {
        $this->storeId = $storeId;
    }

    public function totalOrders(): int {
        return (int)(Database::fetch("SELECT COUNT(*) c FROM orders WHERE store_id = ?", [$this->storeId])['c']);
    }

    public function totalRevenue(): float {
        return (float)(Database::fetch("SELECT COALESCE(SUM(total),0) c FROM orders WHERE store_id = ? AND payment_status = 'paid'", [$this->storeId])['c']);
    }

    public function totalCustomers(): int {
        return (int)(Database::fetch("SELECT COUNT(DISTINCT user_id) c FROM orders WHERE store_id = ? AND user_id IS NOT NULL", [$this->storeId])['c']);
    }

    public function totalProducts(): int {
        return (int)(Database::fetch("SELECT COUNT(*) c FROM products WHERE store_id = ? AND status = 'active'", [$this->storeId])['c']);
    }

    public function ordersToday(): int {
        return (int)(Database::fetch("SELECT COUNT(*) c FROM orders WHERE store_id = ? AND DATE(created_at) = CURDATE()", [$this->storeId])['c']);
    }

    public function revenueToday(): float {
        return (float)(Database::fetch("SELECT COALESCE(SUM(total),0) c FROM orders WHERE store_id = ? AND payment_status = 'paid' AND DATE(created_at) = CURDATE()", [$this->storeId])['c']);
    }

    public function pendingOrders(): int {
        return (int)(Database::fetch("SELECT COUNT(*) c FROM orders WHERE store_id = ? AND status = 'pending'", [$this->storeId])['c']);
    }

    public function lowStockProducts(): int {
        return (int)(Database::fetch("SELECT COUNT(*) c FROM products WHERE store_id = ? AND track_inventory = 1 AND quantity <= low_stock_threshold AND status = 'active'", [$this->storeId])['c']);
    }

    public function recentOrders(int $limit = 5): array {
        return Database::fetchAll(
            "SELECT o.*, u.name as customer_name FROM orders o
             LEFT JOIN users u ON u.id = o.user_id WHERE o.store_id = ?
             ORDER BY o.created_at DESC LIMIT $limit",
            [$this->storeId]
        );
    }

    public function topProducts(int $limit = 5): array {
        return Database::fetchAll(
            "SELECT p.*, p.sales_count,
                    (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM products p WHERE p.store_id = ? ORDER BY p.sales_count DESC LIMIT $limit",
            [$this->storeId]
        );
    }

    public function revenueChart(int $days = 30): array {
        $rows = Database::fetchAll(
            "SELECT DATE(created_at) d, COALESCE(SUM(total),0) revenue, COUNT(*) orders
             FROM orders WHERE store_id = ? AND payment_status = 'paid'
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at) ORDER BY d",
            [$this->storeId, $days]
        );
        return $rows;
    }

    public function orderStatusDistribution(): array {
        return Database::fetchAll(
            "SELECT status, COUNT(*) cnt FROM orders WHERE store_id = ? GROUP BY status",
            [$this->storeId]
        );
    }

    public function trafficSources(int $days = 7): array {
        return Database::fetchAll(
            "SELECT COALESCE(referrer, 'Direct') as source, COUNT(*) cnt
             FROM analytics_events WHERE store_id = ? AND event = 'store_view'
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY source ORDER BY cnt DESC LIMIT 10",
            [$this->storeId, $days]
        );
    }

    public function getAnalyticsDashboard(int $days = 30): array {
        return [
            'kpis' => [
                'revenue'  => $this->totalRevenue(),
                'orders'   => $this->totalOrders(),
                'customers'=> $this->totalCustomers(),
                'aov'      => $this->totalOrders() ? $this->totalRevenue() / $this->totalOrders() : 0,
            ],
            'revenue_chart'  => $this->revenueChart($days),
            'top_products'   => $this->topProducts(10),
            'order_status'   => $this->orderStatusDistribution(),
            'traffic_sources'=> $this->trafficSources($days),
        ];
    }

    public function revenueAnalytics(int $days): array {
        $current  = $this->revenueChart($days);
        $previous = Database::fetchAll(
            "SELECT DATE(created_at) d, COALESCE(SUM(total),0) revenue FROM orders
             WHERE store_id = ? AND payment_status = 'paid'
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             AND created_at < DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at) ORDER BY d",
            [$this->storeId, $days * 2, $days]
        );
        return ['current' => $current, 'previous' => $previous];
    }

    public function productAnalytics(int $days): array {
        return [
            'best_sellers' => Database::fetchAll(
                "SELECT p.id, p.name, p.price, SUM(oi.quantity) as units_sold, SUM(oi.total) as revenue
                 FROM order_items oi JOIN products p ON p.id = oi.product_id JOIN orders o ON o.id = oi.order_id
                 WHERE o.store_id = ? AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY p.id ORDER BY units_sold DESC LIMIT 20", [$this->storeId, $days]
            ),
            'most_viewed' => Database::fetchAll(
                "SELECT * FROM products WHERE store_id = ? ORDER BY view_count DESC LIMIT 20", [$this->storeId]
            ),
            'low_stock' => Database::fetchAll(
                "SELECT * FROM products WHERE store_id = ? AND track_inventory = 1 AND quantity <= low_stock_threshold ORDER BY quantity ASC", [$this->storeId]
            ),
        ];
    }

    public function customerAnalytics(): array {
        return [
            'top_customers' => Database::fetchAll(
                "SELECT u.id, u.name, u.email, COUNT(o.id) as orders, SUM(o.total) as spent
                 FROM users u JOIN orders o ON o.user_id = u.id
                 WHERE o.store_id = ? GROUP BY u.id ORDER BY spent DESC LIMIT 20", [$this->storeId]
            ),
            'new_vs_returning' => Database::fetch(
                "SELECT
                    SUM(CASE WHEN order_count = 1 THEN 1 ELSE 0 END) as new_customers,
                    SUM(CASE WHEN order_count > 1 THEN 1 ELSE 0 END) as returning_customers
                 FROM (SELECT user_id, COUNT(*) as order_count FROM orders WHERE store_id = ? GROUP BY user_id) sub", [$this->storeId]
            ),
        ];
    }

    public function trafficAnalytics(int $days): array {
        return [
            'page_views' => Database::fetchAll(
                "SELECT DATE(created_at) d, COUNT(*) views FROM analytics_events
                 WHERE store_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY DATE(created_at) ORDER BY d", [$this->storeId, $days]
            ),
            'top_pages' => Database::fetchAll(
                "SELECT page, COUNT(*) views FROM analytics_events
                 WHERE store_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY page ORDER BY views DESC LIMIT 20", [$this->storeId, $days]
            ),
            'devices' => Database::fetchAll(
                "SELECT device, COUNT(*) cnt FROM analytics_events
                 WHERE store_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY device", [$this->storeId, $days]
            ),
        ];
    }

    public function salesReport(string $from, string $to): array {
        return [
            'summary' => Database::fetch(
                "SELECT COUNT(*) orders, SUM(total) revenue, AVG(total) aov, SUM(tax_amount) tax, SUM(shipping_amount) shipping
                 FROM orders WHERE store_id = ? AND payment_status = 'paid' AND DATE(created_at) BETWEEN ? AND ?",
                [$this->storeId, $from, $to]
            ),
            'daily' => Database::fetchAll(
                "SELECT DATE(created_at) d, COUNT(*) orders, SUM(total) revenue
                 FROM orders WHERE store_id = ? AND payment_status = 'paid' AND DATE(created_at) BETWEEN ? AND ?
                 GROUP BY DATE(created_at) ORDER BY d", [$this->storeId, $from, $to]
            ),
        ];
    }

    public function exportData(string $type, string $from, string $to): array {
        switch ($type) {
            case 'sales':
                $rows = Database::fetchAll(
                    "SELECT order_number, created_at, total, payment_status, status FROM orders
                     WHERE store_id = ? AND DATE(created_at) BETWEEN ? AND ?", [$this->storeId, $from, $to]
                );
                return ['headers' => ['Order','Date','Total','Payment','Status'], 'rows' => array_map('array_values', $rows)];
            case 'products':
                $rows = Database::fetchAll(
                    "SELECT p.name, p.sku, p.price, p.quantity, p.sales_count FROM products WHERE store_id = ?", [$this->storeId]
                );
                return ['headers' => ['Name','SKU','Price','Stock','Sold'], 'rows' => array_map('array_values', $rows)];
            default:
                return ['headers' => [], 'rows' => []];
        }
    }
}
