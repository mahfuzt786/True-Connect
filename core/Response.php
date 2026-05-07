<?php

class Response {
    public function json(mixed $data, int $statusCode = 200): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function redirect(string $url, int $code = 302): void {
        // Auto-prefix base path for root-relative URLs so app works in subdirectories
        if (defined('BASE_PATH') && BASE_PATH !== '' && str_starts_with($url, '/')
            && !str_starts_with($url, '//') && !str_starts_with($url, BASE_PATH . '/')
            && $url !== BASE_PATH) {
            $url = BASE_PATH . $url;
        }
        if (!headers_sent()) {
            http_response_code($code);
            header("Location: $url");
        }
        exit;
    }

    public function download(string $filePath, string $fileName = ''): void {
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit;
        }
        $fileName = $fileName ?: basename($filePath);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        readfile($filePath);
        exit;
    }

    public function stream(string $content, string $contentType = 'text/plain', string $fileName = ''): void {
        header('Content-Type: ' . $contentType);
        if ($fileName) {
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
        }
        echo $content;
        exit;
    }

    public function noContent(): void {
        http_response_code(204);
        exit;
    }
}
