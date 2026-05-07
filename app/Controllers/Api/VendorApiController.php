<?php
namespace Api;

use Database;

class VendorApiController extends ApiController {
    private function vendor(): array {
        $this->requireUser();
        $v = Database::fetch("SELECT * FROM vendors WHERE user_id = ? AND status = 'active'", [$this->user['id']]);
        if (!$v) $this->error('Not a vendor', 403);
        return $v;
    }
    public function profile(): void {
        $this->ok($this->vendor());
    }
    public function updateProfile(): void {
        $v = $this->vendor();
        $data = $this->request->only(['business_name','description','phone','address','city','state','country','zip_code']);
        Database::update('vendors', $data, 'id = ?', [$v['id']]);
        $this->ok(null, 'Updated');
    }
    public function products(): void {
        $v = $this->vendor();
        $this->ok($this->paginated("SELECT * FROM products WHERE vendor_id = ? ORDER BY created_at DESC", [$v['id']], 20));
    }
    public function storeProduct(): void {
        $v = $this->vendor();
        $data = $this->validate(['name' => 'required', 'price' => 'required|numeric|min:0']);
        $data = array_merge($data, [
            'store_id' => $v['store_id'], 'vendor_id' => $v['id'],
            'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$data['name']),'-')) . '-' . substr(uniqid(),-4),
            'status' => 'draft',
            'sku' => $this->request->post('sku', ''),
            'quantity' => (int)$this->request->post('quantity', 0),
            'description' => $this->request->post('description', ''),
        ]);
        $id = Database::insert('products', $data);
        $this->ok(['id' => $id], 'Product created');
    }
    public function updateProduct(int $id): void {
        $v = $this->vendor();
        $p = Database::fetch("SELECT id FROM products WHERE id = ? AND vendor_id = ?", [$id, $v['id']]);
        if (!$p) $this->error('Not found', 404);
        Database::update('products', $this->request->only(['name','price','quantity','description']), 'id = ?', [$id]);
        $this->ok(null, 'Updated');
    }
    public function destroyProduct(int $id): void {
        $v = $this->vendor();
        Database::delete('products', 'id = ? AND vendor_id = ?', [$id, $v['id']]);
        $this->ok(null, 'Deleted');
    }
    public function orders(): void {
        $v = $this->vendor();
        $sql = "SELECT DISTINCT o.* FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE oi.vendor_id = ? ORDER BY o.created_at DESC";
        $this->ok($this->paginated($sql, [$v['id']], 20));
    }
    public function earnings(): void {
        $v = $this->vendor();
        $earnings = Database::fetchAll(
            "SELECT DATE_FORMAT(o.created_at, '%Y-%m') month, SUM(oi.vendor_amount) earnings
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE oi.vendor_id = ? AND o.payment_status = 'paid'
             GROUP BY month ORDER BY month DESC LIMIT 12", [$v['id']]
        );
        $this->ok(['monthly' => $earnings, 'balance' => $v['balance'], 'total_revenue' => $v['total_revenue']]);
    }
}
