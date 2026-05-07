<?php
namespace Api;

use Database;
use SubscriptionService;

class SubscriptionApiController extends ApiController {
    public function current(): void {
        $this->requireUser();
        $store = Database::fetch("SELECT id FROM stores WHERE user_id = ? LIMIT 1", [$this->user['id']]);
        if (!$store) $this->error('No store', 404);
        $sub = Database::fetch("SELECT sub.*, p.name plan_name FROM subscriptions sub JOIN plans p ON p.id = sub.plan_id WHERE sub.store_id = ? ORDER BY sub.created_at DESC LIMIT 1", [$store['id']]);
        $this->ok($sub);
    }
    public function plans(): void {
        $this->ok(Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug <> 'custom' ORDER BY sort_order"));
    }
    public function subscribe(): void {
        $this->requireUser();
        $data = $this->validate(['plan_id' => 'required|integer', 'cycle' => 'required|in:monthly,yearly']);
        $store = Database::fetch("SELECT id FROM stores WHERE user_id = ? LIMIT 1", [$this->user['id']]);
        if (!$store) $this->error('No store');
        $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$data['plan_id']]);
        if (!$plan) $this->error('Plan not found', 404);
        $result = (new SubscriptionService())->subscribe($store['id'], $plan, $data['cycle']);
        $this->ok($result, 'Subscribed');
    }
    public function cancel(): void {
        $this->requireUser();
        $store = Database::fetch("SELECT id FROM stores WHERE user_id = ? LIMIT 1", [$this->user['id']]);
        $sub = Database::fetch("SELECT * FROM subscriptions WHERE store_id = ? AND status IN ('active','trialing')", [$store['id']]);
        if (!$sub) $this->error('No active subscription');
        (new SubscriptionService())->cancel($sub);
        $this->ok(null, 'Cancelled');
    }
}
