<?php

class SubscriptionController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void {
        $this->requireStore();
        $sub = Database::fetch(
            "SELECT sub.*, p.name as plan_name, p.features as plan_features, p.products_limit, p.vendors_limit
             FROM subscriptions sub JOIN plans p ON p.id = sub.plan_id
             WHERE sub.store_id = ? ORDER BY sub.created_at DESC LIMIT 1",
            [$this->currentStore['id']]
        );
        $invoices = Database::fetchAll("SELECT * FROM invoices_subscription WHERE store_id = ? ORDER BY created_at DESC LIMIT 12", [$this->currentStore['id']]);
        $usage = [
            'products' => (int)(Database::fetch("SELECT COUNT(*) c FROM products WHERE store_id = ?", [$this->currentStore['id']])['c']),
            'vendors'  => (int)(Database::fetch("SELECT COUNT(*) c FROM vendors WHERE store_id = ?", [$this->currentStore['id']])['c']),
            'orders'   => (int)(Database::fetch("SELECT COUNT(*) c FROM orders WHERE store_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$this->currentStore['id']])['c']),
        ];
        $this->view('admin.subscription.index', compact('sub','invoices','usage'));
    }

    public function plans(): void {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug <> 'custom' ORDER BY sort_order");
        $expired = $this->request->boolean('expired');
        $this->view('admin.subscription.plans', compact('plans','expired'));
    }

    public function subscribe(int $planId): void {
        CSRF::validateOrFail();
        $this->requireStore();
        $plan  = Database::fetch("SELECT * FROM plans WHERE id = ? AND is_active = 1", [$planId]);
        if (!$plan) $this->error('Invalid plan.');
        $cycle = $this->request->post('cycle', 'monthly');
        try {
            $subService = new SubscriptionService();
            $result     = $subService->subscribe($this->currentStore['id'], $plan, $cycle);
            if (!empty($result['redirect'])) { redirect($result['redirect']); return; }
            $this->success('Subscription activated successfully!');
        } catch (Throwable $e) {
            logError('Subscription error: ' . $e->getMessage());
            $this->error('Failed to subscribe: ' . $e->getMessage());
        }
    }

    public function cancel(): void {
        CSRF::validateOrFail();
        $this->requireStore();
        $sub = Database::fetch("SELECT * FROM subscriptions WHERE store_id = ? AND status IN ('active','trialing') LIMIT 1", [$this->currentStore['id']]);
        if (!$sub) { $this->error('No active subscription.'); return; }
        try {
            (new SubscriptionService())->cancel($sub);
            $this->success('Subscription cancelled. Access continues until the end of the billing period.');
        } catch (Throwable $e) {
            $this->error('Cancel failed: ' . $e->getMessage());
        }
    }

    public function invoices(): void {
        $this->requireStore();
        $invoices = $this->paginate("SELECT * FROM invoices_subscription WHERE store_id = ? ORDER BY created_at DESC", [$this->currentStore['id']], 20);
        $this->view('admin.subscription.invoices', compact('invoices'));
    }

    public function downloadInvoice(int $id): void {
        $this->requireStore();
        $invoice = Database::fetch("SELECT * FROM invoices_subscription WHERE id = ? AND store_id = ?", [$id, $this->currentStore['id']]);
        if (!$invoice) $this->abort(404);
        $pdf = (new InvoiceService())->generateSubscriptionInvoice($invoice, $this->currentStore);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice-' . $invoice['invoice_number'] . '.pdf"');
        echo $pdf;
        exit;
    }

    public function billingPortal(): void {
        $this->requireStore();
        try {
            $url = (new SubscriptionService())->createBillingPortalSession($this->currentStore['id']);
            redirect($url);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
