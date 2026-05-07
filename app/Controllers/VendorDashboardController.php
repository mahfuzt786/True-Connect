<?php

class VendorDashboardController extends Controller {
    private ?array $vendor = null;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->vendor = Database::fetch("SELECT * FROM vendors WHERE user_id = ? AND status = 'active' LIMIT 1", [$this->currentUser['id']]);
    }

    public function index(): void {
        if (!$this->vendor) { redirect('/vendor/register'); return; }
        $stats = [
            'products'       => (int)(Database::fetch("SELECT COUNT(*) c FROM products WHERE vendor_id = ?", [$this->vendor['id']])['c']),
            'pending_orders' => (int)(Database::fetch("SELECT COUNT(DISTINCT oi.order_id) c FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.vendor_id = ? AND o.status='pending'", [$this->vendor['id']])['c']),
            'total_revenue'  => (float)(Database::fetch("SELECT COALESCE(SUM(vendor_amount),0) c FROM order_items WHERE vendor_id = ?", [$this->vendor['id']])['c']),
            'balance'        => (float)$this->vendor['balance'],
        ];
        $recentOrders = Database::fetchAll(
            "SELECT DISTINCT o.* FROM orders o JOIN order_items oi ON oi.order_id = o.id
             WHERE oi.vendor_id = ? ORDER BY o.created_at DESC LIMIT 10",
            [$this->vendor['id']]
        );
        $this->view('vendor.dashboard', ['vendor' => $this->vendor, 'stats' => $stats, 'recentOrders' => $recentOrders]);
    }

    public function registerForm(): void {
        if ($this->vendor) { redirect('/vendor/dashboard'); return; }
        $stores = Database::fetchAll("SELECT * FROM stores WHERE type = 'marketplace' AND status = 'active'");
        $this->view('vendor.register', compact('stores'));
    }

    public function register(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'store_id'      => 'required|integer|exists:stores,id',
            'business_name' => 'required|min:2|max:191',
            'email'         => 'required|email',
            'phone'         => 'nullable|phone',
            'description'   => 'nullable',
        ]);
        $store = Database::fetch("SELECT * FROM stores WHERE id = ?", [$data['store_id']]);
        $data['user_id']        = $this->currentUser['id'];
        $data['business_slug']  = slugify($data['business_name']) . '-' . substr(uniqid(),-4);
        $data['status']         = $store['auto_approve_vendors'] ? 'active' : 'pending';
        $vendorId = Database::insert('vendors', $data);
        if ($store['auto_approve_vendors']) {
            Database::update('users', ['role' => 'vendor'], 'id = ?', [$this->currentUser['id']]);
        }
        $this->success('Vendor application submitted!');
    }

    public function products(): void {
        if (!$this->vendor) { redirect('/vendor/register'); return; }
        $products = $this->paginate("SELECT p.*, (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image FROM products p WHERE p.vendor_id = ? ORDER BY p.created_at DESC", [$this->vendor['id']], 20);
        $this->view('vendor.products.index', ['vendor' => $this->vendor, 'products' => $products]);
    }

    public function createProduct(): void {
        if (!$this->vendor) { redirect('/vendor/register'); return; }
        $categories = Database::fetchAll("SELECT * FROM categories WHERE store_id = ? ORDER BY name", [$this->vendor['store_id']]);
        $this->view('vendor.products.create', ['vendor' => $this->vendor, 'categories' => $categories]);
    }

    public function storeProduct(): void {
        CSRF::validateOrFail();
        if (!$this->vendor) $this->abort(403);
        $data = $this->validate([
            'name'        => 'required|min:2|max:255',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'nullable|integer',
        ]);
        $data = array_merge($data, [
            'store_id'   => $this->vendor['store_id'],
            'vendor_id'  => $this->vendor['id'],
            'slug'       => slugify($data['name']) . '-' . substr(uniqid(),-4),
            'status'     => 'draft',
            'sku'        => $this->request->post('sku', ''),
            'quantity'   => (int)$this->request->post('quantity', 0),
            'description'=> $this->request->post('description', ''),
        ]);
        $id = Database::insert('products', $data);
        if ($this->request->hasFile('images')) {
            $uploader = (new Upload())->disk('public')->to("uploads/products/$id")->types(['image']);
            foreach ($this->request->files('images') as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) continue;
                $u = $uploader->handle($file);
                Database::insert('product_images', ['product_id' => $id, 'image' => $u['url']]);
            }
        }
        $this->success('Product submitted for approval.', ['id' => $id]);
    }

    public function editProduct(int $id): void {
        if (!$this->vendor) $this->abort(403);
        $product = Database::fetch("SELECT * FROM products WHERE id = ? AND vendor_id = ?", [$id, $this->vendor['id']]);
        if (!$product) $this->abort(404);
        $categories = Database::fetchAll("SELECT * FROM categories WHERE store_id = ?", [$this->vendor['store_id']]);
        $this->view('vendor.products.edit', ['vendor' => $this->vendor, 'product' => $product, 'categories' => $categories]);
    }

    public function updateProduct(int $id): void {
        CSRF::validateOrFail();
        if (!$this->vendor) $this->abort(403);
        $product = Database::fetch("SELECT * FROM products WHERE id = ? AND vendor_id = ?", [$id, $this->vendor['id']]);
        if (!$product) $this->abort(404);
        $data = $this->request->only(['name','price','quantity','description','category_id','sku']);
        Database::update('products', $data, 'id = ?', [$id]);
        $this->success('Product updated.');
    }

    public function destroyProduct(int $id): void {
        if (!$this->vendor) $this->abort(403);
        Database::delete('products', 'id = ? AND vendor_id = ?', [$id, $this->vendor['id']]);
        $this->success('Product deleted.');
    }

    public function orders(): void {
        if (!$this->vendor) { redirect('/vendor/register'); return; }
        $sql = "SELECT DISTINCT o.* FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE oi.vendor_id = ? ORDER BY o.created_at DESC";
        $orders = $this->paginate($sql, [$this->vendor['id']], 25);
        $this->view('vendor.orders.index', ['vendor' => $this->vendor, 'orders' => $orders]);
    }

    public function orderDetail(int $id): void {
        if (!$this->vendor) $this->abort(403);
        $order = Database::fetch("SELECT o.* FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE o.id = ? AND oi.vendor_id = ? LIMIT 1", [$id, $this->vendor['id']]);
        if (!$order) $this->abort(404);
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ? AND vendor_id = ?", [$id, $this->vendor['id']]);
        $this->view('vendor.orders.show', ['vendor' => $this->vendor, 'order' => $order, 'items' => $items]);
    }

    public function earnings(): void {
        if (!$this->vendor) { redirect('/vendor/register'); return; }
        $monthly = Database::fetchAll(
            "SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, SUM(oi.vendor_amount) as earnings
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE oi.vendor_id = ? AND o.payment_status = 'paid' GROUP BY month ORDER BY month DESC LIMIT 12",
            [$this->vendor['id']]
        );
        $payouts = Database::fetchAll("SELECT * FROM vendor_payouts WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 10", [$this->vendor['id']]);
        $this->view('vendor.earnings', ['vendor' => $this->vendor, 'monthly' => $monthly, 'payouts' => $payouts]);
    }

    public function settings(): void {
        if (!$this->vendor) { redirect('/vendor/register'); return; }
        $this->view('vendor.settings', ['vendor' => $this->vendor]);
    }

    public function updateSettings(): void {
        CSRF::validateOrFail();
        if (!$this->vendor) $this->abort(403);
        $data = $this->request->only(['business_name','description','phone','address','city','state','country','zip_code','bank_account_name','bank_account_number','bank_name','paypal_email']);
        if ($this->request->hasFile('logo')) {
            $u = (new Upload())->disk('public')->to('uploads/vendors')->types(['image'])->handle($this->request->file('logo'));
            $data['logo'] = $u['url'];
        }
        Database::update('vendors', $data, 'id = ?', [$this->vendor['id']]);
        $this->success('Settings updated.');
    }
}
