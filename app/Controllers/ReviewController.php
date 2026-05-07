<?php

class ReviewController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $status = $this->request->get('status', '');
        $sql = "SELECT r.*, p.name as product_name, u.name as user_name, u.email as user_email
                FROM reviews r JOIN products p ON p.id = r.product_id
                LEFT JOIN users u ON u.id = r.user_id WHERE r.store_id = ?";
        $params = [$this->storeId];
        if ($status) { $sql .= " AND r.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY r.created_at DESC";
        $reviews = $this->paginate($sql, $params, 20);
        $this->view('admin.reviews.index', compact('reviews','status'));
    }

    public function approve(int $id): void {
        $this->find($id);
        Database::update('reviews', ['status' => 'approved'], 'id = ?', [$id]);
        $this->updateProductRating($id);
        $this->success('Review approved.');
    }

    public function reject(int $id): void {
        $this->find($id);
        Database::update('reviews', ['status' => 'rejected'], 'id = ?', [$id]);
        $this->success('Review rejected.');
    }

    public function destroy(int $id): void {
        $review = $this->find($id);
        Database::delete('reviews', 'id = ?', [$id]);
        $this->updateProductRating($review['product_id']);
        $this->success('Review deleted.');
    }

    public function reply(int $id): void {
        CSRF::validateOrFail();
        $this->find($id);
        Database::update('reviews', [
            'reply'      => $this->request->post('reply'),
            'replied_at' => now(),
            'replied_by' => $this->currentUser['id'],
        ], 'id = ?', [$id]);
        $this->success('Reply posted.');
    }

    private function find(int $id): array {
        $r = Database::fetch("SELECT * FROM reviews WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$r) $this->abort(404);
        return $r;
    }

    private function updateProductRating(int $productId): void {
        $stats = Database::fetch("SELECT AVG(rating) avg, COUNT(*) cnt FROM reviews WHERE product_id = ? AND status = 'approved'", [$productId]);
        Database::update('products', ['rating' => round($stats['avg'] ?? 0, 2), 'review_count' => $stats['cnt']], 'id = ?', [$productId]);
    }
}
