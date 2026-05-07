<?php
namespace Api;

use Database;

class OrderApiController extends ApiController {
    public function index(): void {
        $this->requireUser();
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $this->ok($this->paginated($sql, [$this->user['id']], 15));
    }
    public function show(int $id): void {
        $this->requireUser();
        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$id, $this->user['id']]);
        if (!$order) $this->error('Order not found', 404);
        $order['items']    = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$id]);
        $order['payments'] = Database::fetchAll("SELECT id, gateway, amount, status, paid_at FROM payments WHERE order_id = ?", [$id]);
        $this->ok($order);
    }
    public function track(int $id): void {
        $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$id]);
        if (!$order) $this->error('Order not found', 404);
        $history = Database::fetchAll("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at", [$id]);
        $this->ok([
            'order_number'    => $order['order_number'],
            'status'          => $order['status'],
            'tracking_number' => $order['tracking_number'],
            'tracking_url'    => $order['tracking_url'],
            'history'         => $history,
        ]);
    }
    public function cancel(int $id): void {
        $this->requireUser();
        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$id, $this->user['id']]);
        if (!$order) $this->error('Order not found', 404);
        if (!in_array($order['status'], ['pending','confirmed'])) $this->error('Cannot cancel this order');
        Database::update('orders', ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        $this->ok(null, 'Order cancelled');
    }
}
