<?php

class CustomerController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $search = $this->request->get('search', '');
        $sql = "SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total),0) as total_spent
                FROM users u
                LEFT JOIN orders o ON o.user_id = u.id AND o.store_id = ?
                WHERE u.role = 'customer' AND u.id IN (SELECT DISTINCT user_id FROM orders WHERE store_id = ?)";
        $params = [$this->storeId, $this->storeId];
        if ($search) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%"; $params[] = "%$search%";
        }
        $sql .= " GROUP BY u.id ORDER BY total_spent DESC";
        $customers = $this->paginate($sql, $params, 25);
        $this->view('admin.customers.index', compact('customers','search'));
    }

    public function show(int $id): void {
        $customer = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$customer) $this->abort(404);
        $orders = Database::fetchAll("SELECT * FROM orders WHERE user_id = ? AND store_id = ? ORDER BY created_at DESC", [$id, $this->storeId]);
        $stats  = [
            'total_orders' => count($orders),
            'total_spent'  => array_sum(array_map(fn($o) => (float)$o['total'], $orders)),
            'last_order'   => $orders[0]['created_at'] ?? null,
        ];
        $addresses = Database::fetchAll("SELECT * FROM addresses WHERE user_id = ?", [$id]);
        $this->view('admin.customers.show', compact('customer','orders','stats','addresses'));
    }

    public function ban(int $id): void {
        Database::update('users', ['status' => 'banned'], 'id = ?', [$id]);
        $this->success('Customer banned.');
    }

    public function export(): void {
        $rows = Database::fetchAll("SELECT u.id, u.name, u.email, u.phone, COUNT(o.id) orders, SUM(o.total) spent FROM users u LEFT JOIN orders o ON o.user_id = u.id AND o.store_id = ? GROUP BY u.id", [$this->storeId]);
        $csv = "ID,Name,Email,Phone,Orders,Spent\n";
        foreach ($rows as $r) $csv .= '"' . implode('","', array_map(fn($v) => str_replace('"','""',(string)$v), $r)) . "\"\n";
        (new Response())->stream($csv, 'text/csv', 'customers-' . date('Y-m-d') . '.csv');
    }
}
