<?php

class StoreController extends Controller {

    public function setup(): void {
        $this->requireAuth();
        // Super admins must NEVER own a store directly — send them to the
        // proper "create store on behalf of a store admin" flow.
        if (($this->currentUser['role'] ?? '') === 'super_admin') {
            redirect('/admin/stores/create');
            return;
        }
        if ($this->currentStore && $this->currentStore['onboarding_completed']) {
            redirect('/dashboard');
            return;
        }
        // "Custom" is super-admin-only — never expose it in public flows.
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug <> 'custom' ORDER BY sort_order");
        $this->view('admin.store.setup', compact('plans'), 'auth');
    }

    public function storeSetup(): void {
        CSRF::validateOrFail();
        $this->requireAuth();
        // Hard block: super admin cannot become a store owner via this flow.
        if (($this->currentUser['role'] ?? '') === 'super_admin') {
            $this->error('Super admins must create stores via Admin → Stores → Create.');
            redirect('/admin/stores/create');
            return;
        }
        $data = $this->validate([
            'name'     => 'required|min:2|max:191',
            'type'     => 'required|in:ecommerce,marketplace',
            'currency' => 'required',
            'language' => 'required',
            'timezone' => 'required',
            'plan_id'  => 'required|integer|exists:plans,id',
        ]);

        $userId = $this->currentUser['id'];

        try {
            Database::transaction(function() use ($data, $userId) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($data['name'])));
                $base = $slug;
                $i    = 1;
                while (Database::fetch("SELECT id FROM stores WHERE slug = ?", [$slug])) {
                    $slug = $base . '-' . $i++;
                }

                $currencySymbols = ['INR'=>'₹','USD'=>'$','EUR'=>'€','AED'=>'د.إ ','GBP'=>'£','BDT'=>'৳','JPY'=>'¥'];

                $storeId = Database::insert('stores', [
                    'user_id'         => $userId,
                    'name'            => $data['name'],
                    'slug'            => $slug,
                    'subdomain'       => $slug,
                    'type'            => $data['type'],
                    'currency'        => $data['currency'],
                    'currency_symbol' => $currencySymbols[$data['currency']] ?? '$',
                    'language'        => $data['language'],
                    'timezone'        => $data['timezone'],
                    'status'          => 'active',
                    'theme'           => 'default',
                ]);

                // Only promote regular customers — never super_admin / vendor / staff.
                $current = Database::fetch("SELECT role FROM users WHERE id = ?", [$userId]);
                if (($current['role'] ?? '') === 'customer') {
                    Database::update('users', ['role' => 'store_owner'], 'id = ?', [$userId]);
                }

                // Create trial subscription
                $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$data['plan_id']]);
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

                // Default payment methods
                Database::insert('payment_methods', [
                    'store_id'     => $storeId,
                    'gateway'      => 'cod',
                    'is_enabled'   => 1,
                    'display_name' => 'Cash on Delivery',
                ]);

                // Default categories
                $defaults = ['Featured','New Arrivals','Best Sellers'];
                foreach ($defaults as $i => $catName) {
                    Database::insert('categories', [
                        'store_id'   => $storeId,
                        'name'       => $catName,
                        'slug'       => slugify($catName),
                        'sort_order' => $i,
                    ]);
                }

                // Default attributes
                $sizeId = Database::insert('attributes', ['store_id' => $storeId, 'name' => 'Size', 'slug' => 'size', 'type' => 'select']);
                foreach (['S','M','L','XL'] as $val) {
                    Database::insert('attribute_values', ['attribute_id' => $sizeId, 'value' => strtolower($val), 'label' => $val]);
                }
                $colorId = Database::insert('attributes', ['store_id' => $storeId, 'name' => 'Color', 'slug' => 'color', 'type' => 'color']);
                foreach (['Red'=>'#dc3545','Blue'=>'#0d6efd','Green'=>'#198754','Black'=>'#000000','White'=>'#ffffff'] as $label=>$hex) {
                    Database::insert('attribute_values', ['attribute_id' => $colorId, 'value' => strtolower($label), 'label' => $label, 'color_code' => $hex]);
                }
            });

            // Clear the intent keys carried over from registration.
            Session::forget('intent_store_type');
            Session::forget('intent_plan_id');

            Session::flash('success', "Store created successfully!");
            redirect('/store/setup/theme');
        } catch (Throwable $e) {
            logError('Store setup error: ' . $e->getMessage());
            $this->error('Setup failed. Please try again.');
        }
    }

    public function themeSetup(): void {
        $this->requireAuth();
        $this->requireStore();
        $themes = $this->getAvailableThemes();
        $this->view('admin.store.theme', ['themes' => $themes, 'currentTheme' => $this->currentStore['theme']]);
    }

    public function saveTheme(): void {
        CSRF::validateOrFail();
        $this->requireAuth();
        $this->requireStore();
        $theme = $this->request->post('theme', 'default');
        Database::update('stores', ['theme' => $theme, 'onboarding_completed' => 1], 'id = ?', [$this->currentStore['id']]);
        Session::flash('success', 'Theme saved! Welcome to your dashboard.');
        redirect('/dashboard');
    }

    public function settings(): void {
        $this->requireAuth();
        $this->requireStore();
        $this->view('admin.store.settings', ['store' => $this->currentStore]);
    }

    public function updateGeneral(): void {
        CSRF::validateOrFail();
        $this->requireStore();
        $data = $this->validate([
            'name'        => 'required|min:2|max:191',
            'description' => 'nullable',
            'email'       => 'nullable|email',
            'phone'       => 'nullable',
            'address'     => 'nullable',
            'currency'    => 'required',
            'language'    => 'required',
            'timezone'    => 'required',
        ]);
        Database::update('stores', $data, 'id = ?', [$this->currentStore['id']]);
        $this->success('Store settings updated.');
    }

    public function updateAppearance(): void {
        CSRF::validateOrFail();
        $this->requireStore();
        $data = [];
        if ($this->request->hasFile('logo')) {
            $u = (new Upload())->disk('public')->to('uploads/logos')->types(['image'])->maxSize(2048)->handle($this->request->file('logo'));
            $data['logo'] = $u['url'];
        }
        if ($this->request->hasFile('favicon')) {
            $u = (new Upload())->disk('public')->to('uploads/logos')->types(['image'])->maxSize(512)->handle($this->request->file('favicon'));
            $data['favicon'] = $u['url'];
        }
        if ($this->request->hasFile('banner')) {
            $u = (new Upload())->disk('public')->to('uploads/logos')->types(['image'])->maxSize(5120)->handle($this->request->file('banner'));
            $data['banner'] = $u['url'];
        }
        $themeSettings = [
            'primary_color'   => $this->request->post('primary_color', '#0d6efd'),
            'secondary_color' => $this->request->post('secondary_color', '#6c757d'),
            'font_family'     => $this->request->post('font_family', 'Inter'),
            'layout'          => $this->request->post('layout', 'default'),
        ];
        $data['theme_settings'] = json_encode($themeSettings);
        if (!empty($data)) Database::update('stores', $data, 'id = ?', [$this->currentStore['id']]);
        $this->success('Appearance updated.');
    }

    public function updateDomain(): void {
        CSRF::validateOrFail();
        $this->requireStore();
        if (!$this->currentStore['custom_domain'] && !$this->canUseFeature('custom_domain')) {
            $this->error('Custom domain is not available on your plan.');
            return;
        }
        $data = $this->validate([
            'subdomain'     => 'required|alpha_dash|min:3',
            'custom_domain' => 'nullable',
        ]);
        Database::update('stores', $data, 'id = ?', [$this->currentStore['id']]);
        $this->success('Domain settings updated.');
    }

    public function updateSEO(): void {
        CSRF::validateOrFail();
        $this->requireStore();
        $data = $this->request->only(['meta_title','meta_description','google_analytics_id','facebook_pixel_id']);
        Database::update('stores', $data, 'id = ?', [$this->currentStore['id']]);
        $this->success('SEO settings updated.');
    }

    public function updateSocial(): void {
        CSRF::validateOrFail();
        $this->requireStore();
        $clean = [];
        foreach (array_keys(social_platforms()) as $platform) {
            $url = trim((string)$this->request->post($platform, ''));
            if ($url === '') continue;
            if (!preg_match('#^https?://#i', $url)) continue;
            $clean[$platform] = $url;
        }
        Database::update('stores', ['social_links' => json_encode($clean)], 'id = ?', [$this->currentStore['id']]);
        $this->success('Social links updated.');
    }

    public function staff(): void {
        $this->requireStore();
        $staff = Database::fetchAll(
            "SELECT s.*, u.name, u.email, u.avatar FROM store_staff s
             JOIN users u ON u.id = s.user_id WHERE s.store_id = ?",
            [$this->currentStore['id']]
        );
        $this->view('admin.store.staff', compact('staff'));
    }

    public function inviteStaff(): void {
        CSRF::validateOrFail();
        $this->requireStore();
        $data = $this->validate([
            'email' => 'required|email',
            'role'  => 'required|in:admin,manager,staff',
        ]);
        $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$data['email']]);
        if (!$user) {
            $this->error('User with this email not found. They must register first.');
            return;
        }
        if (Database::fetch("SELECT id FROM store_staff WHERE store_id = ? AND user_id = ?", [$this->currentStore['id'], $user['id']])) {
            $this->error('User is already a staff member.');
            return;
        }
        Database::insert('store_staff', [
            'store_id'   => $this->currentStore['id'],
            'user_id'    => $user['id'],
            'role'       => $data['role'],
            'invited_by' => $this->currentUser['id'],
            'status'     => 'active',
        ]);
        $this->success('Staff member added.');
    }

    public function removeStaff(int $id): void {
        $this->requireStore();
        Database::delete('store_staff', 'id = ? AND store_id = ?', [$id, $this->currentStore['id']]);
        $this->success('Staff member removed.');
    }

    private function canUseFeature(string $feature): bool {
        return !empty($this->currentStore[$feature]);
    }

    private function getAvailableThemes(): array {
        return [
            ['slug' => 'default', 'name' => 'Default',  'preview' => '/themes/default/preview.jpg', 'description' => 'Clean and modern design suitable for any store'],
            ['slug' => 'modern',  'name' => 'Modern',   'preview' => '/themes/modern/preview.jpg',  'description' => 'Bold typography with vibrant accents'],
            ['slug' => 'minimal', 'name' => 'Minimal',  'preview' => '/themes/minimal/preview.jpg', 'description' => 'Minimalist design focused on products'],
        ];
    }
}
