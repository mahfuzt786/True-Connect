<?php
namespace Api;

use Database;
use StripeService;

class PaymentApiController extends ApiController {
    public function createIntent(): void {
        $this->requireUser();
        $data = $this->validate(['order_id' => 'required|integer', 'gateway' => 'required|in:stripe,razorpay,paypal']);
        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$data['order_id'], $this->user['id']]);
        if (!$order) $this->error('Order not found', 404);
        $store = Database::fetch("SELECT * FROM stores WHERE id = ?", [$order['store_id']]);

        try {
            $result = match ($data['gateway']) {
                'stripe' => (new StripeService())->createCheckoutSession($order['id'], $order['order_number'], $order['total'], $store),
                default  => ['error' => 'Gateway not supported via API yet'],
            };
            $this->ok($result);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
    public function confirm(): void {
        $this->requireUser();
        $data = $this->validate(['order_id' => 'required|integer', 'payment_id' => 'required']);
        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$data['order_id'], $this->user['id']]);
        if (!$order) $this->error('Order not found', 404);
        $this->ok(['status' => $order['payment_status']]);
    }
}
