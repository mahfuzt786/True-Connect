<?php

/**
 * Public marketplace pages — aggregates products and stores across the platform
 * so visitors can browse without picking a single storefront first.
 *
 * Browsing is global, but cart / checkout still happen on the originating store
 * (each product card / detail page links back to /shop/{slug}/cart/add).
 */
class MarketplaceController extends Controller {

    public function index(): void {
        $featured = Database::fetchAll(
            "SELECT p.id, p.store_id, p.name, p.slug, p.price, p.compare_price, p.rating, p.review_count,
                    s.slug AS store_slug, s.name AS store_name, s.currency_symbol,
                    (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
             FROM products p
             JOIN stores s ON s.id = p.store_id
             WHERE p.status = 'active' AND s.status = 'active'
             ORDER BY p.featured DESC, p.sales_count DESC, p.created_at DESC
             LIMIT 12"
        );

        $stores = Database::fetchAll(
            "SELECT s.id, s.slug, s.name, s.logo, s.type,
                    (SELECT COUNT(*) FROM products p WHERE p.store_id = s.id AND p.status = 'active') AS product_count
             FROM stores s
             WHERE s.status = 'active'
             ORDER BY product_count DESC, s.created_at DESC
             LIMIT 8"
        );

        $categories = Database::fetchAll(
            "SELECT c.id, c.name, c.slug,
                    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') AS product_count
             FROM categories c
             WHERE c.parent_id IS NULL AND c.is_active = 1
             ORDER BY product_count DESC, c.name ASC
             LIMIT 12"
        );

        $seo = [
            'title'       => 'Marketplace',
            'description' => 'Discover thousands of products from vendors across the platform. One cart, one checkout.',
            'jsonld'      => [
                '@context' => 'https://schema.org',
                '@type'    => 'WebSite',
                'name'     => config('app.name') . ' Marketplace',
                'url'      => rtrim(config('app.url'), '/') . '/marketplace',
                'potentialAction' => [
                    '@type'  => 'SearchAction',
                    'target' => rtrim(config('app.url'), '/') . '/marketplace/products?q={query}',
                    'query-input' => 'required name=query',
                ],
            ],
        ];
        $this->view('home.marketplace.index', compact('featured', 'stores', 'categories', 'seo'), 'public');
    }

    public function products(): void {
        $req = $this->request;
        $page    = max(1, (int)$req->get('page', 1));
        $perPage = 24;

        $q          = trim((string)$req->get('q', ''));
        $categoryId = (int)$req->get('category', 0);
        $storeId    = (int)$req->get('store', 0);
        $minPrice   = $req->get('min_price') !== null && $req->get('min_price') !== '' ? (float)$req->get('min_price') : null;
        $maxPrice   = $req->get('max_price') !== null && $req->get('max_price') !== '' ? (float)$req->get('max_price') : null;
        $minRating  = (int)$req->get('rating', 0);
        $inStock    = $req->get('in_stock') === '1';
        $sort       = (string)$req->get('sort', 'popular');

        $where  = ["p.status = 'active'", "s.status = 'active'"];
        $params = [];

        if ($q !== '') {
            $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }
        if ($categoryId > 0) { $where[] = "p.category_id = ?"; $params[] = $categoryId; }
        if ($storeId > 0)    { $where[] = "p.store_id = ?";    $params[] = $storeId; }
        if ($minPrice !== null) { $where[] = "p.price >= ?"; $params[] = $minPrice; }
        if ($maxPrice !== null) { $where[] = "p.price <= ?"; $params[] = $maxPrice; }
        if ($minRating > 0)     { $where[] = "p.rating >= ?"; $params[] = $minRating; }
        if ($inStock)           { $where[] = "(p.track_inventory = 0 OR p.quantity > 0)"; }

        $orderBy = match ($sort) {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC',
            'rating'     => 'p.rating DESC, p.review_count DESC',
            default      => 'p.featured DESC, p.sales_count DESC, p.created_at DESC', // popular
        };

        $sql = "SELECT p.id, p.store_id, p.name, p.price, p.compare_price, p.rating, p.review_count, p.quantity, p.track_inventory,
                       s.slug AS store_slug, s.name AS store_name, s.currency_symbol, s.type AS store_type,
                       (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
                FROM products p
                JOIN stores s ON s.id = p.store_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy";

        $result = $this->paginate($sql, $params, $perPage);

        $categories = Database::fetchAll(
            "SELECT id, name, slug FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY name"
        );
        $stores = Database::fetchAll(
            "SELECT id, name, slug FROM stores WHERE status = 'active' ORDER BY name"
        );

        $this->view('home.marketplace.products', [
            'products'   => $result,
            'categories' => $categories,
            'stores'     => $stores,
            'filters'    => compact('q', 'categoryId', 'storeId', 'minPrice', 'maxPrice', 'minRating', 'inStock', 'sort'),
        ], 'public');
    }

    public function product(int $id): void {
        $product = Database::fetch(
            "SELECT p.*, s.slug AS store_slug, s.name AS store_name, s.currency_symbol, s.type AS store_type, s.id AS s_id,
                    c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN stores s ON s.id = p.store_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ? AND p.status = 'active' AND s.status = 'active'",
            [$id]
        );
        if (!$product) {
            $this->abort(404);
            return;
        }

        // Track a view for the product (best-effort)
        try {
            Database::query("UPDATE products SET view_count = view_count + 1 WHERE id = ?", [$id]);
        } catch (Throwable) {}

        $images = Database::fetchAll(
            "SELECT image, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC",
            [$id]
        );

        $vendor = null;
        if (!empty($product['vendor_id'])) {
            $vendor = Database::fetch(
                "SELECT id, business_name, business_slug FROM vendors WHERE id = ?",
                [$product['vendor_id']]
            );
        }

        $reviews = Database::fetchAll(
            "SELECT r.*, u.name AS user_name
             FROM reviews r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC
             LIMIT 25",
            [$id]
        );

        // Rating distribution (1-5 stars)
        $ratingRows = Database::fetchAll(
            "SELECT rating, COUNT(*) AS c FROM reviews WHERE product_id = ? AND status = 'approved' GROUP BY rating",
            [$id]
        );
        $ratingDist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $totalReviews = 0;
        foreach ($ratingRows as $row) {
            $r = (int)$row['rating'];
            if (isset($ratingDist[$r])) {
                $ratingDist[$r] = (int)$row['c'];
                $totalReviews  += (int)$row['c'];
            }
        }

        $related = Database::fetchAll(
            "SELECT p.id, p.name, p.price, p.compare_price, p.rating, p.review_count,
                    s.slug AS store_slug, s.currency_symbol,
                    (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
             FROM products p
             JOIN stores s ON s.id = p.store_id
             WHERE p.status = 'active' AND s.status = 'active'
                   AND p.id <> ?
                   AND (p.category_id = ? OR p.store_id = ?)
             ORDER BY p.featured DESC, p.sales_count DESC
             LIMIT 6",
            [$id, $product['category_id'] ?? 0, $product['store_id']]
        );

        $inStock = (int)$product['track_inventory'] === 0 || (int)$product['quantity'] > 0;
        $seo = [
            'title'       => $product['name'],
            'description' => mb_substr(strip_tags($product['short_description'] ?: $product['description'] ?: $product['name']), 0, 200),
            'image'       => $images[0]['image'] ?? url('images/logo.svg'),
            'type'        => 'product',
            'jsonld'      => [
                '@context'    => 'https://schema.org',
                '@type'       => 'Product',
                'name'        => $product['name'],
                'description' => strip_tags((string)($product['short_description'] ?? $product['description'] ?? '')),
                'sku'         => $product['sku'] ?? null,
                'image'       => array_values(array_map(fn($i) => $i['image'], $images)),
                'brand'       => ['@type' => 'Brand', 'name' => $product['store_name']],
                'offers'      => [
                    '@type'         => 'Offer',
                    'price'         => (string)$product['price'],
                    'priceCurrency' => 'INR',
                    'availability'  => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url'           => url('marketplace/products/' . (int)$product['id']),
                ],
                'aggregateRating' => $totalReviews > 0 ? [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => (float)$product['rating'],
                    'reviewCount' => (int)$totalReviews,
                ] : null,
            ],
        ];
        $this->view('home.marketplace.product', compact(
            'product', 'images', 'vendor', 'reviews', 'ratingDist', 'totalReviews', 'related', 'seo'
        ), 'public');
    }

    public function stores(): void {
        $type = (string)$this->request->get('type', '');
        $where = ["s.status = 'active'"];
        $params = [];
        if (in_array($type, ['ecommerce', 'marketplace'], true)) {
            $where[] = "s.type = ?";
            $params[] = $type;
        }
        $stores = Database::fetchAll(
            "SELECT s.*, (SELECT COUNT(*) FROM products p WHERE p.store_id = s.id AND p.status = 'active') AS product_count
             FROM stores s
             WHERE " . implode(' AND ', $where) . "
             ORDER BY product_count DESC, s.name ASC",
            $params
        );
        $this->view('home.marketplace.stores', compact('stores', 'type'), 'public');
    }
}
