<?php

class PageController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $pages = Database::fetchAll("SELECT * FROM pages WHERE store_id = ? ORDER BY sort_order, title", [$this->storeId]);
        $this->view('admin.pages.index', compact('pages'));
    }

    public function create(): void {
        $this->view('admin.pages.create', []);
    }

    public function store(): void {
        CSRF::validateOrFail();
        $data = $this->validate(['title' => 'required|min:2', 'content' => 'nullable']);
        $data['store_id'] = $this->storeId;
        $data['slug']     = slugify($data['title']);
        $data['meta_title'] = $this->request->post('meta_title', '');
        $data['meta_description'] = $this->request->post('meta_description', '');
        Database::insert('pages', $data);
        $this->success('Page created.');
    }

    public function edit(int $id): void {
        $page = Database::fetch("SELECT * FROM pages WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$page) $this->abort(404);
        $this->view('admin.pages.edit', compact('page'));
    }

    public function update(int $id): void {
        CSRF::validateOrFail();
        $data = $this->request->only(['title','content','meta_title','meta_description']);
        $data['is_active'] = $this->request->boolean('is_active', true) ? 1 : 0;
        Database::update('pages', $data, 'id = ? AND store_id = ?', [$id, $this->storeId]);
        $this->success('Page updated.');
    }

    public function destroy(int $id): void {
        Database::delete('pages', 'id = ? AND store_id = ?', [$id, $this->storeId]);
        $this->success('Page deleted.');
    }
}
