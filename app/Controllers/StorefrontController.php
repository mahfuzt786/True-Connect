<?php

class StorefrontController extends Controller {

    private function getStore(string $slug): array {
        $store = Database::fetch("SELECT * FROM stores WHERE (slug = ? OR subdomain = ? OR custom_domain = ?) AND status = 'active'", [$slug, $slug, $slug]);
        if (!$store) $this->abort(404, 'Store not found');
        trackEvent('store_view', $store['id']);
        return $store;
    }

    public function home(string $storeSlug): void {
        $store      = $this->getStore($storeSlug);
        $featured   = Database::fetchAll(
            "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM products p WHERE p.store_id = ? AND p.status = 'active' AND p.featured = 1 ORDER BY p.created_at DESC LIMIT 8",
            [$store['id']]
        );
        $newest    = Database::fetchAll(
            "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM products p WHERE p.store_id = ? AND p.status = 'active' ORDER BY p.created_at DESC LIMIT 12",
            [$store['id']]
        );
        $bestsellers = Database::fetchAll(
            "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM products p WHERE p.store_id = ? AND p.status = 'active' ORDER BY p.sales_count DESC LIMIT 8",
            [$store['id']]
        );
        $categories = Database::fetchAll("SELECT * FROM categories WHERE store_id = ? AND is_active = 1 AND parent_id IS NULL ORDER BY sort_order LIMIT 8", [$store['id']]);
        $this->renderTheme($store, 'home', compact('store','featured','newest','bestsellers','categories'));
    }

    public function products(string $storeSlug): void {
        $store    = $this->getStore($storeSlug);
        $catSlug  = $this->request->get('category', '');
        $minPrice = $this->request->get('min_price', '');
        $maxPrice = $this->request->get('max_price', '');
        $sort     = $this->request->get('sort', 'newest');

        $sql    = "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                   FROM products p WHERE p.store_id = ? AND p.status = 'active'";
        $params = [$store['id']];

        if ($catSlug) {
            $cat = Database::fetch("SELECT id FROM categories WHERE store_id = ? AND slug = ?", [$store['id'], $catSlug]);
            if ($cat) { $sql .= " AND p.category_id = ?"; $params[] = $cat['id']; }
        }
        if ($minPrice !== '') { $sql .= " AND p.price >= ?"; $params[] = $minPrice; }
        if ($maxPrice !== '') { $sql .= " AND p.price <= ?"; $params[] = $maxPrice; }

        $sql .= match ($sort) {
            'price_asc'  => " ORDER BY p.price ASC",
            'price_desc' => " ORDER BY p.price DESC",
            'name'       => " ORDER BY p.name ASC",
            'rating'     => " ORDER BY p.rating DESC",
            'popular'    => " ORDER BY p.sales_count DESC",
            default      => " ORDER BY p.created_at DESC",
        };

        $products   = $this->paginate($sql, $params, 24);
        $categories = Database::fetchAll("SELECT * FROM categories WHERE store_id = ? AND is_active = 1 ORDER BY sort_order", [$store['id']]);
        $this->renderTheme($store, 'products', compact('store','products','categories','catSlug','sort'));
    }

    public function product(string $storeSlug, string $slug): void {
        $store   = $this->getStore($storeSlug);
        $product = Database::fetch("SELECT * FROM products WHERE store_id = ? AND slug = ? AND status = 'active'", [$store['id'], $slug]);
        if (!$product) $this->abort(404);

        Database::query("UPDATE products SET view_count = view_count + 1 WHERE id = ?", [$product['id']]);
        trackEvent('product_view', $store['id'], ['product_id' => $product['id']]);

        $images   = Database::fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order", [$product['id']]);
        $variants = Database::fetchAll("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1", [$product['id']]);
        $reviews  = Database::fetchAll(
            "SELECT r.*, u.name as user_name, u.avatar as user_avatar
             FROM reviews r LEFT JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20",
            [$product['id']]
        );
        $related = Database::fetchAll(
            "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM products p WHERE p.store_id = ? AND p.category_id = ? AND p.id != ? AND p.status = 'active' LIMIT 6",
            [$store['id'], $product['category_id'], $product['id']]
        );
        $vendor = $product['vendor_id'] ? Database::fetch("SELECT * FROM vendors WHERE id = ?", [$product['vendor_id']]) : null;

        $this->renderTheme($store, 'product', compact('store','product','images','variants','reviews','related','vendor'));
    }

    public function category(string $storeSlug, string $slug): void {
        $store    = $this->getStore($storeSlug);
        $category = Database::fetch("SELECT * FROM categories WHERE store_id = ? AND slug = ?", [$store['id'], $slug]);
        if (!$category) $this->abort(404);
        $sql = "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                FROM products p WHERE p.store_id = ? AND p.category_id = ? AND p.status = 'active' ORDER BY p.created_at DESC";
        $products = $this->paginate($sql, [$store['id'], $category['id']], 24);
        $this->renderTheme($store, 'category', compact('store','category','products'));
    }

    public function cart(string $storeSlug): void {
        $store   = $this->getStore($storeSlug);
        $service = new CartService($store['id']);
        $cart    = $service->getCart();
        $this->renderTheme($store, 'cart', compact('store','cart'));
    }

    public function addToCart(string $storeSlug): void {
        $store     = $this->getStore($storeSlug);
        $productId = (int)$this->request->post('product_id');
        $variantId = $this->request->post('variant_id') ? (int)$this->request->post('variant_id') : null;
        $quantity  = max(1, (int)$this->request->post('quantity', 1));
        $service   = new CartService($store['id']);
        try {
            $service->add($productId, $variantId, $quantity);
            trackEvent('add_to_cart', $store['id'], ['product_id' => $productId]);
            if ($this->request->isAjax()) {
                $this->json(['success' => true, 'cart_count' => $service->itemCount(), 'cart_total' => $service->total()]);
            }
            $this->success('Added to cart.');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    public function updateCart(string $storeSlug): void {
        $store    = $this->getStore($storeSlug);
        $itemId   = (int)$this->request->post('item_id');
        $quantity = max(0, (int)$this->request->post('quantity'));
        $service  = new CartService($store['id']);
        $service->updateQuantity($itemId, $quantity);
        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'cart' => $service->getCart()]);
        }
        $this->back();
    }

    public function removeFromCart(string $storeSlug): void {
        $store   = $this->getStore($storeSlug);
        $itemId  = (int)$this->request->post('item_id');
        $service = new CartService($store['id']);
        $service->remove($itemId);
        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'cart' => $service->getCart()]);
        }
        $this->back();
    }

    public function applyCoupon(string $storeSlug): void {
        $store   = $this->getStore($storeSlug);
        $code    = trim($this->request->post('code', ''));
        $service = new CartService($store['id']);
        try {
            $discount = $service->applyCoupon($code);
            $this->json(['success' => true, 'discount' => $discount]);
        } catch (Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function checkout(string $storeSlug): void {
        $store   = $this->getStore($storeSlug);
        $service = new CartService($store['id']);
        $cart    = $service->getCart();
        if (empty($cart['items'])) {
            redirect("/shop/{$storeSlug}/cart");
            return;
        }
        $addresses      = Auth::check() ? Database::fetchAll("SELECT * FROM addresses WHERE user_id = ?", [Auth::id()]) : [];
        $paymentMethods = Database::fetchAll("SELECT * FROM payment_methods WHERE store_id = ? AND is_enabled = 1 ORDER BY sort_order", [$store['id']]);
        $shippingZones  = Database::fetchAll("SELECT * FROM shipping_zones WHERE store_id = ? AND is_active = 1", [$store['id']]);
        $this->renderTheme($store, 'checkout', compact('store','cart','addresses','paymentMethods','shippingZones'));
    }

    public function processCheckout(string $storeSlug): void {
        CSRF::validateOrFail();
        $store    = $this->getStore($storeSlug);
        $checkout = new CheckoutService($store['id']);
        try {
            $result = $checkout->process($this->request->all());
            if (!empty($result['redirect'])) {
                redirect($result['redirect']);
                return;
            }
            redirect("/shop/{$storeSlug}/order/{$result['order_number']}/success");
        } catch (Throwable $e) {
            logError('Checkout error: ' . $e->getMessage());
            Session::flash('error', $e->getMessage());
            redirect("/shop/{$storeSlug}/checkout");
        }
    }

    public function orderSuccess(string $storeSlug, string $orderNumber): void {
        $store = $this->getStore($storeSlug);
        $order = Database::fetch("SELECT * FROM orders WHERE order_number = ? AND store_id = ?", [$orderNumber, $store['id']]);
        if (!$order) $this->abort(404);
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
        trackEvent('order_completed', $store['id'], ['order_id' => $order['id']]);
        $this->renderTheme($store, 'order-success', compact('store','order','items'));
    }

    public function orderFailed(string $storeSlug, string $orderNumber): void {
        $store = $this->getStore($storeSlug);
        $this->renderTheme($store, 'order-failed', compact('store','orderNumber'));
    }

    public function search(string $storeSlug): void {
        $store    = $this->getStore($storeSlug);
        $q        = trim($this->request->get('q', ''));
        $products = [];
        if ($q) {
            $products = Database::fetchAll(
                "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                 FROM products p WHERE p.store_id = ? AND p.status = 'active'
                 AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?) LIMIT 50",
                [$store['id'], "%$q%", "%$q%", "%$q%"]
            );
        }
        $this->renderTheme($store, 'search', compact('store','q','products'));
    }

    public function vendors(string $storeSlug): void {
        $store   = $this->getStore($storeSlug);
        if ($store['type'] !== 'marketplace') $this->abort(404);
        $vendors = Database::fetchAll(
            "SELECT v.*, (SELECT COUNT(*) FROM products WHERE vendor_id = v.id AND status = 'active') as product_count
             FROM vendors v WHERE v.store_id = ? AND v.status = 'active' ORDER BY v.rating DESC",
            [$store['id']]
        );
        $this->renderTheme($store, 'vendors', compact('store','vendors'));
    }

    public function vendorShop(string $storeSlug, string $slug): void {
        $store  = $this->getStore($storeSlug);
        $vendor = Database::fetch("SELECT * FROM vendors WHERE store_id = ? AND business_slug = ? AND status = 'active'", [$store['id'], $slug]);
        if (!$vendor) $this->abort(404);
        $sql = "SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                FROM products p WHERE p.vendor_id = ? AND p.status = 'active' ORDER BY p.created_at DESC";
        $products = $this->paginate($sql, [$vendor['id']], 24);
        $this->renderTheme($store, 'vendor-shop', compact('store','vendor','products'));
    }

    public function page(string $storeSlug, string $slug): void {
        $store = $this->getStore($storeSlug);
        $page  = Database::fetch("SELECT * FROM pages WHERE store_id = ? AND slug = ? AND is_active = 1", [$store['id'], $slug]);
        if (!$page) $this->abort(404);
        $this->renderTheme($store, 'page', compact('store','page'));
    }

    public function submitReview(string $storeSlug): void {
        CSRF::validateOrFail();
        if (!Auth::check()) { redirect('/login'); return; }
        $store = $this->getStore($storeSlug);
        $data  = $this->validate([
            'product_id' => 'required|integer',
            'rating'     => 'required|integer|min:1|max:5',
            'title'      => 'nullable',
            'body'       => 'required|min:10',
        ]);
        $hasOrdered = Database::fetch(
            "SELECT 1 FROM orders o JOIN order_items oi ON oi.order_id = o.id
             WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'",
            [Auth::id(), $data['product_id']]
        );
        Database::insert('reviews', [
            'store_id'          => $store['id'],
            'product_id'        => $data['product_id'],
            'user_id'           => Auth::id(),
            'rating'            => $data['rating'],
            'title'             => $data['title'] ?? '',
            'body'              => $data['body'],
            'status'            => 'pending',
            'verified_purchase' => $hasOrdered ? 1 : 0,
        ]);
        $this->success('Thank you for your review! It will be visible after moderation.');
    }

    public function toggleWishlist(string $storeSlug): void {
        if (!Auth::check()) { $this->json(['error' => 'Please log in'], 401); return; }
        $store     = $this->getStore($storeSlug);
        $productId = (int)$this->request->post('product_id');
        $existing  = Database::fetch("SELECT id FROM wishlists WHERE user_id = ? AND store_id = ? AND product_id = ?", [Auth::id(), $store['id'], $productId]);
        if ($existing) {
            Database::delete('wishlists', 'id = ?', [$existing['id']]);
            $this->json(['success' => true, 'in_wishlist' => false]);
        } else {
            Database::insert('wishlists', ['user_id' => Auth::id(), 'store_id' => $store['id'], 'product_id' => $productId]);
            $this->json(['success' => true, 'in_wishlist' => true]);
        }
    }

    public function trackOrder(string $storeSlug, string $orderNumber): void {
        $store = $this->getStore($storeSlug);
        $order = Database::fetch("SELECT * FROM orders WHERE order_number = ? AND store_id = ?", [$orderNumber, $store['id']]);
        if (!$order) $this->abort(404);
        $history = Database::fetchAll("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC", [$order['id']]);
        $this->renderTheme($store, 'track-order', compact('store','order','history'));
    }

    private function renderTheme(array $store, string $page, array $data = []): void {
        $themeService = new ThemeService($store['theme'] ?? 'default');
        $themeService->render($page, $data);
    }
}
