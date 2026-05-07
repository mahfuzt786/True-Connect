<?php

class ThemeService {
    private string $theme;
    private string $themePath;

    public function __construct(string $theme = 'default') {
        $available = ['default', 'modern', 'minimal'];
        $this->theme = in_array($theme, $available) ? $theme : 'default';
        $this->themePath = THEMES_PATH . '/' . $this->theme;
    }

    public function render(string $page, array $data = []): void {
        $pageFile = $this->themePath . '/pages/' . $page . '.php';
        if (!file_exists($pageFile)) {
            // Fallback to default theme
            $pageFile = THEMES_PATH . '/default/pages/' . $page . '.php';
            if (!file_exists($pageFile)) {
                http_response_code(404);
                echo "Theme page '$page' not found";
                exit;
            }
        }
        $themeUrl = '/themes/' . $this->theme;
        extract($data);
        ob_start();
        include $pageFile;
        $content = ob_get_clean();

        $layoutFile = $this->themePath . '/layout/main.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public function partial(string $name, array $data = []): void {
        $file = $this->themePath . '/partials/' . $name . '.php';
        if (!file_exists($file)) $file = THEMES_PATH . '/default/partials/' . $name . '.php';
        if (file_exists($file)) {
            extract($data);
            include $file;
        }
    }

    public function getThemePath(): string { return $this->themePath; }
    public function getTheme(): string { return $this->theme; }
    public function getUrl(): string { return '/themes/' . $this->theme; }
}
