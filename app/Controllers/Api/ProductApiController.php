<?php
namespace Api;

use Database;

class ProductApiController extends ApiController {

    public function index(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $sql    = "SELECT p.id, p.name, p.slug, p.price, p.compare_price, p.rating, p.review_count, p.featured,
                          (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                   FROM products p WHERE p.store_id = ? AND p.status = 'active'";
        $params = [$store['id']];
        if ($cat = $this->request->get('category')) {
            $sql .= " AND p.category_id = ?";
            $params[] = $cat;
        }
        if ($featured = $this->request->get('featured')) {
            $sql .= " AND p.featured = 1";
        }
        $sql .= " ORDER BY p.created_at DESC";
        $this->ok($this->paginated($sql, $params, 20));
    }

    public function show(string $storeSlug, string $slug): void {
        $store   = $this->getStore($storeSlug);
        $product = Database::fetch("SELECT * FROM products WHERE store_id = ? AND slug = ? AND status = 'active'", [$store['id'], $slug]);
        if (!$product) $this->error('Product not found', 404);
        Database::query("UPDATE products SET view_count = view_count + 1 WHERE id = ?", [$product['id']]);
        $product['images']   = Database::fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC", [$product['id']]);
        $product['variants'] = Database::fetchAll("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1", [$product['id']]);
        $this->ok($product);
    }

    public function categories(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $cats  = Database::fetchAll("SELECT * FROM categories WHERE store_id = ? AND is_active = 1 ORDER BY sort_order", [$store['id']]);
        $this->ok($cats);
    }

    public function search(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $q     = trim($this->request->get('q', ''));
        if (!$q) { $this->ok([]); return; }
        $items = Database::fetchAll(
            "SELECT id, name, slug, price, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM products p WHERE store_id = ? AND status = 'active' AND (name LIKE ? OR description LIKE ?) LIMIT 20",
            [$store['id'], "%$q%", "%$q%"]
        );
        $this->ok($items);
    }

    public function featured(string $storeSlug): void {
        $store = $this->getStore($storeSlug);
        $items = Database::fetchAll(
            "SELECT id, name, slug, price, compare_price, rating,
                    (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM products p WHERE store_id = ? AND status = 'active' AND featured = 1 ORDER BY created_at DESC LIMIT 12",
            [$store['id']]
        );
        $this->ok($items);
    }

    private function getStore(string $slug): array {
        $store = Database::fetch("SELECT * FROM stores WHERE (slug = ? OR subdomain = ?) AND status = 'active'", [$slug, $slug]);
        if (!$store) $this->error('Store not found', 404);
        return $store;
    }
}
