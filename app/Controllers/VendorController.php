<?php

class VendorController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        if ($this->currentStore['type'] !== 'marketplace' && empty($this->currentStore['marketplace_enabled'])) {
            $this->abort(403, 'Marketplace not enabled for your plan');
        }
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $status = $this->request->get('status', '');
        // u.email aliased so it doesn't collide with vendors.email when paginate wraps the SQL.
        $sql    = "SELECT v.*, u.email AS user_email, u.name AS user_name FROM vendors v JOIN users u ON u.id = v.user_id WHERE v.store_id = ?";
        $params = [$this->storeId];
        if ($status) { $sql .= " AND v.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY v.created_at DESC";
        $vendors = $this->paginate($sql, $params, 25);
        $this->view('admin.vendors.index', compact('vendors','status'));
    }

    public function show(int $id): void {
        $vendor = Database::fetch("SELECT v.*, u.email AS user_email, u.name AS user_name FROM vendors v JOIN users u ON u.id = v.user_id WHERE v.id = ? AND v.store_id = ?", [$id, $this->storeId]);
        if (!$vendor) $this->abort(404);
        $stats = [
            'products' => (int)(Database::fetch("SELECT COUNT(*) c FROM products WHERE vendor_id = ?", [$id])['c']),
            'orders'   => (int)(Database::fetch("SELECT COUNT(DISTINCT order_id) c FROM order_items WHERE vendor_id = ?", [$id])['c']),
            'revenue'  => (float)(Database::fetch("SELECT COALESCE(SUM(vendor_amount),0) c FROM order_items WHERE vendor_id = ?", [$id])['c']),
        ];
        $payouts = Database::fetchAll("SELECT * FROM vendor_payouts WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
        $this->view('admin.vendors.show', compact('vendor','stats','payouts'));
    }

    public function approve(int $id): void {
        $vendor = $this->find($id);
        Database::update('vendors', [
            'status' => 'active', 'approved_at' => now(), 'approved_by' => $this->currentUser['id']
        ], 'id = ?', [$id]);
        Database::update('users', ['role' => 'vendor'], 'id = ?', [$vendor['user_id']]);
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$vendor['user_id']]);
        (new EmailService())->sendVendorApproved($user, $vendor);
        $this->success('Vendor approved.');
    }

    public function reject(int $id): void {
        CSRF::validateOrFail();
        $reason = $this->request->post('reason', '');
        Database::update('vendors', ['status' => 'rejected', 'rejection_reason' => $reason], 'id = ?', [$id]);
        $this->success('Vendor application rejected.');
    }

    public function suspend(int $id): void {
        Database::update('vendors', ['status' => 'suspended'], 'id = ?', [$id]);
        $this->success('Vendor suspended.');
    }

    public function payout(int $id): void {
        CSRF::validateOrFail();
        $vendor = $this->find($id);
        $amount = (float)$this->request->post('amount', 0);
        $method = $this->request->post('method', 'manual');
        if ($amount <= 0 || $amount > $vendor['balance']) { $this->error('Invalid amount.'); return; }
        Database::transaction(function() use ($vendor, $amount, $method) {
            Database::insert('vendor_payouts', [
                'vendor_id'    => $vendor['id'],
                'store_id'     => $this->storeId,
                'amount'       => $amount,
                'currency'     => $this->currentStore['currency'],
                'method'       => $method,
                'status'       => 'completed',
                'processed_by' => $this->currentUser['id'],
                'processed_at' => now(),
            ]);
            Database::update('vendors', ['balance' => $vendor['balance'] - $amount], 'id = ?', [$vendor['id']]);
        });
        $this->success('Payout recorded.');
    }

    public function payouts(): void {
        $sql = "SELECT vp.*, v.business_name FROM vendor_payouts vp JOIN vendors v ON v.id = vp.vendor_id WHERE vp.store_id = ? ORDER BY vp.created_at DESC";
        $payouts = $this->paginate($sql, [$this->storeId], 25);
        $this->view('admin.vendors.payouts', compact('payouts'));
    }

    private function find(int $id): array {
        $vendor = Database::fetch("SELECT * FROM vendors WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$vendor) $this->abort(404);
        return $vendor;
    }
}
