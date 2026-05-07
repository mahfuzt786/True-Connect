<?php

class TaxController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $rates = Database::fetchAll("SELECT * FROM tax_rates WHERE store_id = ? ORDER BY priority", [$this->storeId]);
        $this->view('admin.tax.index', compact('rates'));
    }

    public function store(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'name'    => 'required|min:2',
            'country' => 'required',
            'rate'    => 'required|numeric|min:0|max:100',
        ]);
        $data['store_id']   = $this->storeId;
        $data['state']      = $this->request->post('state', '');
        $data['tax_class']  = $this->request->post('tax_class', 'standard');
        Database::insert('tax_rates', $data);
        $this->success('Tax rate created.');
    }

    public function update(int $id): void {
        CSRF::validateOrFail();
        $data = $this->request->only(['name','country','state','rate','tax_class']);
        Database::update('tax_rates', $data, 'id = ? AND store_id = ?', [$id, $this->storeId]);
        $this->success('Tax rate updated.');
    }

    public function destroy(int $id): void {
        Database::delete('tax_rates', 'id = ? AND store_id = ?', [$id, $this->storeId]);
        $this->success('Tax rate deleted.');
    }
}
