<?php

class OrderController extends Controller {

    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $search  = $this->request->get('search', '');
        $status  = $this->request->get('status', '');
        $from    = $this->request->get('from', '');
        $to      = $this->request->get('to', '');

        $sql    = "SELECT o.*, u.name as customer_name, u.email as customer_email
                   FROM orders o LEFT JOIN users u ON u.id = o.user_id
                   WHERE o.store_id = ?";
        $params = [$this->storeId];

        if ($search) {
            $sql .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }
        if ($status) { $sql .= " AND o.status = ?"; $params[] = $status; }
        if ($from)   { $sql .= " AND DATE(o.created_at) >= ?"; $params[] = $from; }
        if ($to)     { $sql .= " AND DATE(o.created_at) <= ?"; $params[] = $to; }
        $sql .= " ORDER BY o.created_at DESC";

        $orders = $this->paginate($sql, $params, 25);
        $this->view('admin.orders.index', compact('orders', 'search', 'status', 'from', 'to'));
    }

    public function create(): void {
        $products  = Database::fetchAll("SELECT id, name, sku, price, quantity FROM products WHERE store_id = ? AND status = 'active' ORDER BY name", [$this->storeId]);
        $customers = Database::fetchAll("SELECT id, name, email FROM users WHERE role = 'customer' AND status = 'active' ORDER BY name LIMIT 100");
        $this->view('admin.orders.create', compact('products', 'customers'));
    }

    public function store(): void {
        CSRF::validateOrFail();
        $userId = $this->request->post('user_id') ?: null;
        $items  = $this->request->post('items', []);
        if (empty($items)) {
            $this->error('No items added to order.');
            return;
        }
        try {
            $orderId = Database::transaction(function() use ($userId, $items) {
                $orderNumber = generateOrderNumber();
                $subtotal = 0;
                foreach ($items as $item) {
                    $product = Database::fetch("SELECT * FROM products WHERE id = ? AND store_id = ?", [$item['product_id'], $this->storeId]);
                    if (!$product) continue;
                    $subtotal += $product['price'] * (int)$item['quantity'];
                }
                $orderId = Database::insert('orders', [
                    'store_id'         => $this->storeId,
                    'user_id'          => $userId,
                    'order_number'     => $orderNumber,
                    'status'           => 'confirmed',
                    'payment_status'   => $this->request->post('payment_status', 'pending'),
                    'payment_method'   => $this->request->post('payment_method', 'manual'),
                    'subtotal'         => $subtotal,
                    'total'            => $subtotal,
                    'currency'         => $this->currentStore['currency'],
                    'notes'            => $this->request->post('notes', ''),
                    'confirmed_at'     => now(),
                ]);
                foreach ($items as $item) {
                    $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$item['product_id']]);
                    if (!$product) continue;
                    Database::insert('order_items', [
                        'order_id'     => $orderId,
                        'product_id'   => $product['id'],
                        'product_name' => $product['name'],
                        'sku'          => $product['sku'],
                        'quantity'     => (int)$item['quantity'],
                        'unit_price'   => $product['price'],
                        'total'        => $product['price'] * (int)$item['quantity'],
                    ]);
                }
                Database::insert('order_status_history', [
                    'order_id' => $orderId,
                    'status'   => 'confirmed',
                    'comment'  => 'Manual order created by admin',
                    'created_by' => $this->currentUser['id'],
                ]);
                return $orderId;
            });
            $this->auditLog('order.create', 'Order', $orderId);
            $this->success('Order created.', ['id' => $orderId]);
        } catch (Throwable $e) {
            $this->error('Failed to create order: ' . $e->getMessage());
        }
    }

    public function show(int $id): void {
        $order = $this->findOrder($id);
        $items = Database::fetchAll(
            "SELECT oi.*, p.slug as product_slug, (SELECT image FROM product_images WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1) as product_image
             FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?",
            [$id]
        );
        $payments  = Database::fetchAll("SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC", [$id]);
        $history   = Database::fetchAll("SELECT h.*, u.name as created_by_name FROM order_status_history h LEFT JOIN users u ON u.id = h.created_by WHERE h.order_id = ? ORDER BY h.created_at DESC", [$id]);
        $customer  = $order['user_id'] ? Database::fetch("SELECT * FROM users WHERE id = ?", [$order['user_id']]) : null;
        $refunds   = Database::fetchAll("SELECT * FROM refunds WHERE order_id = ?", [$id]);
        $this->view('admin.orders.show', compact('order', 'items', 'payments', 'history', 'customer', 'refunds'));
    }

    public function updateStatus(int $id): void {
        CSRF::validateOrFail();
        $order  = $this->findOrder($id);
        $status = $this->request->post('status');
        $note   = $this->request->post('note', '');
        $notify = $this->request->boolean('notify', true);

        $validTransitions = [
            'pending'           => ['confirmed','cancelled','on_hold'],
            'confirmed'         => ['processing','cancelled'],
            'processing'        => ['shipped','cancelled'],
            'shipped'           => ['out_for_delivery','delivered'],
            'out_for_delivery'  => ['delivered'],
            'delivered'         => ['refunded'],
            'on_hold'           => ['confirmed','cancelled'],
            'cancelled'         => [],
            'refunded'          => [],
        ];

        if (!isset($validTransitions[$order['status']]) || !in_array($status, $validTransitions[$order['status']])) {
            $this->error("Cannot transition from '{$order['status']}' to '$status'.");
            return;
        }

        $updateData = ['status' => $status];
        switch ($status) {
            case 'confirmed':  $updateData['confirmed_at'] = now(); break;
            case 'shipped':    $updateData['shipped_at']   = now(); break;
            case 'delivered':  $updateData['delivered_at'] = now(); break;
            case 'cancelled':
                $updateData['cancelled_at']        = now();
                $updateData['cancellation_reason'] = $note;
                $this->restoreInventory($order);
                break;
        }

        Database::update('orders', $updateData, 'id = ?', [$id]);
        Database::insert('order_status_history', [
            'order_id'        => $id,
            'status'          => $status,
            'comment'         => $note,
            'notify_customer' => $notify ? 1 : 0,
            'created_by'      => $this->currentUser['id'],
        ]);

        if ($notify && $order['user_id']) {
            $customer = Database::fetch("SELECT * FROM users WHERE id = ?", [$order['user_id']]);
            (new EmailService())->sendOrderStatusUpdate($customer, $order, $status, $note);
        }

        $this->auditLog('order.status_update', 'Order', $id, ['status' => $order['status']], ['status' => $status]);
        $this->success("Order status updated to $status.");
    }

    public function updateTracking(int $id): void {
        CSRF::validateOrFail();
        $this->findOrder($id);
        $data = [
            'tracking_number' => $this->request->post('tracking_number', ''),
            'tracking_url'    => $this->request->post('tracking_url', ''),
            'shipping_method' => $this->request->post('shipping_method', ''),
        ];
        Database::update('orders', $data, 'id = ?', [$id]);
        $this->success('Tracking info updated.');
    }

    public function createRefund(int $id): void {
        CSRF::validateOrFail();
        $order  = $this->findOrder($id);
        $amount = (float)$this->request->post('amount', 0);
        $reason = $this->request->post('reason', '');
        $items  = $this->request->post('items', []);

        if ($amount <= 0 || $amount > $order['total']) {
            $this->error('Invalid refund amount.');
            return;
        }

        try {
            $refundService = new RefundService();
            $refundId      = $refundService->createRefund($order, $amount, $reason, (array)$items);
            $this->auditLog('order.refund', 'Order', $id, [], ['amount' => $amount, 'reason' => $reason]);
            $this->success('Refund request created.', ['refund_id' => $refundId]);
        } catch (Throwable $e) {
            $this->error('Refund failed: ' . $e->getMessage());
        }
    }

    public function invoice(int $id): void {
        $order  = $this->findOrder($id);
        $items  = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$id]);
        $store  = $this->currentStore;
        $invoiceService = new InvoiceService();
        $pdf    = $invoiceService->generate($order, $items, $store);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="invoice-' . $order['order_number'] . '.pdf"');
        echo $pdf;
        exit;
    }

    public function export(): void {
        $status = $this->request->get('status', '');
        $from   = $this->request->get('from', date('Y-m-01'));
        $to     = $this->request->get('to', date('Y-m-d'));

        $sql    = "SELECT o.order_number, u.name as customer, u.email, o.status, o.payment_status,
                          o.subtotal, o.discount_amount, o.tax_amount, o.shipping_amount, o.total,
                          o.currency, o.created_at
                   FROM orders o LEFT JOIN users u ON u.id = o.user_id
                   WHERE o.store_id = ? AND DATE(o.created_at) BETWEEN ? AND ?";
        $params = [$this->storeId, $from, $to];
        if ($status) { $sql .= " AND o.status = ?"; $params[] = $status; }
        $orders  = Database::fetchAll($sql, $params);
        $headers = ['Order#', 'Customer', 'Email', 'Status', 'Payment', 'Subtotal', 'Discount', 'Tax', 'Shipping', 'Total', 'Currency', 'Date'];
        $csv     = implode(',', $headers) . "\n";
        foreach ($orders as $r) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"','""',(string)$v) . '"', array_values($r))) . "\n";
        }
        (new Response())->stream($csv, 'text/csv', "orders-{$from}-to-{$to}.csv");
    }

    private function findOrder(int $id): array {
        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$order) $this->abort(404, 'Order not found');
        return $order;
    }

    private function restoreInventory(array $order): void {
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
        foreach ($items as $item) {
            if (!$item['product_id']) continue;
            $product = Database::fetch("SELECT quantity FROM products WHERE id = ?", [$item['product_id']]);
            if (!$product) continue;
            $newQty = $product['quantity'] + $item['quantity'];
            Database::update('products', ['quantity' => $newQty], 'id = ?', [$item['product_id']]);
            Database::insert('inventory_logs', [
                'store_id'        => $this->storeId,
                'product_id'      => $item['product_id'],
                'type'            => 'return',
                'quantity_before' => $product['quantity'],
                'quantity_change' => $item['quantity'],
                'quantity_after'  => $newQty,
                'reference_type'  => 'order',
                'reference_id'    => $order['id'],
                'notes'           => 'Order cancelled - stock restored',
                'user_id'         => $this->currentUser['id'],
            ]);
        }
    }
}
