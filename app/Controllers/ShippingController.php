<?php

class ShippingController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $zones = Database::fetchAll("SELECT * FROM shipping_zones WHERE store_id = ? ORDER BY sort_order", [$this->storeId]);
        foreach ($zones as &$z) {
            $z['rates'] = Database::fetchAll("SELECT * FROM shipping_rates WHERE zone_id = ?", [$z['id']]);
        }
        $this->view('admin.shipping.index', compact('zones'));
    }

    public function createZone(): void {
        CSRF::validateOrFail();
        $data = $this->validate(['name' => 'required|min:2']);
        $data['store_id']  = $this->storeId;
        $data['countries'] = json_encode((array)$this->request->post('countries', []));
        Database::insert('shipping_zones', $data);
        $this->success('Shipping zone created.');
    }

    public function updateZone(int $id): void {
        CSRF::validateOrFail();
        $data = $this->request->only(['name']);
        $data['countries'] = json_encode((array)$this->request->post('countries', []));
        Database::update('shipping_zones', $data, 'id = ? AND store_id = ?', [$id, $this->storeId]);
        $this->success('Shipping zone updated.');
    }

    public function deleteZone(int $id): void {
        Database::delete('shipping_zones', 'id = ? AND store_id = ?', [$id, $this->storeId]);
        $this->success('Zone deleted.');
    }

    public function createRate(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'zone_id' => 'required|integer',
            'name'    => 'required|min:2',
            'type'    => 'required|in:flat,weight,price,free',
            'rate'    => 'required|numeric|min:0',
        ]);
        Database::insert('shipping_rates', $data);
        $this->success('Shipping rate added.');
    }

    public function deleteRate(int $id): void {
        Database::delete('shipping_rates', 'id = ?', [$id]);
        $this->success('Rate deleted.');
    }
}
