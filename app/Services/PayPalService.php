<?php

class PayPalService {
    private string $clientId;
    private string $clientSecret;
    private string $apiBase;

    public function __construct() {
        $this->clientId     = config('payment.paypal.client_id', '');
        $this->clientSecret = config('payment.paypal.client_secret', '');
        $mode = config('payment.paypal.mode', 'sandbox');
        $this->apiBase = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    public function createOrder(int $orderId, float $amount, array $store): array {
        $token = $this->getAccessToken();
        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => 'order_' . $orderId,
                'amount' => ['currency_code' => $store['currency'], 'value' => number_format($amount, 2, '.', '')],
            ]],
            'application_context' => [
                'return_url' => url("/payment/paypal/success?order={$orderId}&store={$store['slug']}"),
                'cancel_url' => url("/payment/paypal/cancel?order={$orderId}&store={$store['slug']}"),
            ],
        ];
        $result = $this->call('POST', '/v2/checkout/orders', $data, $token);
        $approveUrl = '';
        foreach ($result['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') { $approveUrl = $link['href']; break; }
        }
        Database::insert('payments', [
            'order_id' => $orderId, 'store_id' => $store['id'], 'gateway' => 'paypal',
            'gateway_order_id' => $result['id'], 'amount' => $amount,
            'currency' => $store['currency'], 'status' => 'pending',
        ]);
        return ['id' => $result['id'], 'approve_url' => $approveUrl];
    }

    public function captureOrder(string $paypalOrderId, string $orderNumber): bool {
        $token = $this->getAccessToken();
        $result = $this->call('POST', "/v2/checkout/orders/$paypalOrderId/capture", new stdClass(), $token);
        if (($result['status'] ?? '') === 'COMPLETED') {
            $order = Database::fetch("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);
            if ($order) {
                Database::update('orders', ['payment_status' => 'paid', 'status' => 'confirmed', 'confirmed_at' => now()], 'id = ?', [$order['id']]);
                Database::update('payments', ['status' => 'completed', 'paid_at' => now()], 'gateway_order_id = ?', [$paypalOrderId]);
            }
            return true;
        }
        return false;
    }

    public function refund(string $captureId, float $amount, string $currency): array {
        $token = $this->getAccessToken();
        return $this->call('POST', "/v2/payments/captures/$captureId/refund", [
            'amount' => ['value' => number_format($amount, 2, '.', ''), 'currency_code' => $currency],
        ], $token);
    }

    public function handleWebhook(string $payload, array $headers): void {
        $event = json_decode($payload, true);
        $eventType = $event['event_type'] ?? '';
        if ($eventType === 'CHECKOUT.ORDER.APPROVED' || $eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $resourceId = $event['resource']['supplementary_data']['related_ids']['order_id']
                       ?? $event['resource']['id'] ?? null;
            if ($resourceId) {
                $payment = Database::fetch("SELECT * FROM payments WHERE gateway_order_id = ?", [$resourceId]);
                if ($payment) {
                    Database::update('payments', ['status' => 'completed', 'paid_at' => now()], 'id = ?', [$payment['id']]);
                    Database::update('orders', ['payment_status' => 'paid', 'status' => 'confirmed'], 'id = ?', [$payment['order_id']]);
                }
            }
        }
    }

    private function getAccessToken(): string {
        $ch = curl_init($this->apiBase . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->clientId . ':' . $this->clientSecret,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        return $data['access_token'] ?? '';
    }

    private function call(string $method, string $path, mixed $data, string $token): array {
        $ch = curl_init($this->apiBase . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $token],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? [];
    }
}
