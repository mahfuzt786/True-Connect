<?php

class HomeController extends Controller {
    public function index(): void {
        $plans  = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug <> 'custom' ORDER BY sort_order");
        $stores = Database::fetchAll("SELECT slug, name, logo FROM stores WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");
        $seo = [
            'title'       => 'Build your online store or marketplace',
            'description' => 'Launch a fully-featured e-commerce store or multi-vendor marketplace in minutes. Payments, shipping, taxes, analytics — all built in.',
            'jsonld'      => [
                '@context' => 'https://schema.org',
                '@type'    => 'Organization',
                'name'     => config('app.name'),
                'url'      => rtrim(config('app.url'), '/') . '/',
                'logo'     => url('images/logo.svg'),
                'sameAs'   => array_values(social_links()),
            ],
        ];
        $this->view('home.index', compact('plans', 'stores', 'seo'), 'public');
    }

    public function pricing(): void {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug <> 'custom' ORDER BY sort_order");
        $seo = [
            'title'       => 'Pricing',
            'description' => 'Simple, transparent pricing. Start free, upgrade as you grow. INR / USD / EUR / AED supported.',
        ];
        $this->view('home.pricing', compact('plans', 'seo'), 'public');
    }

    public function features(): void {
        $seo = ['title' => 'Features', 'description' => 'Everything you need to sell online — store builder, marketplace, payments, shipping, analytics.'];
        $this->view('home.features', compact('seo'), 'public');
    }

    public function about(): void {
        $seo = ['title' => 'About', 'description' => 'About ' . config('app.name') . ' — connecting buyers and sellers with a powerful multi-vendor marketplace platform.'];
        $this->view('home.about', compact('seo'), 'public');
    }

    public function contact(): void {
        $seo = ['title' => 'Contact', 'description' => 'Get in touch with ' . config('app.name') . '.'];
        $this->view('home.contact', compact('seo'), 'public');
    }

    public function robots(): void {
        header('Content-Type: text/plain; charset=utf-8');
        $base = rtrim(config('app.url', ''), '/');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /vendor\n";
        echo "Disallow: /dashboard\n";
        echo "Disallow: /api\n";
        echo "Disallow: /install\n";
        echo "Disallow: /login\n";
        echo "Disallow: /register\n";
        echo "Sitemap: {$base}/sitemap.xml\n";
        exit;
    }

    public function sitemap(): void {
        header('Content-Type: application/xml; charset=utf-8');
        $base = rtrim(config('app.url', ''), '/');

        $urls = [
            ['loc' => $base . '/',             'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $base . '/marketplace',  'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $base . '/marketplace/products', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $base . '/marketplace/stores',   'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => $base . '/features',     'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => $base . '/pricing',      'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $base . '/about',        'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $base . '/contact',      'priority' => '0.4', 'changefreq' => 'yearly'],
        ];
        try {
            foreach (Database::fetchAll("SELECT slug, updated_at FROM stores WHERE status = 'active' LIMIT 5000") as $s) {
                $urls[] = ['loc' => $base . '/shop/' . $s['slug'], 'lastmod' => date('Y-m-d', strtotime($s['updated_at'])), 'priority' => '0.6'];
            }
            foreach (Database::fetchAll("SELECT id, updated_at FROM products WHERE status = 'active' LIMIT 5000") as $p) {
                $urls[] = ['loc' => $base . '/marketplace/products/' . (int)$p['id'], 'lastmod' => date('Y-m-d', strtotime($p['updated_at'])), 'priority' => '0.7'];
            }
        } catch (Throwable) { /* no DB yet → skip dynamic urls */ }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo '  <url>';
            echo '<loc>' . htmlspecialchars($u['loc']) . '</loc>';
            if (!empty($u['lastmod']))    echo '<lastmod>' . $u['lastmod'] . '</lastmod>';
            if (!empty($u['changefreq'])) echo '<changefreq>' . $u['changefreq'] . '</changefreq>';
            if (!empty($u['priority']))   echo '<priority>' . $u['priority'] . '</priority>';
            echo '</url>' . "\n";
        }
        echo '</urlset>' . "\n";
        exit;
    }

    public function contactSubmit(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'name'    => 'required|min:2',
            'email'   => 'required|email',
            'subject' => 'required|min:3',
            'message' => 'required|min:10',
        ]);
        try {
            Mailer::make()
                ->to(setting('app_email', 'admin@saas.com'))
                ->subject('Contact: ' . $data['subject'])
                ->html("<p><b>From:</b> {$data['name']} ({$data['email']})</p><p>{$data['message']}</p>")
                ->send();
        } catch (Throwable) {}
        $this->success('Thank you! We will respond within 24 hours.');
    }
}
