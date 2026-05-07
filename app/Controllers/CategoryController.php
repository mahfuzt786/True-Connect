<?php

class CategoryController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $categories = Database::fetchAll(
            "SELECT c.*, p.name as parent_name,
                    (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
             FROM categories c LEFT JOIN categories p ON p.id = c.parent_id
             WHERE c.store_id = ? ORDER BY c.sort_order, c.name",
            [$this->storeId]
        );
        $this->view('admin.categories.index', compact('categories'));
    }

    public function store(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'name'        => 'required|min:2|max:191',
            'parent_id'   => 'nullable|integer',
            'description' => 'nullable',
        ]);
        $data['store_id']  = $this->storeId;
        $data['slug']      = slugify($data['name']);
        $data['parent_id'] = $data['parent_id'] ?: null;

        if ($this->request->hasFile('image')) {
            $u = (new Upload())->disk('public')->to('uploads/categories')->types(['image'])->maxSize(2048)->handle($this->request->file('image'));
            $data['image'] = $u['url'];
        }

        $id = Database::insert('categories', $data);
        $this->success('Category created.', ['id' => $id]);
    }

    public function edit(int $id): void {
        $category   = $this->find($id);
        $categories = Database::fetchAll("SELECT id, name FROM categories WHERE store_id = ? AND id != ?", [$this->storeId, $id]);
        $this->view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(int $id): void {
        CSRF::validateOrFail();
        $this->find($id);
        $data = $this->validate([
            'name'        => 'required|min:2|max:191',
            'parent_id'   => 'nullable|integer',
            'description' => 'nullable',
            'is_active'   => 'nullable',
        ]);
        $data['parent_id'] = $data['parent_id'] ?: null;
        $data['is_active'] = $this->request->boolean('is_active', true) ? 1 : 0;
        if ($this->request->hasFile('image')) {
            $u = (new Upload())->disk('public')->to('uploads/categories')->types(['image'])->maxSize(2048)->handle($this->request->file('image'));
            $data['image'] = $u['url'];
        }
        Database::update('categories', $data, 'id = ?', [$id]);
        $this->success('Category updated.');
    }

    public function destroy(int $id): void {
        $this->find($id);
        Database::delete('categories', 'id = ?', [$id]);
        $this->success('Category deleted.');
    }

    public function reorder(): void {
        CSRF::validateOrFail();
        $order = $this->request->post('order', []);
        foreach ($order as $i => $catId) {
            Database::update('categories', ['sort_order' => $i], 'id = ? AND store_id = ?', [$catId, $this->storeId]);
        }
        $this->json(['success' => true]);
    }

    private function find(int $id): array {
        $cat = Database::fetch("SELECT * FROM categories WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$cat) $this->abort(404);
        return $cat;
    }
}
