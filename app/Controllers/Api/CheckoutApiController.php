<?php
namespace Api;

use CheckoutService;
use Database;

class CheckoutApiController extends ApiController {
    public function process(string $storeSlug): void {
        $store = Database::fetch("SELECT * FROM stores WHERE (slug = ? OR subdomain = ?) AND status = 'active'", [$storeSlug, $storeSlug]);
        if (!$store) $this->error('Store not found', 404);
        try {
            $service = new CheckoutService($store['id']);
            $result  = $service->process($this->request->all());
            $this->ok($result, 'Order placed');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
    public function shippingRates(string $storeSlug): void {
        $store = Database::fetch("SELECT id FROM stores WHERE slug = ?", [$storeSlug]);
        if (!$store) $this->error('Store not found', 404);
        $rates = Database::fetchAll(
            "SELECT sr.* FROM shipping_rates sr
             JOIN shipping_zones sz ON sz.id = sr.zone_id
             WHERE sz.store_id = ? AND sr.is_active = 1",
            [$store['id']]
        );
        $this->ok($rates);
    }
}
