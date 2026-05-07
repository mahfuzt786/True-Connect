<?php

class RazorpayService {
    private string $keyId;
    private string $keySecret;
    private string $apiBase = 'https://api.razorpay.com/v1';

    public function __construct() {
        $this->keyId     = config('payment.razorpay.key_id', '');
        $this->keySecret = config('payment.razorpay.key_secret', '');
    }

    public function createOrder(int $orderId, float $amount, array $store): array {
        $params = [
            'amount'   => (int)round($amount * 100),
            'currency' => $store['currency'],
            'receipt'  => 'order_' . $orderId,
            'notes'    => ['order_id' => (string)$orderId, 'store_id' => (string)$store['id']],
        ];
        $rOrder = $this->call('POST', '/orders', $params);
        Database::insert('payments', [
            'order_id' => $orderId, 'store_id' => $store['id'], 'gateway' => 'razorpay',
            'gateway_order_id' => $rOrder['id'], 'amount' => $amount,
            'currency' => $store['currency'], 'status' => 'pending',
        ]);
        return $rOrder;
    }

    public function verifyPayment(array $payload): bool {
        $expected = hash_hmac('sha256', $payload['razorpay_order_id'] . '|' . $payload['razorpay_payment_id'], $this->keySecret);
        if (!hash_equals($expected, $payload['razorpay_signature'])) {
            throw new RuntimeException('Invalid signature');
        }
        $payment = Database::fetch("SELECT * FROM payments WHERE gateway_order_id = ?", [$payload['razorpay_order_id']]);
        if ($payment) {
            Database::update('payments', [
                'status' => 'completed', 'paid_at' => now(),
                'gateway_payment_id' => $payload['razorpay_payment_id']
            ], 'id = ?', [$payment['id']]);
            Database::update('orders', ['payment_status' => 'paid', 'status' => 'confirmed', 'confirmed_at' => now()], 'id = ?', [$payment['order_id']]);
        }
        return true;
    }

    public function refund(string $paymentId, ?float $amount = null): array {
        $params = $amount ? ['amount' => (int)round($amount * 100)] : [];
        return $this->call('POST', "/payments/$paymentId/refund", $params);
    }

    public function handleWebhook(string $payload, string $signature): void {
        $secret = config('payment.razorpay.webhook_secret');
        $expected = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expected, $signature)) throw new RuntimeException('Invalid webhook signature');
        $event = json_decode($payload, true);
        if (($event['event'] ?? '') === 'payment.captured') {
            $payment = $event['payload']['payment']['entity'];
            $p = Database::fetch("SELECT * FROM payments WHERE gateway_order_id = ?", [$payment['order_id']]);
            if ($p) {
                Database::update('payments', ['status' => 'completed', 'paid_at' => now()], 'id = ?', [$p['id']]);
                Database::update('orders', ['payment_status' => 'paid', 'status' => 'confirmed'], 'id = ?', [$p['order_id']]);
            }
        }
    }

    private function call(string $method, string $path, array $params = []): array {
        $ch = curl_init($this->apiBase . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->keyId . ':' . $this->keySecret,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);
        if ($params) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);
        if ($code >= 400) throw new RuntimeException($data['error']['description'] ?? 'Razorpay API error');
        return $data ?? [];
    }
}
