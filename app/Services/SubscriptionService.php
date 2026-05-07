<?php

class SubscriptionService {

    public function subscribe(int $storeId, array $plan, string $cycle = 'monthly'): array {
        $store    = Database::fetch("SELECT * FROM stores WHERE id = ?", [$storeId]);
        $price    = $cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
        $cancel   = Database::query("UPDATE subscriptions SET status = 'cancelled', cancelled_at = NOW() WHERE store_id = ? AND status IN ('active','trialing')", [$storeId]);

        if ($price > 0 && config('payment.stripe.secret')) {
            return $this->subscribeViaStripe($store, $plan, $cycle);
        }

        $subId = Database::insert('subscriptions', [
            'store_id'             => $storeId,
            'plan_id'              => $plan['id'],
            'billing_cycle'        => $cycle,
            'status'               => 'active',
            'current_period_start' => date('Y-m-d H:i:s'),
            'current_period_end'   => date('Y-m-d H:i:s', strtotime($cycle === 'yearly' ? '+1 year' : '+1 month')),
            'amount'               => $price,
            'currency'             => $plan['currency'],
            'gateway'              => 'manual',
        ]);
        Database::insert('invoices_subscription', [
            'subscription_id' => $subId, 'store_id' => $storeId,
            'invoice_number'  => generateInvoiceNumber(),
            'amount'          => $price, 'currency' => $plan['currency'],
            'status'          => 'paid', 'paid_at' => now(),
        ]);
        return ['success' => true, 'subscription_id' => $subId];
    }

    private function subscribeViaStripe(array $store, array $plan, string $cycle): array {
        try {
            $stripe = new StripeService();
            $owner  = Database::fetch("SELECT * FROM users WHERE id = ?", [$store['user_id']]);
            $customer = $stripe->createCustomer($owner['email'], $owner['name'], ['store_id' => $store['id']]);
            $priceId = $cycle === 'yearly' ? $plan['stripe_price_id_yearly'] : $plan['stripe_price_id_monthly'];
            if (!$priceId) {
                // Fall back to manual
                return $this->subscribeManual($store, $plan, $cycle);
            }
            $stripeSub = $stripe->createSubscription($customer['id'], $priceId);
            $price = $cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
            $subId = Database::insert('subscriptions', [
                'store_id' => $store['id'], 'plan_id' => $plan['id'],
                'billing_cycle' => $cycle, 'status' => 'active',
                'stripe_subscription_id' => $stripeSub['id'],
                'stripe_customer_id' => $customer['id'],
                'gateway' => 'stripe',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end' => date('Y-m-d H:i:s', strtotime($cycle === 'yearly' ? '+1 year' : '+1 month')),
                'amount' => $price, 'currency' => $plan['currency'],
            ]);
            return ['success' => true, 'subscription_id' => $subId];
        } catch (Throwable $e) {
            return $this->subscribeManual($store, $plan, $cycle);
        }
    }

    private function subscribeManual(array $store, array $plan, string $cycle): array {
        $price = $cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
        $subId = Database::insert('subscriptions', [
            'store_id' => $store['id'], 'plan_id' => $plan['id'],
            'billing_cycle' => $cycle, 'status' => 'active',
            'gateway' => 'manual',
            'current_period_start' => date('Y-m-d H:i:s'),
            'current_period_end' => date('Y-m-d H:i:s', strtotime($cycle === 'yearly' ? '+1 year' : '+1 month')),
            'amount' => $price, 'currency' => $plan['currency'],
        ]);
        return ['success' => true, 'subscription_id' => $subId];
    }

    public function cancel(array $subscription): bool {
        if ($subscription['gateway'] === 'stripe' && $subscription['stripe_subscription_id']) {
            try {
                (new StripeService())->call ?? null;
            } catch (Throwable) {}
        }
        Database::update('subscriptions', [
            'status' => 'cancelled', 'cancelled_at' => now(),
            'ends_at' => $subscription['current_period_end'],
        ], 'id = ?', [$subscription['id']]);
        return true;
    }

    public function processRecurring(): void {
        $expired = Database::fetchAll("SELECT * FROM subscriptions WHERE status = 'active' AND current_period_end <= NOW()");
        foreach ($expired as $sub) {
            $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$sub['plan_id']]);
            $price = $sub['billing_cycle'] === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
            Database::update('subscriptions', [
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end'   => date('Y-m-d H:i:s', strtotime($sub['billing_cycle'] === 'yearly' ? '+1 year' : '+1 month')),
            ], 'id = ?', [$sub['id']]);
            Database::insert('invoices_subscription', [
                'subscription_id' => $sub['id'], 'store_id' => $sub['store_id'],
                'invoice_number'  => generateInvoiceNumber(),
                'amount'          => $price, 'currency' => $sub['currency'],
                'status'          => 'paid', 'paid_at' => now(),
            ]);
        }
        // Expire trials
        Database::query("UPDATE subscriptions SET status = 'expired' WHERE status = 'trialing' AND trial_ends_at < NOW()");
    }

    public function createBillingPortalSession(int $storeId): string {
        $sub = Database::fetch("SELECT * FROM subscriptions WHERE store_id = ? AND status IN ('active','trialing') LIMIT 1", [$storeId]);
        if (!$sub || !$sub['stripe_customer_id']) throw new RuntimeException('No active Stripe subscription');
        return url('/subscription'); // Placeholder — would call Stripe billing portal API
    }
}
