<?php

class RefundService {

    public function createRefund(array $order, float $amount, string $reason, array $items = []): int {
        return Database::transaction(function() use ($order, $amount, $reason, $items) {
            $refundId = Database::insert('refunds', [
                'order_id'      => $order['id'],
                'store_id'      => $order['store_id'],
                'refund_number' => 'RFN-' . strtoupper(uniqid()),
                'amount'        => $amount,
                'reason'        => $reason,
                'status'        => 'processed',
                'items'         => json_encode($items),
                'processed_by'  => Auth::id(),
                'processed_at'  => now(),
            ]);

            // Process gateway refund
            $payment = Database::fetch("SELECT * FROM payments WHERE order_id = ? AND status = 'completed' LIMIT 1", [$order['id']]);
            if ($payment) {
                try {
                    switch ($payment['gateway']) {
                        case 'stripe':
                            (new StripeService())->refund($payment['gateway_payment_id'], $amount);
                            break;
                        case 'razorpay':
                            (new RazorpayService())->refund($payment['gateway_payment_id'], $amount);
                            break;
                        case 'paypal':
                            (new PayPalService())->refund($payment['gateway_payment_id'], $amount, $order['currency']);
                            break;
                    }
                } catch (Throwable $e) {
                    logError('Refund gateway error: ' . $e->getMessage());
                }
            }

            // Update order
            $newStatus = $amount >= $order['total'] ? 'refunded' : 'partially_refunded';
            Database::update('orders', ['payment_status' => $newStatus], 'id = ?', [$order['id']]);

            return $refundId;
        });
    }
}
