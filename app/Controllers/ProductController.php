<?php

class ProductController extends Controller {

    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $search   = $this->request->get('search', '');
        $category = $this->request->get('category', '');
        $status   = $this->request->get('status', '');
        $vendor   = $this->request->get('vendor', '');

        $sql    = "SELECT p.*, c.name as category_name, v.business_name as vendor_name,
                          (SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                   FROM products p
                   LEFT JOIN categories c ON c.id = p.category_id
                   LEFT JOIN vendors v ON v.id = p.vendor_id
                   WHERE p.store_id = ?";
        $params = [$this->storeId];

        if ($search) {
            $sql    .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($category) { $sql .= " AND p.category_id = ?"; $params[] = $category; }
        if ($status)   { $sql .= " AND p.status = ?"; $params[] = $status; }
        if ($vendor)   { $sql .= " AND p.vendor_id = ?"; $params[] = $vendor; }

        $sql .= " ORDER BY p.created_at DESC";

        $products   = $this->paginate($sql, $params, 25);
        $categories = Database::fetchAll("SELECT * FROM categories WHERE store_id = ? ORDER BY name", [$this->storeId]);
        $vendors    = Database::fetchAll("SELECT * FROM vendors WHERE store_id = ? AND status = 'active'", [$this->storeId]);

        $this->view('admin.products.index', compact('products', 'categories', 'vendors', 'search', 'category', 'status'));
    }

    public function create(): void {
        $categories = Database::fetchAll("SELECT * FROM categories WHERE store_id = ? ORDER BY name", [$this->storeId]);
        // `values` is reserved in MySQL 8+ — alias as `options` instead.
        $attributes = Database::fetchAll("SELECT a.*, JSON_ARRAYAGG(JSON_OBJECT('id', av.id, 'label', av.label, 'value', av.value)) AS options
                                          FROM attributes a
                                          LEFT JOIN attribute_values av ON av.attribute_id = a.id
                                          WHERE a.store_id = ? GROUP BY a.id", [$this->storeId]);
        $vendors    = Database::fetchAll("SELECT * FROM vendors WHERE store_id = ? AND status = 'active'", [$this->storeId]);
        $this->view('admin.products.create', compact('categories', 'attributes', 'vendors'));
    }

    public function store(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'name'        => 'required|min:2|max:255',
            'price'       => 'required|numeric|min:0',
            'type'        => 'required|in:simple,variable,digital,service',
            'status'      => 'required|in:active,inactive,draft',
            'category_id' => 'nullable|integer',
            'sku'         => 'nullable',
            'quantity'    => 'nullable|integer',
            'description' => 'nullable',
        ]);

        $data = array_merge($data, [
            'store_id'          => $this->storeId,
            'slug'              => slugify($data['name']),
            'vendor_id'         => $this->request->post('vendor_id') ?: null,
            'compare_price'     => $this->request->post('compare_price') ?: null,
            'cost_price'        => $this->request->post('cost_price') ?: null,
            'short_description' => $this->request->post('short_description', ''),
            'featured'          => $this->request->boolean('featured', false) ? 1 : 0,
            'is_taxable'        => $this->request->boolean('is_taxable', true) ? 1 : 0,
            'track_inventory'   => $this->request->boolean('track_inventory', true) ? 1 : 0,
            'weight'            => $this->request->post('weight') ?: null,
            'meta_title'        => $this->request->post('meta_title', ''),
            'meta_description'  => $this->request->post('meta_description', ''),
            'tags'              => json_encode(array_filter(explode(',', $this->request->post('tags', '')))),
            'published_at'      => $data['status'] === 'active' ? date('Y-m-d H:i:s') : null,
        ]);

        // Check plan product limit
        $limit = $this->currentStore['products_limit'];
        if ($limit !== null) {
            $count = Database::fetch("SELECT COUNT(*) as cnt FROM products WHERE store_id = ?", [$this->storeId])['cnt'];
            if ($count >= $limit) {
                $this->error("Your plan allows maximum $limit products. Please upgrade to add more.");
                return;
            }
        }

        try {
            $productId = Database::insert('products', $data);

            // Handle images
            if ($this->request->hasFile('images')) {
                $uploader = (new Upload())->disk('public')->to("uploads/products/$productId")->types(['image'])->maxSize(5120);
                $files    = $this->request->files('images');
                $primary  = true;
                foreach ($files as $file) {
                    if ($file['error'] !== UPLOAD_ERR_OK) continue;
                    $uploaded = $uploader->handle($file);
                    Database::insert('product_images', [
                        'product_id' => $productId,
                        'image'      => $uploaded['url'],
                        'is_primary' => $primary ? 1 : 0,
                    ]);
                    $primary = false;
                }
            }

            // Handle variants
            $variantData = json_decode($this->request->post('variants', '[]'), true);
            if (is_array($variantData) && !empty($variantData)) {
                foreach ($variantData as $variant) {
                    $variantId = Database::insert('product_variants', [
                        'product_id' => $productId,
                        'sku'        => $variant['sku'] ?? null,
                        'price'      => $variant['price'],
                        'quantity'   => $variant['quantity'] ?? 0,
                    ]);
                    foreach ($variant['options'] ?? [] as $opt) {
                        Database::insert('product_variant_options', [
                            'variant_id'         => $variantId,
                            'attribute_id'       => $opt['attribute_id'],
                            'attribute_value_id' => $opt['value_id'],
                        ]);
                    }
                }
            }

            // Extra categories
            $extraCats = $this->request->post('extra_categories', []);
            foreach ((array)$extraCats as $catId) {
                Database::insert('product_categories', ['product_id' => $productId, 'category_id' => $catId]);
            }

            // Inventory log
            if (($data['quantity'] ?? 0) > 0) {
                Database::insert('inventory_logs', [
                    'store_id'        => $this->storeId,
                    'product_id'      => $productId,
                    'type'            => 'purchase',
                    'quantity_before' => 0,
                    'quantity_change' => $data['quantity'],
                    'quantity_after'  => $data['quantity'],
                    'notes'           => 'Initial stock',
                    'user_id'         => $this->currentUser['id'],
                ]);
            }

            $this->auditLog('product.create', 'Product', $productId, [], $data);
            $this->success('Product created successfully!', ['id' => $productId]);
        } catch (Throwable $e) {
            logError('Product creation error: ' . $e->getMessage());
            $this->error('Failed to create product. Please try again.');
        }
    }

    public function edit(int $id): void {
        $product    = $this->findProduct($id);
        $categories = Database::fetchAll("SELECT * FROM categories WHERE store_id = ? ORDER BY name", [$this->storeId]);
        $variants   = Database::fetchAll(
            "SELECT pv.*, GROUP_CONCAT(CONCAT(a.name, ':', av.label) SEPARATOR ', ') as option_labels
             FROM product_variants pv
             LEFT JOIN product_variant_options pvo ON pvo.variant_id = pv.id
             LEFT JOIN attribute_values av ON av.id = pvo.attribute_value_id
             LEFT JOIN attributes a ON a.id = pvo.attribute_id
             WHERE pv.product_id = ? GROUP BY pv.id",
            [$id]
        );
        $images  = Database::fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, is_primary DESC", [$id]);
        $vendors = Database::fetchAll("SELECT * FROM vendors WHERE store_id = ? AND status = 'active'", [$this->storeId]);
        $attributes = Database::fetchAll("SELECT a.* FROM attributes a WHERE a.store_id = ?", [$this->storeId]);
        $this->view('admin.products.edit', compact('product', 'categories', 'variants', 'images', 'vendors', 'attributes'));
    }

    public function update(int $id): void {
        CSRF::validateOrFail();
        $product = $this->findProduct($id);
        $oldData = $product;

        $data = $this->validate([
            'name'   => 'required|min:2|max:255',
            'price'  => 'required|numeric|min:0',
            'type'   => 'required|in:simple,variable,digital,service',
            'status' => 'required|in:active,inactive,draft,archived',
        ]);

        $data = array_merge($data, [
            'vendor_id'         => $this->request->post('vendor_id') ?: null,
            'category_id'       => $this->request->post('category_id') ?: null,
            'sku'               => $this->request->post('sku', ''),
            'compare_price'     => $this->request->post('compare_price') ?: null,
            'cost_price'        => $this->request->post('cost_price') ?: null,
            'quantity'          => (int)$this->request->post('quantity', 0),
            'description'       => $this->request->post('description', ''),
            'short_description' => $this->request->post('short_description', ''),
            'featured'          => $this->request->boolean('featured') ? 1 : 0,
            'is_taxable'        => $this->request->boolean('is_taxable', true) ? 1 : 0,
            'meta_title'        => $this->request->post('meta_title', ''),
            'meta_description'  => $this->request->post('meta_description', ''),
            'tags'              => json_encode(array_filter(explode(',', $this->request->post('tags', '')))),
        ]);

        Database::update('products', $data, 'id = ?', [$id]);

        // Handle new images
        if ($this->request->hasFile('images')) {
            $uploader = (new Upload())->disk('public')->to("uploads/products/$id")->types(['image'])->maxSize(5120);
            $files    = $this->request->files('images');
            foreach ($files as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) continue;
                $uploaded = $uploader->handle($file);
                Database::insert('product_images', ['product_id' => $id, 'image' => $uploaded['url']]);
            }
        }

        $this->auditLog('product.update', 'Product', $id, $oldData, $data);
        $this->success('Product updated successfully!');
    }

    public function destroy(int $id): void {
        $product = $this->findProduct($id);
        Database::delete('products', 'id = ?', [$id]);
        $this->auditLog('product.delete', 'Product', $id, $product, []);
        $this->success('Product deleted successfully!');
    }

    public function duplicate(int $id): void {
        $product = $this->findProduct($id);
        unset($product['id'], $product['created_at'], $product['updated_at']);
        $product['name']        = $product['name'] . ' (Copy)';
        $product['slug']        = slugify($product['name']) . '-' . time();
        $product['status']      = 'draft';
        $product['sales_count'] = 0;
        $product['view_count']  = 0;
        $newId = Database::insert('products', $product);
        $this->success('Product duplicated!', ['id' => $newId]);
    }

    public function bulk(): void {
        CSRF::validateOrFail();
        $action     = $this->request->post('action');
        $productIds = $this->request->post('ids', []);
        if (!$action || empty($productIds)) {
            $this->error('No action or products selected.');
            return;
        }
        // Verify all belong to this store
        $ids = implode(',', array_map('intval', $productIds));
        $count = (int)Database::fetch("SELECT COUNT(*) as cnt FROM products WHERE id IN ($ids) AND store_id = ?", [$this->storeId])['cnt'];
        if ($count !== count($productIds)) {
            $this->error('Unauthorized action.');
            return;
        }
        switch ($action) {
            case 'activate':
                Database::query("UPDATE products SET status = 'active' WHERE id IN ($ids)");
                break;
            case 'deactivate':
                Database::query("UPDATE products SET status = 'inactive' WHERE id IN ($ids)");
                break;
            case 'delete':
                Database::query("DELETE FROM products WHERE id IN ($ids)");
                break;
            case 'feature':
                Database::query("UPDATE products SET featured = 1 WHERE id IN ($ids)");
                break;
        }
        $this->success('Bulk action applied to ' . count($productIds) . ' products.');
    }

    public function importForm(): void {
        $this->view('admin.products.import', []);
    }

    public function import(): void {
        CSRF::validateOrFail();
        if (!$this->request->hasFile('csv_file')) {
            $this->error('Please select a CSV file to import.');
            return;
        }
        $file = $this->request->file('csv_file');
        try {
            $service = new ProductImportService($this->storeId);
            $result  = $service->importFromCsv($file['tmp_name']);
            $this->success("Imported {$result['imported']} products. {$result['errors']} errors.", $result);
        } catch (Throwable $e) {
            $this->error('Import failed: ' . $e->getMessage());
        }
    }

    public function export(): void {
        $sql  = "SELECT p.name, p.sku, p.price, p.compare_price, p.quantity, p.status,
                        c.name as category, p.description
                 FROM products p LEFT JOIN categories c ON c.id = p.category_id
                 WHERE p.store_id = ? ORDER BY p.name";
        $rows = Database::fetchAll($sql, [$this->storeId]);
        $headers = ['Name','SKU','Price','Compare Price','Quantity','Status','Category','Description'];
        $csv = implode(',', $headers) . "\n";
        foreach ($rows as $r) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"','""',$v) . '"', array_values($r))) . "\n";
        }
        (new Response())->stream($csv, 'text/csv', 'products-export-' . date('Y-m-d') . '.csv');
    }

    public function uploadImages(int $id): void {
        $this->findProduct($id);
        if (!$this->request->hasFile('images')) {
            $this->json(['error' => 'No images provided'], 400);
            return;
        }
        $uploader = (new Upload())->disk('public')->to("uploads/products/$id")->types(['image'])->maxSize(5120);
        $files    = $this->request->files('images');
        $uploaded = [];
        foreach ($files as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) continue;
            $result = $uploader->handle($file);
            $imgId  = Database::insert('product_images', ['product_id' => $id, 'image' => $result['url']]);
            $uploaded[] = ['id' => $imgId, 'url' => $result['url']];
        }
        $this->json(['success' => true, 'images' => $uploaded]);
    }

    public function deleteImage(int $id, int $imgId): void {
        $img = Database::fetch("SELECT pi.* FROM product_images pi JOIN products p ON p.id = pi.product_id WHERE pi.id = ? AND p.store_id = ?", [$imgId, $this->storeId]);
        if (!$img) $this->abort(404);
        Upload::delete($img['image']);
        Database::delete('product_images', 'id = ?', [$imgId]);
        $this->json(['success' => true]);
    }

    public function setPrimaryImage(int $id, int $imgId): void {
        $this->findProduct($id);
        Database::update('product_images', ['is_primary' => 0], 'product_id = ?', [$id]);
        Database::update('product_images', ['is_primary' => 1], 'id = ?', [$imgId]);
        $this->json(['success' => true]);
    }

    public function addVariant(int $id): void {
        CSRF::validateOrFail();
        $this->findProduct($id);
        $variantId = Database::insert('product_variants', [
            'product_id'    => $id,
            'sku'           => $this->request->post('sku', ''),
            'price'         => (float)$this->request->post('price', 0),
            'compare_price' => $this->request->post('compare_price') ?: null,
            'quantity'      => (int)$this->request->post('quantity', 0),
            'weight'        => $this->request->post('weight') ?: null,
            'is_active'     => 1,
        ]);
        // Save attribute options
        foreach ((array)$this->request->post('options', []) as $opt) {
            if (empty($opt['attribute_id']) || empty($opt['value_id'])) continue;
            Database::insert('product_variant_options', [
                'variant_id'         => $variantId,
                'attribute_id'       => $opt['attribute_id'],
                'attribute_value_id' => $opt['value_id'],
            ]);
        }
        $this->success('Variant added.', ['id' => $variantId]);
    }

    public function updateVariant(int $id, int $vid): void {
        CSRF::validateOrFail();
        $this->findProduct($id);
        $variant = Database::fetch("SELECT * FROM product_variants WHERE id = ? AND product_id = ?", [$vid, $id]);
        if (!$variant) $this->abort(404, 'Variant not found');
        $data = [
            'sku'           => $this->request->post('sku', $variant['sku']),
            'price'         => (float)$this->request->post('price', $variant['price']),
            'compare_price' => $this->request->post('compare_price') ?: null,
            'quantity'      => (int)$this->request->post('quantity', $variant['quantity']),
            'is_active'     => $this->request->boolean('is_active', true) ? 1 : 0,
        ];
        Database::update('product_variants', $data, 'id = ?', [$vid]);
        $this->success('Variant updated.');
    }

    public function deleteVariant(int $id, int $vid): void {
        $this->findProduct($id);
        Database::delete('product_variants', 'id = ? AND product_id = ?', [$vid, $id]);
        $this->success('Variant deleted.');
    }

    private function findProduct(int $id): array {
        $product = Database::fetch("SELECT * FROM products WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$product) $this->abort(404, 'Product not found');
        return $product;
    }
}
