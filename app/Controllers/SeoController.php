<?php

class SeoController extends Controller {

    public function sitemap(): void {
        header('Content-Type: application/xml; charset=utf-8');
        $base   = config('app.url');
        $stores = Database::fetchAll("SELECT slug FROM stores WHERE status = 'active'");
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        echo "<url><loc>{$base}/</loc><priority>1.0</priority></url>\n";
        echo "<url><loc>{$base}/pricing</loc><priority>0.8</priority></url>\n";
        echo "<url><loc>{$base}/features</loc><priority>0.8</priority></url>\n";
        foreach ($stores as $store) {
            echo "<url><loc>{$base}/shop/{$store['slug']}</loc><priority>0.9</priority></url>\n";
            $products = Database::fetchAll("SELECT p.slug, p.updated_at FROM products p JOIN stores s ON s.id = p.store_id WHERE s.slug = ? AND p.status = 'active'", [$store['slug']]);
            foreach ($products as $p) {
                echo "<url><loc>{$base}/shop/{$store['slug']}/products/{$p['slug']}</loc><lastmod>" . date('Y-m-d', strtotime($p['updated_at'])) . "</lastmod></url>\n";
            }
        }
        echo '</urlset>';
        exit;
    }

    public function robots(): void {
        header('Content-Type: text/plain');
        $base = config('app.url');
        echo "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nDisallow: /api\n\nSitemap: {$base}/sitemap.xml";
        exit;
    }
}
