<?php

/**
 * Granular activity logger — captures page visits, login events, and any
 * arbitrary action against a user / store with metadata.
 *
 * Distinct from audit_logs (which is for state-change diffs). Use this
 * for "every movement" — high-volume timeline data.
 */
class ActivityLogger {

    /** Log an arbitrary activity. */
    public static function log(
        string $action,
        ?string $description = null,
        ?int $userId = null,
        ?int $storeId = null,
        ?array $metadata = null
    ): void {
        // Best-effort: never let logging crash the request.
        try {
            $req = new Request();
            Database::insert('activity_logs', [
                'user_id'     => $userId   ?? Auth::id(),
                'store_id'    => $storeId,
                'action'      => $action,
                'description' => $description ? mb_substr($description, 0, 500) : null,
                'method'      => $_SERVER['REQUEST_METHOD'] ?? null,
                'path'        => isset($_SERVER['REQUEST_URI']) ? mb_substr($_SERVER['REQUEST_URI'], 0, 500) : null,
                'ip_address'  => $req->ip(),
                'user_agent'  => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                'metadata'    => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ]);
        } catch (Throwable $e) {
            logError('ActivityLogger failed: ' . $e->getMessage());
        }
    }

    /** Log a page visit (called from middleware). */
    public static function logPageView(): void {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Don't log every static asset, AJAX poll, or login redirect noise.
        if (self::shouldSkip($path, $method)) return;

        self::log('page.view');
    }

    private static function shouldSkip(string $path, string $method): bool {
        // Skip static-asset extensions
        if (preg_match('#\.(css|js|map|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|otf|eot)(\?|$)#i', $path)) return true;

        // Strip the BASE_PATH prefix when comparing routes.
        $route = $path;
        if (defined('BASE_PATH') && BASE_PATH !== '' && str_starts_with($route, BASE_PATH)) {
            $route = substr($route, strlen(BASE_PATH));
        }
        $route = strtok($route, '?'); // drop query string

        // Don't log views of the activity log itself — would fill the log
        // with rows describing the act of viewing the log.
        if (preg_match('#^/admin/(users|stores)/\d+/activity$#', $route)) return true;

        // Skip GET-only noise
        if ($method !== 'GET') return false;
        // Skip API endpoints — they have their own logging needs
        if (str_starts_with($path, '/api/')) return true;
        return false;
    }
}
