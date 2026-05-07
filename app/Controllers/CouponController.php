<?php

class CouponController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $coupons = $this->paginate("SELECT * FROM coupons WHERE store_id = ? ORDER BY created_at DESC", [$this->storeId], 20);
        $this->view('admin.coupons.index', compact('coupons'));
    }

    public function store(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'code'  => 'required|alpha_dash|min:3',
            'type'  => 'required|in:percentage,fixed,free_shipping,buy_x_get_y',
            'value' => 'required|numeric|min:0',
        ]);
        $data['store_id']           = $this->storeId;
        $data['code']               = strtoupper($data['code']);
        $data['min_order_amount']   = $this->request->post('min_order_amount') ?: null;
        $data['max_discount_amount']= $this->request->post('max_discount_amount') ?: null;
        $data['usage_limit']        = $this->request->post('usage_limit') ?: null;
        $data['starts_at']          = $this->request->post('starts_at') ?: null;
        $data['expires_at']         = $this->request->post('expires_at') ?: null;
        Database::insert('coupons', $data);
        $this->success('Coupon created.');
    }

    public function edit(int $id): void {
        $coupon = $this->find($id);
        $this->view('admin.coupons.edit', compact('coupon'));
    }

    public function update(int $id): void {
        CSRF::validateOrFail();
        $this->find($id);
        $data = $this->request->only(['code','type','value','min_order_amount','max_discount_amount','usage_limit','starts_at','expires_at']);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $this->request->boolean('is_active', true) ? 1 : 0;
        Database::update('coupons', $data, 'id = ?', [$id]);
        $this->success('Coupon updated.');
    }

    public function destroy(int $id): void {
        $this->find($id);
        Database::delete('coupons', 'id = ?', [$id]);
        $this->success('Coupon deleted.');
    }

    private function find(int $id): array {
        $c = Database::fetch("SELECT * FROM coupons WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$c) $this->abort(404);
        return $c;
    }
}
