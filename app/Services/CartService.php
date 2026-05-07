<?php

class CartService {
    private int $storeId;
    private ?int $cartId = null;

    public function __construct(int $storeId) {
        $this->storeId = $storeId;
        $this->getOrCreateCart();
    }

    private function getOrCreateCart(): array {
        $userId = Auth::id();
        $sessionId = Session::id();

        if ($userId) {
            $cart = Database::fetch("SELECT * FROM carts WHERE store_id = ? AND user_id = ?", [$this->storeId, $userId]);
        } else {
            $cart = Database::fetch("SELECT * FROM carts WHERE store_id = ? AND session_id = ?", [$this->storeId, $sessionId]);
        }
        if (!$cart) {
            $id = Database::insert('carts', [
                'store_id'   => $this->storeId,
                'user_id'    => $userId,
                'session_id' => $userId ? null : $sessionId,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
            ]);
            $cart = Database::fetch("SELECT * FROM carts WHERE id = ?", [$id]);
        }
        $this->cartId = (int)$cart['id'];
        return $cart;
    }

    public function add(int $productId, ?int $variantId, int $quantity): void {
        $product = Database::fetch("SELECT * FROM products WHERE id = ? AND store_id = ? AND status = 'active'", [$productId, $this->storeId]);
        if (!$product) throw new RuntimeException('Product not available');

        $price = (float)$product['price'];
        if ($variantId) {
            $variant = Database::fetch("SELECT * FROM product_variants WHERE id = ? AND product_id = ? AND is_active = 1", [$variantId, $productId]);
            if (!$variant) throw new RuntimeException('Variant not available');
            $price = (float)$variant['price'];
            if ($variant['quantity'] < $quantity) throw new RuntimeException('Insufficient stock');
        } elseif ($product['track_inventory'] && $product['quantity'] < $quantity) {
            throw new RuntimeException('Insufficient stock');
        }

        $existing = Database::fetch("SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ? AND " . ($variantId ? "variant_id = ?" : "variant_id IS NULL"),
            $variantId ? [$this->cartId, $productId, $variantId] : [$this->cartId, $productId]);

        if ($existing) {
            Database::update('cart_items', ['quantity' => $existing['quantity'] + $quantity, 'unit_price' => $price], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('cart_items', [
                'cart_id'    => $this->cartId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'vendor_id'  => $product['vendor_id'],
                'quantity'   => $quantity,
                'unit_price' => $price,
            ]);
        }
    }

    public function updateQuantity(int $itemId, int $quantity): void {
        if ($quantity <= 0) { $this->remove($itemId); return; }
        Database::update('cart_items', ['quantity' => $quantity], 'id = ? AND cart_id = ?', [$itemId, $this->cartId]);
    }

    public function remove(int $itemId): void {
        Database::delete('cart_items', 'id = ? AND cart_id = ?', [$itemId, $this->cartId]);
    }

    public function clear(): void {
        Database::delete('cart_items', 'cart_id = ?', [$this->cartId]);
        Database::update('carts', ['coupon_id' => null, 'coupon_discount' => 0], 'id = ?', [$this->cartId]);
    }

    public function applyCoupon(string $code): float {
        $coupon = Database::fetch(
            "SELECT * FROM coupons WHERE store_id = ? AND code = ? AND is_active = 1
             AND (starts_at IS NULL OR starts_at <= NOW()) AND (expires_at IS NULL OR expires_at >= NOW())",
            [$this->storeId, strtoupper($code)]
        );
        if (!$coupon) throw new RuntimeException('Invalid coupon code');
        if ($coupon['usage_limit'] !== null && $coupon['usage_count'] >= $coupon['usage_limit']) {
            throw new RuntimeException('Coupon usage limit reached');
        }
        $subtotal = $this->subtotal();
        if ($coupon['min_order_amount'] && $subtotal < $coupon['min_order_amount']) {
            throw new RuntimeException("Minimum order amount: " . $coupon['min_order_amount']);
        }
        $discount = match ($coupon['type']) {
            'percentage' => $subtotal * ($coupon['value'] / 100),
            'fixed'      => (float)$coupon['value'],
            default      => 0,
        };
        if ($coupon['max_discount_amount']) $discount = min($discount, (float)$coupon['max_discount_amount']);
        Database::update('carts', ['coupon_id' => $coupon['id'], 'coupon_discount' => $discount], 'id = ?', [$this->cartId]);
        return $discount;
    }

    public function getCart(): array {
        $cart = Database::fetch("SELECT * FROM carts WHERE id = ?", [$this->cartId]);
        $items = Database::fetchAll(
            "SELECT ci.*, p.name as product_name, p.slug, p.track_inventory, p.weight,
                    (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image,
                    pv.sku as variant_sku, pv.image as variant_image
             FROM cart_items ci JOIN products p ON p.id = ci.product_id
             LEFT JOIN product_variants pv ON pv.id = ci.variant_id WHERE ci.cart_id = ?",
            [$this->cartId]
        );
        $coupon = $cart['coupon_id'] ? Database::fetch("SELECT * FROM coupons WHERE id = ?", [$cart['coupon_id']]) : null;
        $subtotal = $this->subtotal();
        $discount = (float)($cart['coupon_discount'] ?? 0);
        return [
            'id'              => $this->cartId,
            'items'           => $items,
            'item_count'      => array_sum(array_map(fn($i) => $i['quantity'], $items)),
            'subtotal'        => $subtotal,
            'coupon'          => $coupon,
            'coupon_discount' => $discount,
            'total'           => max(0, $subtotal - $discount),
        ];
    }

    public function subtotal(): float {
        $items = Database::fetchAll("SELECT quantity, unit_price FROM cart_items WHERE cart_id = ?", [$this->cartId]);
        return array_reduce($items, fn($sum, $i) => $sum + ($i['quantity'] * $i['unit_price']), 0);
    }

    public function total(): float {
        $cart = Database::fetch("SELECT coupon_discount FROM carts WHERE id = ?", [$this->cartId]);
        return max(0, $this->subtotal() - (float)($cart['coupon_discount'] ?? 0));
    }

    public function itemCount(): int {
        return (int)(Database::fetch("SELECT COALESCE(SUM(quantity),0) c FROM cart_items WHERE cart_id = ?", [$this->cartId])['c'] ?? 0);
    }

    public function mergeGuestCart(int $userId): void {
        $guestCart = Database::fetch("SELECT * FROM carts WHERE store_id = ? AND session_id = ?", [$this->storeId, Session::id()]);
        $userCart  = Database::fetch("SELECT * FROM carts WHERE store_id = ? AND user_id = ?", [$this->storeId, $userId]);
        if ($guestCart && $userCart) {
            $items = Database::fetchAll("SELECT * FROM cart_items WHERE cart_id = ?", [$guestCart['id']]);
            foreach ($items as $item) {
                Database::query(
                    "INSERT INTO cart_items (cart_id, product_id, variant_id, vendor_id, quantity, unit_price)
                     VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)",
                    [$userCart['id'], $item['product_id'], $item['variant_id'], $item['vendor_id'], $item['quantity'], $item['unit_price']]
                );
            }
            Database::delete('carts', 'id = ?', [$guestCart['id']]);
        } elseif ($guestCart && !$userCart) {
            Database::update('carts', ['user_id' => $userId, 'session_id' => null], 'id = ?', [$guestCart['id']]);
        }
    }
}
