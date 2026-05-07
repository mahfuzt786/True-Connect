<?php

class AdminController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireRole('super_admin');
    }

    public function dashboard(): void {
        $stats = [
            'total_stores'        => (int)(Database::fetch("SELECT COUNT(*) c FROM stores")['c']),
            'active_stores'       => (int)(Database::fetch("SELECT COUNT(*) c FROM stores WHERE status='active'")['c']),
            'total_users'         => (int)(Database::fetch("SELECT COUNT(*) c FROM users")['c']),
            'total_orders'        => (int)(Database::fetch("SELECT COUNT(*) c FROM orders")['c']),
            'total_revenue'       => (float)(Database::fetch("SELECT COALESCE(SUM(total),0) c FROM orders WHERE payment_status='paid'")['c']),
            'mrr'                 => (float)(Database::fetch("SELECT COALESCE(SUM(amount),0) c FROM subscriptions WHERE status IN ('active','trialing') AND billing_cycle='monthly'")['c']),
            'active_subscriptions'=> (int)(Database::fetch("SELECT COUNT(*) c FROM subscriptions WHERE status='active'")['c']),
            'trialing'            => (int)(Database::fetch("SELECT COUNT(*) c FROM subscriptions WHERE status='trialing'")['c']),
        ];
        $recentStores = Database::fetchAll("SELECT s.*, u.email as owner_email FROM stores s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC LIMIT 10");
        $recentOrders = Database::fetchAll("SELECT o.*, s.name as store_name FROM orders o LEFT JOIN stores s ON s.id = o.store_id ORDER BY o.created_at DESC LIMIT 10");
        $revenueChart = Database::fetchAll("SELECT DATE(created_at) d, SUM(total) total FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY d");
        $this->view('superadmin.dashboard', compact('stats','recentStores','recentOrders','revenueChart'));
    }

    public function stores(): void {
        $search = $this->request->get('search', '');
        $sql    = "SELECT s.*, u.email as owner_email, p.name as plan_name, sub.status as sub_status,
                          (SELECT COUNT(*) FROM orders WHERE store_id = s.id) as order_count,
                          (SELECT COUNT(*) FROM products WHERE store_id = s.id) as product_count
                   FROM stores s
                   LEFT JOIN users u ON u.id = s.user_id
                   LEFT JOIN subscriptions sub ON sub.store_id = s.id AND sub.status IN ('active','trialing')
                   LEFT JOIN plans p ON p.id = sub.plan_id
                   WHERE 1=1";
        $params = [];
        if ($search) { $sql .= " AND (s.name LIKE ? OR u.email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
        $sql   .= " ORDER BY s.created_at DESC";
        $stores = $this->paginate($sql, $params, 25);
        $this->view('superadmin.stores.index', compact('stores','search'));
    }

    public function storeDetail(int $id): void {
        $store = Database::fetch("SELECT s.*, u.name as owner_name, u.email as owner_email FROM stores s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?", [$id]);
        if (!$store) $this->abort(404);
        $stats = [
            'orders'   => Database::fetch("SELECT COUNT(*) c, COALESCE(SUM(total),0) total FROM orders WHERE store_id = ?", [$id]),
            'products' => (int)(Database::fetch("SELECT COUNT(*) c FROM products WHERE store_id = ?", [$id])['c']),
            'vendors'  => (int)(Database::fetch("SELECT COUNT(*) c FROM vendors WHERE store_id = ?", [$id])['c']),
        ];
        $subscription = Database::fetch("SELECT sub.*, p.name as plan_name FROM subscriptions sub JOIN plans p ON p.id = sub.plan_id WHERE sub.store_id = ? ORDER BY sub.created_at DESC LIMIT 1", [$id]);
        $this->view('superadmin.stores.show', compact('store','stats','subscription'));
    }

    public function createStoreForm(): void {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order");
        $this->view('superadmin.stores.create', compact('plans'));
    }

    public function createStore(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'store_name'    => 'required|min:2|max:191',
            'admin_name'    => 'required|min:2|max:100',
            'admin_email'   => 'required|email',
            'admin_password'=> 'required|password_strength',
            'type'          => 'required|in:ecommerce,marketplace',
            'currency'      => 'required',
            'language'      => 'required',
            'timezone'      => 'required',
            'plan_id'       => 'required|integer|exists:plans,id',
        ]);

        $email = strtolower(trim($data['admin_email']));

        // Hard rule: store-admin email must NOT be a super_admin's email.
        $existing = Database::fetch("SELECT id, role FROM users WHERE email = ?", [$email]);
        if ($existing && $existing['role'] === 'super_admin') {
            $this->error('A super-admin email cannot also be a store admin. Use a different email.');
            return;
        }
        // Same restriction against your own logged-in email.
        if ($email === strtolower($this->currentUser['email'])) {
            $this->error('Use a different email for the store admin — not your own super-admin email.');
            return;
        }

        try {
            $storeId = Database::transaction(function () use ($data, $email, $existing) {
                // Create or reuse the store-admin user.
                if ($existing) {
                    $userId = (int)$existing['id'];
                    if ($existing['role'] === 'customer') {
                        Database::update('users', ['role' => 'store_owner'], 'id = ?', [$userId]);
                    }
                } else {
                    $userId = Database::insert('users', [
                        'name'              => $data['admin_name'],
                        'email'             => $email,
                        'password'          => password_hash($data['admin_password'], PASSWORD_BCRYPT, ['cost' => 12]),
                        'role'              => 'store_owner',
                        'status'            => 'active',
                        'email_verified_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // Slug
                $slug = slugify($data['store_name']);
                $base = $slug; $i = 1;
                while (Database::fetch("SELECT id FROM stores WHERE slug = ?", [$slug])) {
                    $slug = $base . '-' . $i++;
                }

                $currencySymbols = ['INR'=>'₹','USD'=>'$','EUR'=>'€','AED'=>'د.إ ','GBP'=>'£','BDT'=>'৳','JPY'=>'¥'];
                $storeId = Database::insert('stores', [
                    'user_id'         => $userId,
                    'name'            => $data['store_name'],
                    'slug'            => $slug,
                    'subdomain'       => $slug,
                    'type'            => $data['type'],
                    'currency'        => $data['currency'],
                    'currency_symbol' => $currencySymbols[$data['currency']] ?? '₹',
                    'language'        => $data['language'],
                    'timezone'        => $data['timezone'],
                    'status'          => 'active',
                    'theme'           => 'default',
                    'onboarding_completed' => 1,
                ]);

                // Subscription. The "Custom" / free plan gets an active,
                // non-expiring subscription with amount=0 — no fee on setup,
                // super admin can edit later from /admin/subscriptions.
                $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$data['plan_id']]);
                $isFree = ((float)$plan['price_monthly'] === 0.0 && (int)$plan['trial_days'] === 0);

                if ($isFree) {
                    Database::insert('subscriptions', [
                        'store_id'             => $storeId,
                        'plan_id'              => $plan['id'],
                        'billing_cycle'        => 'monthly',
                        'status'               => 'active',
                        'trial_ends_at'        => null,
                        'current_period_start' => date('Y-m-d H:i:s'),
                        'current_period_end'   => date('Y-m-d H:i:s', strtotime('+100 years')),
                        'amount'               => 0,
                        'currency'             => $plan['currency'],
                        'gateway'              => 'manual',
                    ]);
                } else {
                    Database::insert('subscriptions', [
                        'store_id'             => $storeId,
                        'plan_id'              => $plan['id'],
                        'billing_cycle'        => 'monthly',
                        'status'               => 'trialing',
                        'trial_ends_at'        => date('Y-m-d H:i:s', strtotime("+{$plan['trial_days']} days")),
                        'current_period_start' => date('Y-m-d H:i:s'),
                        'current_period_end'   => date('Y-m-d H:i:s', strtotime("+{$plan['trial_days']} days")),
                        'amount'               => $plan['price_monthly'],
                        'currency'             => $plan['currency'],
                        'gateway'              => 'manual',
                    ]);
                }

                // Default payment + categories
                Database::insert('payment_methods', [
                    'store_id'     => $storeId,
                    'gateway'      => 'cod',
                    'is_enabled'   => 1,
                    'display_name' => 'Cash on Delivery',
                ]);
                foreach (['Featured','New Arrivals','Best Sellers'] as $i => $catName) {
                    Database::insert('categories', [
                        'store_id' => $storeId, 'name' => $catName,
                        'slug' => slugify($catName), 'sort_order' => $i,
                    ]);
                }
                return $storeId;
            });

            $this->auditLog('store.create', 'Store', $storeId, [], ['email' => $email]);
            $this->success('Store created. Store admin can now log in with the email and password you set.');
        } catch (Throwable $e) {
            logError('Admin create store: ' . $e->getMessage());
            $this->error('Could not create store. Make sure the email is not already taken by another store/vendor.');
        }
    }

    /**
     * Switch into a store's admin context. Super admin keeps their role + menu;
     * resolveStore() in Controller will pick up this session key and treat the
     * selected store as the active one across all admin pages.
     */
    public function impersonateStore(int $id): void {
        CSRF::validateOrFail();
        $store = Database::fetch("SELECT id, name FROM stores WHERE id = ?", [$id]);
        if (!$store) $this->abort(404);
        Session::set('super_admin_active_store_id', (int)$store['id']);
        $this->auditLog('store.impersonate', 'Store', (int)$store['id']);
        Session::flash('success', "Switched into '{$store['name']}'. You are still logged in as super admin.");
        redirect('/dashboard');
    }

    public function leaveImpersonation(): void {
        CSRF::validateOrFail();
        Session::forget('super_admin_active_store_id');
        Session::flash('success', 'Left store context.');
        redirect('/admin/stores');
    }

    public function suspendStore(int $id): void {
        Database::update('stores', ['status' => 'suspended'], 'id = ?', [$id]);
        $this->auditLog('store.suspend', 'Store', $id);
        $this->success('Store suspended.');
    }

    public function activateStore(int $id): void {
        Database::update('stores', ['status' => 'active'], 'id = ?', [$id]);
        $this->auditLog('store.activate', 'Store', $id);
        $this->success('Store activated.');
    }

    public function deleteStore(int $id): void {
        Database::delete('stores', 'id = ?', [$id]);
        $this->auditLog('store.delete', 'Store', $id);
        $this->success('Store deleted.');
    }

    public function users(): void {
        $search = $this->request->get('search', '');
        $role   = $this->request->get('role', '');
        $sql    = "SELECT * FROM users WHERE 1=1";
        $params = [];
        if ($search) { $sql .= " AND (name LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
        if ($role)   { $sql .= " AND role = ?"; $params[] = $role; }
        $sql .= " ORDER BY created_at DESC";
        $users = $this->paginate($sql, $params, 25);
        $this->view('superadmin.users.index', compact('users','search','role'));
    }

    public function userDetail(int $id): void {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) $this->abort(404);
        $stores = Database::fetchAll("SELECT * FROM stores WHERE user_id = ?", [$id]);
        $orders = Database::fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 20", [$id]);
        $this->view('superadmin.users.show', compact('user','stores','orders'));
    }

    public function userActivity(int $id): void {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) $this->abort(404);

        $action = (string)$this->request->get('action', '');
        $where  = ['user_id = ?'];
        $params = [$id];
        if ($action) { $where[] = 'action = ?'; $params[] = $action; }

        $sql = "SELECT * FROM activity_logs WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
        $logs = $this->paginate($sql, $params, 100);

        // Distinct action names for the filter
        $actions = Database::fetchAll("SELECT action, COUNT(*) c FROM activity_logs WHERE user_id = ? GROUP BY action ORDER BY c DESC", [$id]);

        $this->view('superadmin.activity.user', compact('user', 'logs', 'actions', 'action'));
    }

    public function storeActivity(int $id): void {
        $store = Database::fetch("SELECT * FROM stores WHERE id = ?", [$id]);
        if (!$store) $this->abort(404);

        $action = (string)$this->request->get('action', '');
        $where  = ['store_id = ?'];
        $params = [$id];
        if ($action) { $where[] = 'action = ?'; $params[] = $action; }

        $sql = "SELECT a.*, u.name AS u_name, u.email AS u_email
                FROM activity_logs a
                LEFT JOIN users u ON u.id = a.user_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.created_at DESC";
        $logs = $this->paginate($sql, $params, 100);

        $actions = Database::fetchAll("SELECT action, COUNT(*) c FROM activity_logs WHERE store_id = ? GROUP BY action ORDER BY c DESC", [$id]);

        $this->view('superadmin.activity.store', compact('store', 'logs', 'actions', 'action'));
    }

    public function approveUser(int $id): void {
        CSRF::validateOrFail();
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) $this->abort(404);
        Database::update('users', ['status' => 'active', 'email_verified_at' => $user['email_verified_at'] ?? date('Y-m-d H:i:s')], 'id = ?', [$id]);
        $this->auditLog('user.approve', 'User', $id, ['status' => $user['status']], ['status' => 'active']);
        $this->success('User approved.');
    }

    public function rejectUser(int $id): void {
        CSRF::validateOrFail();
        // Block self-reject — super_admin can't lock themselves out.
        if ((int)$id === (int)($this->currentUser['id'] ?? 0)) {
            $this->error('You cannot reject your own super-admin account.');
            return;
        }
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) $this->abort(404);
        if ($user['role'] === 'super_admin') {
            $this->error('Super-admin accounts cannot be rejected.');
            return;
        }
        Database::update('users', ['status' => 'inactive'], 'id = ?', [$id]);
        $this->auditLog('user.reject', 'User', $id, ['status' => $user['status']], ['status' => 'inactive']);
        $this->success('User rejected.');
    }

    public function banUser(int $id): void {
        CSRF::validateOrFail();
        if ((int)$id === (int)($this->currentUser['id'] ?? 0)) {
            $this->error('You cannot ban your own account.');
            return;
        }
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) $this->abort(404);
        if ($user['role'] === 'super_admin') {
            $this->error('Super-admin accounts cannot be banned.');
            return;
        }
        Database::update('users', ['status' => 'banned'], 'id = ?', [$id]);
        $this->auditLog('user.ban', 'User', $id, ['status' => $user['status']], ['status' => 'banned']);
        $this->success('User banned.');
    }

    public function unbanUser(int $id): void {
        CSRF::validateOrFail();
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) $this->abort(404);
        Database::update('users', ['status' => 'active'], 'id = ?', [$id]);
        $this->auditLog('user.unban', 'User', $id, ['status' => $user['status']], ['status' => 'active']);
        $this->success('User reinstated.');
    }

    public function approveStore(int $id): void {
        CSRF::validateOrFail();
        $store = Database::fetch("SELECT * FROM stores WHERE id = ?", [$id]);
        if (!$store) $this->abort(404);
        Database::update('stores', ['status' => 'active'], 'id = ?', [$id]);
        $this->auditLog('store.approve', 'Store', $id, ['status' => $store['status']], ['status' => 'active']);
        $this->success('Store approved.');
    }

    public function rejectStore(int $id): void {
        CSRF::validateOrFail();
        $store = Database::fetch("SELECT * FROM stores WHERE id = ?", [$id]);
        if (!$store) $this->abort(404);
        Database::update('stores', ['status' => 'inactive'], 'id = ?', [$id]);
        $this->auditLog('store.reject', 'Store', $id, ['status' => $store['status']], ['status' => 'inactive']);
        $this->success('Store rejected.');
    }

    public function subscriptions(): void {
        $sql = "SELECT sub.*, s.name as store_name, p.name as plan_name, u.email as owner_email
                FROM subscriptions sub
                JOIN stores s ON s.id = sub.store_id
                JOIN plans p ON p.id = sub.plan_id
                JOIN users u ON u.id = s.user_id
                ORDER BY sub.created_at DESC";
        $subscriptions = $this->paginate($sql, [], 25);
        $this->view('superadmin.subscriptions', compact('subscriptions'));
    }

    public function plans(): void {
        $plans = Database::fetchAll("SELECT * FROM plans ORDER BY sort_order");
        // Build a union of curated presets + every feature already in use across plans,
        // so every feature in the system appears as a checkbox.
        $featureSet = self::planFeaturePresets();
        foreach ($plans as $p) {
            $decoded = json_decode($p['features'] ?? '[]', true);
            if (is_array($decoded)) {
                foreach ($decoded as $f) {
                    $f = trim((string)$f);
                    if ($f !== '' && !in_array($f, $featureSet, true)) $featureSet[] = $f;
                }
            }
        }
        $this->view('superadmin.plans.index', ['plans' => $plans, 'allFeatures' => $featureSet]);
    }

    /** @return string[] Curated list of common plan-feature labels. */
    public static function planFeaturePresets(): array {
        return [
            'Email support',
            'Priority support',
            'Dedicated support',
            'Account manager',
            'Basic analytics',
            'Advanced analytics',
            'Full analytics',
            'Custom domain',
            'API access',
            'White-label option',
            'Custom integrations',
            'SLA guarantee',
            'Staff accounts',
            'Multi-vendor marketplace',
        ];
    }

    private function buildFeaturesJson(): string {
        $checked = (array)$this->request->post('features', []);
        $clean   = array_values(array_filter(array_map(fn($v) => trim((string)$v), $checked), fn($v) => $v !== ''));
        return json_encode(array_values(array_unique($clean)));
    }

    public function createPlan(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'name'           => 'required|min:2',
            'price_monthly'  => 'required|numeric|min:0',
            'price_yearly'   => 'required|numeric|min:0',
            'trial_days'     => 'required|integer',
        ]);
        $data['slug']     = slugify($data['name']);
        $data['features'] = $this->buildFeaturesJson();
        $data['products_limit']   = $this->request->post('products_limit') !== '' ? (int)$this->request->post('products_limit') : null;
        $data['vendors_limit']    = $this->request->post('vendors_limit') !== '' ? (int)$this->request->post('vendors_limit') : null;
        $data['storage_limit_mb'] = $this->request->post('storage_limit_mb') !== '' ? (int)$this->request->post('storage_limit_mb') : null;
        $data['marketplace_enabled'] = $this->request->boolean('marketplace_enabled') ? 1 : 0;
        $data['custom_domain']    = $this->request->boolean('custom_domain') ? 1 : 0;
        $data['analytics']        = $this->request->boolean('analytics') ? 1 : 0;
        $data['api_access']       = $this->request->boolean('api_access') ? 1 : 0;
        Database::insert('plans', $data);
        $this->success('Plan created.');
    }

    public function updatePlan(int $id): void {
        CSRF::validateOrFail();
        $data = $this->request->only(['name','price_monthly','price_yearly','trial_days']);
        $data['products_limit']      = $this->request->post('products_limit') !== '' ? (int)$this->request->post('products_limit') : null;
        $data['vendors_limit']       = $this->request->post('vendors_limit') !== '' ? (int)$this->request->post('vendors_limit') : null;
        $data['storage_limit_mb']    = $this->request->post('storage_limit_mb') !== '' ? (int)$this->request->post('storage_limit_mb') : null;
        $data['features']            = $this->buildFeaturesJson();
        $data['marketplace_enabled'] = $this->request->boolean('marketplace_enabled') ? 1 : 0;
        $data['custom_domain']       = $this->request->boolean('custom_domain') ? 1 : 0;
        $data['analytics']           = $this->request->boolean('analytics') ? 1 : 0;
        $data['api_access']          = $this->request->boolean('api_access') ? 1 : 0;
        $data['is_active']           = $this->request->boolean('is_active', true) ? 1 : 0;
        Database::update('plans', $data, 'id = ?', [$id]);
        $this->success('Plan updated.');
    }

    public function deletePlan(int $id): void {
        Database::delete('plans', 'id = ?', [$id]);
        $this->success('Plan deleted.');
    }

    public function revenue(): void {
        $monthly = Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue, COUNT(*) as transactions
             FROM invoices_subscription WHERE status = 'paid' GROUP BY month ORDER BY month DESC LIMIT 12"
        );
        $byPlan = Database::fetchAll(
            "SELECT p.name, COUNT(sub.id) as subscribers, SUM(sub.amount) as mrr
             FROM plans p LEFT JOIN subscriptions sub ON sub.plan_id = p.id AND sub.status = 'active'
             GROUP BY p.id ORDER BY mrr DESC"
        );
        $this->view('superadmin.revenue', compact('monthly','byPlan'));
    }

    public function analytics(): void {
        $signupsChart = Database::fetchAll("SELECT DATE(created_at) d, COUNT(*) c FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY d");
        $storesChart  = Database::fetchAll("SELECT DATE(created_at) d, COUNT(*) c FROM stores WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY d");
        $ordersChart  = Database::fetchAll("SELECT DATE(created_at) d, COUNT(*) c, SUM(total) t FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY d");
        $this->view('superadmin.analytics', compact('signupsChart','storesChart','ordersChart'));
    }

    public function settings(): void {
        $settings = Database::fetchAll("SELECT * FROM settings WHERE store_id IS NULL");
        $grouped  = [];
        foreach ($settings as $s) {
            // Hide the social_links row from the generic editor — it gets a dedicated section.
            if ($s['key'] === 'social_links') continue;
            $grouped[$s['group']][$s['key']] = $s;
        }
        $socialLinks = social_links();
        $platforms   = social_platforms();
        $this->view('superadmin.settings', compact('grouped', 'socialLinks', 'platforms') + ['settings' => $grouped]);
    }

    public function updateSettings(): void {
        CSRF::validateOrFail();
        $settings = $this->request->post('settings', []);
        foreach ($settings as $key => $value) {
            Database::query(
                "INSERT INTO settings (store_id, `key`, value, type) VALUES (NULL, ?, ?, 'string')
                 ON DUPLICATE KEY UPDATE value = VALUES(value)",
                [$key, is_array($value) ? json_encode($value) : (string)$value]
            );
        }
        // Social links: validated, JSON-encoded as a single setting.
        $social = $this->request->post('social_links', []);
        $clean  = [];
        foreach ($social as $platform => $url) {
            $url = trim((string)$url);
            if ($url === '') continue;
            // Only accept http(s) URLs to avoid javascript: / data: payloads.
            if (!preg_match('#^https?://#i', $url)) continue;
            $clean[$platform] = $url;
        }
        Database::query(
            "INSERT INTO settings (store_id, `group`, `key`, value, type) VALUES (NULL, 'social', 'social_links', ?, 'json')
             ON DUPLICATE KEY UPDATE value = VALUES(value), type = 'json'",
            [json_encode($clean)]
        );
        $this->success('Settings updated.');
    }

    public function auditLogs(): void {
        $sql  = "SELECT a.*, u.email as user_email, s.name as store_name
                 FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN stores s ON s.id = a.store_id
                 ORDER BY a.created_at DESC";
        $logs = $this->paginate($sql, [], 50);
        $this->view('superadmin.audit', compact('logs'));
    }

    public function emailLogs(): void {
        $logs = $this->paginate("SELECT * FROM email_logs ORDER BY created_at DESC", [], 50);
        $this->view('superadmin.email-logs', compact('logs'));
    }

    public function jobs(): void {
        $pending = Database::fetchAll("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 50");
        $failed  = Database::fetchAll("SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 50");
        $this->view('superadmin.jobs', compact('pending','failed'));
    }

    public function retryJob(int $id): void {
        $job = Database::fetch("SELECT * FROM failed_jobs WHERE id = ?", [$id]);
        if ($job) {
            Database::insert('jobs', ['queue' => $job['queue'], 'payload' => $job['payload'], 'attempts' => 0, 'available_at' => now()]);
            Database::delete('failed_jobs', 'id = ?', [$id]);
        }
        $this->success('Job re-queued.');
    }

    public function deleteJob(int $id): void {
        Database::delete('failed_jobs', 'id = ?', [$id]);
        Database::delete('jobs', 'id = ?', [$id]);
        $this->success('Job deleted.');
    }
}
