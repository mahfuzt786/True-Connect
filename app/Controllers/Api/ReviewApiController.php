<?php
namespace Api;

use Database;

class ReviewApiController extends ApiController {
    public function index(int $productId): void {
        $reviews = Database::fetchAll(
            "SELECT r.*, u.name as user_name, u.avatar as user_avatar
             FROM reviews r LEFT JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC",
            [$productId]
        );
        $stats = Database::fetch("SELECT AVG(rating) avg, COUNT(*) cnt FROM reviews WHERE product_id = ? AND status = 'approved'", [$productId]);
        $this->ok(['reviews' => $reviews, 'stats' => $stats]);
    }
    public function store(int $productId): void {
        $this->requireUser();
        $data = $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'required|min:5',
            'title'  => 'nullable',
        ]);
        $product = Database::fetch("SELECT store_id FROM products WHERE id = ?", [$productId]);
        if (!$product) $this->error('Product not found', 404);
        $hasOrdered = Database::fetch(
            "SELECT 1 FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ?",
            [$this->user['id'], $productId]
        );
        $id = Database::insert('reviews', [
            'store_id'   => $product['store_id'],
            'product_id' => $productId,
            'user_id'    => $this->user['id'],
            'rating'     => $data['rating'],
            'title'      => $data['title'] ?? '',
            'body'       => $data['body'],
            'status'     => 'pending',
            'verified_purchase' => $hasOrdered ? 1 : 0,
        ]);
        $this->ok(['id' => $id], 'Review submitted for moderation');
    }
    public function update(int $id): void {
        $this->requireUser();
        $review = Database::fetch("SELECT * FROM reviews WHERE id = ? AND user_id = ?", [$id, $this->user['id']]);
        if (!$review) $this->error('Review not found', 404);
        $data = $this->request->only(['rating','title','body']);
        $data['status'] = 'pending';
        Database::update('reviews', $data, 'id = ?', [$id]);
        $this->ok(null, 'Review updated');
    }
    public function destroy(int $id): void {
        $this->requireUser();
        $r = Database::fetch("SELECT * FROM reviews WHERE id = ? AND user_id = ?", [$id, $this->user['id']]);
        if (!$r) $this->error('Review not found', 404);
        Database::delete('reviews', 'id = ?', [$id]);
        $this->ok(null, 'Deleted');
    }
}
