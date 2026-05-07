<?php

class AccountController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void {
        $this->view('account.index', ['user' => $this->currentUser]);
    }

    public function orders(): void {
        $orders = $this->paginate("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$this->currentUser['id']], 15);
        $this->view('account.orders', compact('orders'));
    }

    public function orderDetail(int $id): void {
        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$id, $this->currentUser['id']]);
        if (!$order) $this->abort(404);
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$id]);
        $this->view('account.order-detail', compact('order','items'));
    }

    public function wishlist(): void {
        $items = Database::fetchAll(
            "SELECT w.*, p.name, p.slug, p.price, p.compare_price, s.slug as store_slug,
                    (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
             FROM wishlists w JOIN products p ON p.id = w.product_id JOIN stores s ON s.id = w.store_id
             WHERE w.user_id = ? ORDER BY w.created_at DESC",
            [$this->currentUser['id']]
        );
        $this->view('account.wishlist', compact('items'));
    }

    public function addresses(): void {
        $addresses = Database::fetchAll("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC", [$this->currentUser['id']]);
        $this->view('account.addresses', compact('addresses'));
    }

    public function storeAddress(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'first_name'   => 'required',
            'last_name'    => 'required',
            'address_line1'=> 'required',
            'city'         => 'required',
            'country'      => 'required',
            'zip_code'     => 'required',
        ]);
        $data['user_id'] = $this->currentUser['id'];
        $data['type']    = $this->request->post('type', 'shipping');
        $data['phone']   = $this->request->post('phone', '');
        $data['state']   = $this->request->post('state', '');
        if ($this->request->boolean('is_default', false)) {
            Database::update('addresses', ['is_default' => 0], 'user_id = ?', [$this->currentUser['id']]);
            $data['is_default'] = 1;
        }
        Database::insert('addresses', $data);
        $this->success('Address added.');
    }

    public function updateAddress(int $id): void {
        CSRF::validateOrFail();
        $address = Database::fetch("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$id, $this->currentUser['id']]);
        if (!$address) $this->abort(404);
        $data = $this->request->only(['first_name','last_name','address_line1','address_line2','city','state','country','zip_code','phone']);
        Database::update('addresses', $data, 'id = ?', [$id]);
        $this->success('Address updated.');
    }

    public function deleteAddress(int $id): void {
        Database::delete('addresses', 'id = ? AND user_id = ?', [$id, $this->currentUser['id']]);
        $this->success('Address deleted.');
    }

    public function setDefaultAddress(int $id): void {
        Database::update('addresses', ['is_default' => 0], 'user_id = ?', [$this->currentUser['id']]);
        Database::update('addresses', ['is_default' => 1], 'id = ? AND user_id = ?', [$id, $this->currentUser['id']]);
        $this->success('Default address set.');
    }

    public function reviews(): void {
        $reviews = Database::fetchAll(
            "SELECT r.*, p.name as product_name, p.slug as product_slug
             FROM reviews r JOIN products p ON p.id = r.product_id
             WHERE r.user_id = ? ORDER BY r.created_at DESC",
            [$this->currentUser['id']]
        );
        $this->view('account.reviews', compact('reviews'));
    }

    public function profile(): void {
        $this->view('account.profile', ['user' => $this->currentUser]);
    }

    public function updateProfile(): void {
        CSRF::validateOrFail();
        $data = $this->validate(['name' => 'required|min:2', 'phone' => 'nullable']);
        Database::update('users', $data, 'id = ?', [$this->currentUser['id']]);
        $this->success('Profile updated.');
    }

    public function changePassword(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'current_password' => 'required',
            'password'         => 'required|password_strength|confirmed',
        ]);
        if (!password_verify($data['current_password'], $this->currentUser['password'])) {
            $this->error('Current password is incorrect.');
            return;
        }
        Database::update('users', ['password' => password_hash($data['password'], PASSWORD_BCRYPT)], 'id = ?', [$this->currentUser['id']]);
        $this->success('Password changed.');
    }
}
