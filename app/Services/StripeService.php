<?php

class StripeService {
    private string $secretKey;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct(?string $secretKey = null) {
        $this->secretKey = $secretKey ?: config('payment.stripe.secret', '');
    }

    public function createCheckoutSession(int $orderId, string $orderNumber, float $amount, array $store): array {
        $params = [
            'mode'                  => 'payment',
            'payment_method_types[]'=> 'card',
            'line_items[0][price_data][currency]'      => strtolower($store['currency']),
            'line_items[0][price_data][product_data][name]' => "Order #$orderNumber",
            'line_items[0][price_data][unit_amount]'   => (int)round($amount * 100),
            'line_items[0][quantity]' => 1,
            'success_url'           => url("/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}&order=$orderNumber&store={$store['slug']}"),
            'cancel_url'            => url("/payment/stripe/cancel?order=$orderNumber&store={$store['slug']}"),
            'metadata[order_id]'    => $orderId,
            'metadata[store_id]'    => $store['id'],
        ];
        $session = $this->call('POST', '/checkout/sessions', $params);
        Database::insert('payments', [
            'order_id' => $orderId, 'store_id' => $store['id'], 'gateway' => 'stripe',
            'gateway_payment_id' => $session['id'], 'amount' => $amount,
            'currency' => $store['currency'], 'status' => 'pending',
        ]);
        return $session;
    }

    public function verifyCheckoutSession(string $sessionId, string $orderNumber): bool {
        $session = $this->call('GET', "/checkout/sessions/$sessionId");
        if (($session['payment_status'] ?? '') === 'paid') {
            $order = Database::fetch("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);
            if ($order) {
                Database::update('orders', ['payment_status' => 'paid', 'status' => 'confirmed', 'confirmed_at' => now()], 'id = ?', [$order['id']]);
                Database::update('payments', ['status' => 'completed', 'paid_at' => now(), 'gateway_response' => json_encode($session)], 'order_id = ? AND gateway = ?', [$order['id'], 'stripe']);
                return true;
            }
        }
        return false;
    }

    public function createSubscription(string $customerId, string $priceId, ?string $couponCode = null): array {
        $params = [
            'customer'        => $customerId,
            'items[0][price]' => $priceId,
        ];
        if ($couponCode) $params['coupon'] = $couponCode;
        return $this->call('POST', '/subscriptions', $params);
    }

    public function createCustomer(string $email, string $name, array $metadata = []): array {
        $params = ['email' => $email, 'name' => $name];
        foreach ($metadata as $k => $v) $params["metadata[$k]"] = $v;
        return $this->call('POST', '/customers', $params);
    }

    public function refund(string $paymentIntentId, ?float $amount = null): array {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amount !== null) $params['amount'] = (int)round($amount * 100);
        return $this->call('POST', '/refunds', $params);
    }

    public function handleWebhook(string $payload, string $signature): void {
        $event = json_decode($payload, true);
        if (!$event) throw new RuntimeException('Invalid payload');

        switch ($event['type']) {
            case 'checkout.session.completed':
                $session = $event['data']['object'];
                $orderId = $session['metadata']['order_id'] ?? null;
                if ($orderId) {
                    Database::update('orders', ['payment_status' => 'paid', 'status' => 'confirmed', 'confirmed_at' => now()], 'id = ?', [$orderId]);
                    Database::update('payments', ['status' => 'completed', 'paid_at' => now()], 'order_id = ?', [$orderId]);
                }
                break;
            case 'invoice.paid':
                $invoice = $event['data']['object'];
                $sub = Database::fetch("SELECT * FROM subscriptions WHERE stripe_subscription_id = ?", [$invoice['subscription'] ?? '']);
                if ($sub) {
                    Database::insert('invoices_subscription', [
                        'subscription_id'    => $sub['id'],
                        'store_id'           => $sub['store_id'],
                        'invoice_number'     => generateInvoiceNumber(),
                        'amount'             => $invoice['amount_paid'] / 100,
                        'currency'           => strtoupper($invoice['currency']),
                        'status'             => 'paid',
                        'gateway'            => 'stripe',
                        'gateway_invoice_id' => $invoice['id'],
                        'paid_at'            => now(),
                    ]);
                }
                break;
            case 'customer.subscription.deleted':
                $sub = $event['data']['object'];
                Database::update('subscriptions', ['status' => 'cancelled', 'cancelled_at' => now()], 'stripe_subscription_id = ?', [$sub['id']]);
                break;
        }
    }

    private function call(string $method, string $path, array $params = []): array {
        $ch = curl_init($this->apiBase . $path . ($method === 'GET' && $params ? '?' . http_build_query($params) : ''));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->secretKey],
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 30,
        ]);
        if ($method !== 'GET' && $params) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);
        if ($code >= 400) throw new RuntimeException($data['error']['message'] ?? 'Stripe API error');
        return $data ?? [];
    }
}
