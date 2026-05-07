<?php

class CheckoutService {
    private int $storeId;
    private array $store;

    public function __construct(int $storeId) {
        $this->storeId = $storeId;
        $this->store   = Database::fetch("SELECT * FROM stores WHERE id = ?", [$storeId]);
    }

    public function process(array $input): array {
        $cartService = new CartService($this->storeId);
        $cart        = $cartService->getCart();
        if (empty($cart['items'])) throw new RuntimeException('Cart is empty');

        $billingAddress  = $this->extractAddress($input, 'billing');
        $shippingAddress = !empty($input['same_as_billing']) ? $billingAddress : $this->extractAddress($input, 'shipping');
        $paymentMethod   = $input['payment_method'] ?? 'cod';

        $shipping = $this->calculateShipping($cart, $shippingAddress, (int)($input['shipping_rate_id'] ?? 0));
        $tax      = $this->calculateTax($cart, $shippingAddress);
        $total    = $cart['total'] + $shipping + $tax;

        return Database::transaction(function() use ($cart, $billingAddress, $shippingAddress, $paymentMethod, $shipping, $tax, $total, $input) {
            $orderNumber = generateOrderNumber();
            $userId      = Auth::id();
            if (!$userId && !empty($input['create_account'])) {
                $userId = Auth::register([
                    'name'     => $billingAddress['first_name'] . ' ' . $billingAddress['last_name'],
                    'email'    => $billingAddress['email'],
                    'password' => $input['password'] ?? bin2hex(random_bytes(8)),
                    'role'     => 'customer',
                ]);
                Database::update('users', ['email_verified_at' => now(), 'status' => 'active'], 'id = ?', [$userId]);
                Auth::login(Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]));
            }

            $orderId = Database::insert('orders', [
                'store_id'         => $this->storeId,
                'user_id'          => $userId,
                'order_number'     => $orderNumber,
                'status'           => 'pending',
                'payment_status'   => 'pending',
                'payment_method'   => $paymentMethod,
                'subtotal'         => $cart['subtotal'],
                'discount_amount'  => $cart['coupon_discount'],
                'coupon_id'        => $cart['coupon']['id'] ?? null,
                'coupon_code'      => $cart['coupon']['code'] ?? null,
                'tax_amount'       => $tax,
                'shipping_amount'  => $shipping,
                'total'            => $total,
                'currency'         => $this->store['currency'],
                'billing_address'  => json_encode($billingAddress),
                'shipping_address' => json_encode($shippingAddress),
                'customer_notes'   => $input['notes'] ?? '',
                'ip_address'       => (new Request())->ip(),
                'user_agent'       => substr((new Request())->userAgent(), 0, 255),
            ]);

            // Create order items + decrement inventory
            foreach ($cart['items'] as $item) {
                $product   = Database::fetch("SELECT * FROM products WHERE id = ?", [$item['product_id']]);
                $vendor    = $product['vendor_id'] ? Database::fetch("SELECT * FROM vendors WHERE id = ?", [$product['vendor_id']]) : null;
                $commRate  = $vendor['commission_rate'] ?? $this->store['commission_rate'];
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $commission = $vendor ? ($itemTotal * $commRate / 100) : 0;
                $vendorAmount = $itemTotal - $commission;

                Database::insert('order_items', [
                    'order_id'         => $orderId,
                    'product_id'       => $product['id'],
                    'variant_id'       => $item['variant_id'],
                    'vendor_id'        => $product['vendor_id'],
                    'product_name'     => $product['name'],
                    'sku'              => $product['sku'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'total'            => $itemTotal,
                    'vendor_commission'=> $commission,
                    'vendor_amount'    => $vendorAmount,
                    'product_snapshot' => json_encode($product),
                ]);

                if ($product['track_inventory']) {
                    if ($item['variant_id']) {
                        Database::query("UPDATE product_variants SET quantity = quantity - ? WHERE id = ?", [$item['quantity'], $item['variant_id']]);
                    } else {
                        Database::query("UPDATE products SET quantity = quantity - ?, sales_count = sales_count + ? WHERE id = ?", [$item['quantity'], $item['quantity'], $product['id']]);
                    }
                }

                // Update vendor balance (held until order delivered)
                if ($vendor) {
                    Database::query("UPDATE vendors SET total_sales = total_sales + ?, total_revenue = total_revenue + ? WHERE id = ?", [$item['quantity'], $vendorAmount, $vendor['id']]);
                }
            }

            // Update coupon usage
            if (!empty($cart['coupon']['id'])) {
                Database::query("UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?", [$cart['coupon']['id']]);
            }

            // Status history
            Database::insert('order_status_history', [
                'order_id' => $orderId, 'status' => 'pending', 'comment' => 'Order placed', 'notify_customer' => 1,
            ]);

            // Process payment
            $result = $this->processPayment($orderId, $orderNumber, $total, $paymentMethod, $userId);

            // Clear cart
            $cartService = new CartService($this->storeId);
            $cartService->clear();

            // Send order confirmation email
            if ($userId) {
                $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);
                $user  = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
                (new EmailService())->sendOrderConfirmation($user, $order);
            }

            return array_merge($result, ['order_id' => $orderId, 'order_number' => $orderNumber]);
        });
    }

    private function extractAddress(array $input, string $prefix): array {
        return [
            'first_name'   => $input["{$prefix}_first_name"] ?? '',
            'last_name'    => $input["{$prefix}_last_name"] ?? '',
            'email'        => $input["{$prefix}_email"] ?? '',
            'phone'        => $input["{$prefix}_phone"] ?? '',
            'address_line1'=> $input["{$prefix}_address_line1"] ?? '',
            'address_line2'=> $input["{$prefix}_address_line2"] ?? '',
            'city'         => $input["{$prefix}_city"] ?? '',
            'state'        => $input["{$prefix}_state"] ?? '',
            'country'      => $input["{$prefix}_country"] ?? '',
            'zip_code'     => $input["{$prefix}_zip_code"] ?? '',
        ];
    }

    private function calculateShipping(array $cart, array $address, int $rateId): float {
        if ($rateId) {
            $rate = Database::fetch("SELECT * FROM shipping_rates WHERE id = ?", [$rateId]);
            if ($rate) return (float)$rate['rate'];
        }
        $rates = Database::fetchAll(
            "SELECT sr.* FROM shipping_rates sr JOIN shipping_zones sz ON sz.id = sr.zone_id
             WHERE sz.store_id = ? AND sr.is_active = 1 LIMIT 1",
            [$this->storeId]
        );
        return $rates ? (float)$rates[0]['rate'] : 0;
    }

    private function calculateTax(array $cart, array $address): float {
        $taxable = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_price'], $cart['items']));
        $rate    = Database::fetch("SELECT rate FROM tax_rates WHERE store_id = ? AND (country = ? OR country IS NULL OR country = '') AND is_active = 1 ORDER BY priority LIMIT 1",
            [$this->storeId, $address['country'] ?? '']);
        $taxRate = $rate ? (float)$rate['rate'] : (float)$this->store['tax_rate'];
        return ($taxable - $cart['coupon_discount']) * ($taxRate / 100);
    }

    private function processPayment(int $orderId, string $orderNumber, float $amount, string $method, ?int $userId): array {
        switch ($method) {
            case 'cod':
                Database::insert('payments', [
                    'order_id' => $orderId, 'store_id' => $this->storeId, 'gateway' => 'cod',
                    'amount' => $amount, 'currency' => $this->store['currency'], 'status' => 'pending',
                ]);
                Database::update('orders', ['status' => 'confirmed', 'confirmed_at' => now()], 'id = ?', [$orderId]);
                return ['success' => true, 'method' => 'cod'];

            case 'stripe':
                $stripe = new StripeService();
                $session = $stripe->createCheckoutSession($orderId, $orderNumber, $amount, $this->store);
                return ['redirect' => $session['url']];

            case 'razorpay':
                $razor = new RazorpayService();
                $rOrder = $razor->createOrder($orderId, $amount, $this->store);
                return ['redirect' => "/payment/razorpay/checkout?order={$rOrder['id']}&num=$orderNumber"];

            case 'paypal':
                $paypal = new PayPalService();
                $pOrder = $paypal->createOrder($orderId, $amount, $this->store);
                return ['redirect' => $pOrder['approve_url']];

            case 'bank_transfer':
                Database::insert('payments', [
                    'order_id' => $orderId, 'store_id' => $this->storeId, 'gateway' => 'bank_transfer',
                    'amount' => $amount, 'currency' => $this->store['currency'], 'status' => 'pending',
                ]);
                return ['success' => true, 'method' => 'bank_transfer'];

            default:
                throw new RuntimeException('Unsupported payment method');
        }
    }
}
