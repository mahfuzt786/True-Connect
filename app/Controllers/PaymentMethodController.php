<?php

class PaymentMethodController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $methods = Database::fetchAll("SELECT * FROM payment_methods WHERE store_id = ?", [$this->storeId]);
        $byGateway = [];
        foreach ($methods as $m) $byGateway[$m['gateway']] = $m;
        $this->view('admin.payments.index', compact('methods','byGateway'));
    }

    public function update(string $gateway): void {
        CSRF::validateOrFail();
        $credentials = [];
        $allowed = ['stripe','razorpay','paypal','bank_transfer','cod','manual'];
        if (!in_array($gateway, $allowed)) $this->abort(400);

        switch ($gateway) {
            case 'stripe':
                $credentials = [
                    'public_key'  => $this->request->post('public_key', ''),
                    'secret_key'  => encryptData($this->request->post('secret_key', '')),
                ];
                break;
            case 'razorpay':
                $credentials = [
                    'key_id'     => $this->request->post('key_id', ''),
                    'key_secret' => encryptData($this->request->post('key_secret', '')),
                ];
                break;
            case 'paypal':
                $credentials = [
                    'client_id'     => $this->request->post('client_id', ''),
                    'client_secret' => encryptData($this->request->post('client_secret', '')),
                    'mode'          => $this->request->post('mode', 'sandbox'),
                ];
                break;
            case 'bank_transfer':
                $credentials = [
                    'bank_name'      => $this->request->post('bank_name', ''),
                    'account_name'   => $this->request->post('account_name', ''),
                    'account_number' => $this->request->post('account_number', ''),
                    'routing'        => $this->request->post('routing', ''),
                    'instructions'   => $this->request->post('instructions', ''),
                ];
                break;
        }

        Database::query(
            "INSERT INTO payment_methods (store_id, gateway, is_enabled, test_mode, credentials, display_name, description)
             VALUES (?, ?, 1, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE credentials = VALUES(credentials), display_name = VALUES(display_name), description = VALUES(description), test_mode = VALUES(test_mode)",
            [
                $this->storeId,
                $gateway,
                $this->request->boolean('test_mode', true) ? 1 : 0,
                json_encode($credentials),
                $this->request->post('display_name', ucfirst($gateway)),
                $this->request->post('description', ''),
            ]
        );
        $this->success(ucfirst($gateway) . ' settings saved.');
    }

    public function toggle(string $gateway): void {
        CSRF::validateOrFail();
        $method = Database::fetch("SELECT * FROM payment_methods WHERE store_id = ? AND gateway = ?", [$this->storeId, $gateway]);
        if (!$method) $this->abort(404);
        Database::update('payment_methods', ['is_enabled' => $method['is_enabled'] ? 0 : 1], 'id = ?', [$method['id']]);
        $this->json(['success' => true, 'enabled' => !$method['is_enabled']]);
    }
}
