<?php

class ThemeController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
    }

    public function index(): void {
        $themes = $this->getAvailableThemes();
        $current = $this->currentStore['theme'];
        $settings = json_decode($this->currentStore['theme_settings'] ?? '{}', true);
        $this->view('admin.theme.index', compact('themes','current','settings'));
    }

    public function switch(): void {
        CSRF::validateOrFail();
        $theme = $this->request->post('theme', 'default');
        Database::update('stores', ['theme' => $theme], 'id = ?', [$this->currentStore['id']]);
        $this->success('Theme switched to ' . ucfirst($theme));
    }

    public function customize(): void {
        CSRF::validateOrFail();
        $settings = json_decode($this->currentStore['theme_settings'] ?? '{}', true);
        $newSettings = array_merge($settings, [
            'primary_color'    => $this->request->post('primary_color', '#0d6efd'),
            'secondary_color'  => $this->request->post('secondary_color', '#6c757d'),
            'accent_color'     => $this->request->post('accent_color', '#ff6b6b'),
            'font_family'      => $this->request->post('font_family', 'Inter'),
            'header_layout'    => $this->request->post('header_layout', 'default'),
            'footer_layout'    => $this->request->post('footer_layout', 'default'),
            'show_announcement'=> $this->request->boolean('show_announcement', false),
            'announcement_text'=> $this->request->post('announcement_text', ''),
        ]);
        Database::update('stores', ['theme_settings' => json_encode($newSettings)], 'id = ?', [$this->currentStore['id']]);
        $this->success('Theme customized.');
    }

    public function preview(string $theme): void {
        Session::set('preview_theme', $theme);
        redirect("/shop/{$this->currentStore['slug']}");
    }

    private function getAvailableThemes(): array {
        return [
            ['slug'=>'default','name'=>'Default','preview'=>'/themes/default/preview.jpg'],
            ['slug'=>'modern','name'=>'Modern','preview'=>'/themes/modern/preview.jpg'],
            ['slug'=>'minimal','name'=>'Minimal','preview'=>'/themes/minimal/preview.jpg'],
        ];
    }
}
