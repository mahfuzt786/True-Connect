<?php
namespace Api;

use CartService;
use Database;

class CartApiController extends ApiController {

    public function show(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $svc   = new CartService($store['id']);
        $this->ok($svc->getCart());
    }

    public function add(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $data  = $this->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'nullable|integer|min:1',
        ]);
        try {
            $svc = new CartService($store['id']);
            $svc->add($data['product_id'], $this->request->post('variant_id') ?: null, (int)($data['quantity'] ?? 1));
            $this->ok($svc->getCart(), 'Added to cart');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    public function update(string $storeSlug, int $id): void {
        $store = $this->getStore($storeSlug);
        $svc   = new CartService($store['id']);
        $svc->updateQuantity($id, (int)$this->request->post('quantity'));
        $this->ok($svc->getCart());
    }

    public function remove(string $storeSlug, int $id): void {
        $store = $this->getStore($storeSlug);
        $svc   = new CartService($store['id']);
        $svc->remove($id);
        $this->ok($svc->getCart());
    }

    public function applyCoupon(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $code  = $this->request->post('code', '');
        $svc   = new CartService($store['id']);
        try {
            $discount = $svc->applyCoupon($code);
            $this->ok(['discount' => $discount, 'cart' => $svc->getCart()]);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    public function removeCoupon(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $cart  = Database::fetch("SELECT id FROM carts WHERE store_id = ? AND user_id = ?", [$store['id'], $this->user['id'] ?? 0]);
        if ($cart) Database::update('carts', ['coupon_id' => null, 'coupon_discount' => 0], 'id = ?', [$cart['id']]);
        $svc = new CartService($store['id']);
        $this->ok($svc->getCart());
    }

    private function getStore(string $slug): array {
        $store = Database::fetch("SELECT * FROM stores WHERE (slug = ? OR subdomain = ?) AND status = 'active'", [$slug, $slug]);
        if (!$store) $this->error('Store not found', 404);
        return $store;
    }
}
