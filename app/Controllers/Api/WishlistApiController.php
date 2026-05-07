<?php
namespace Api;

use Database;

class WishlistApiController extends ApiController {
    public function index(): void {
        $this->requireUser();
        $items = Database::fetchAll(
            "SELECT w.*, p.name, p.slug, p.price, p.compare_price,
                    (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM wishlists w JOIN products p ON p.id = w.product_id WHERE w.user_id = ?",
            [$this->user['id']]
        );
        $this->ok($items);
    }
    public function toggle(): void {
        $this->requireUser();
        $data = $this->validate(['store_id' => 'required|integer', 'product_id' => 'required|integer']);
        $existing = Database::fetch("SELECT id FROM wishlists WHERE user_id = ? AND store_id = ? AND product_id = ?",
            [$this->user['id'], $data['store_id'], $data['product_id']]);
        if ($existing) {
            Database::delete('wishlists', 'id = ?', [$existing['id']]);
            $this->ok(['in_wishlist' => false]);
        } else {
            Database::insert('wishlists', ['user_id' => $this->user['id'], 'store_id' => $data['store_id'], 'product_id' => $data['product_id']]);
            $this->ok(['in_wishlist' => true]);
        }
    }
}
