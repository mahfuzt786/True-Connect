<?php
namespace Api;

use Database;

class StoreApiController extends ApiController {
    public function show(string $slug): void {
        $store = Database::fetch("SELECT * FROM stores WHERE (slug = ? OR subdomain = ?) AND status = 'active'", [$slug, $slug]);
        if (!$store) $this->error('Store not found', 404);
        unset($store['theme_settings']);
        $this->ok($store);
    }
    public function config(string $slug): void {
        $store = Database::fetch("SELECT id, name, currency, currency_symbol, language, timezone, theme, theme_settings FROM stores WHERE (slug = ? OR subdomain = ?) AND status = 'active'", [$slug, $slug]);
        if (!$store) $this->error('Store not found', 404);
        $store['theme_settings'] = json_decode($store['theme_settings'] ?? '{}', true);
        $this->ok($store);
    }
}
