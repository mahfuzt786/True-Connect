<?php

abstract class Controller {
    protected Request $request;
    protected Response $response;
    protected ?array $currentUser = null;
    protected ?array $currentStore = null;

    public function __construct() {
        $this->request  = new Request();
        $this->response = new Response();
        $this->currentUser  = Auth::user();
        $this->currentStore = $this->resolveStore();
    }

    protected function view(string $view, array $data = [], ?string $layout = 'main'): void {
        extract($data);
        $user  = $this->currentUser;
        $store = $this->currentStore;
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View [$view] not found at $viewFile");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $statusCode = 200): void {
        $this->response->json($data, $statusCode);
    }

    protected function redirect(string $url, int $code = 302): void {
        $this->response->redirect($url, $code);
    }

    protected function back(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function validate(array $rules): array {
        $validator = new Validator($this->request->all(), $rules);
        if ($validator->fails()) {
            if ($this->request->isAjax()) {
                $this->json(['errors' => $validator->errors()], 422);
                exit;
            }
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            exit;
        }
        return $validator->validated();
    }

    protected function abort(int $code, string $message = ''): void {
        http_response_code($code);
        if ($this->request->isAjax()) {
            $this->json(['error' => $message ?: "HTTP $code"], $code);
        } else {
            $viewFile = VIEWS_PATH . "/errors/{$code}.php";
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                echo "<h1>$code - $message</h1>";
            }
        }
        exit;
    }

    protected function requireAuth(): void {
        if (!$this->currentUser) {
            if ($this->request->isAjax()) {
                $this->json(['error' => 'Unauthenticated'], 401);
                exit;
            }
            Session::flash('intended', $_SERVER['REQUEST_URI'] ?? '/');
            $this->redirect('/login');
            exit;
        }
    }

    protected function requireRole(string ...$roles): void {
        $this->requireAuth();
        if (!in_array($this->currentUser['role'], $roles)) {
            $this->abort(403, 'Forbidden');
        }
    }

    protected function requireStore(): void {
        if (!$this->currentStore) {
            $this->redirect('/store/setup');
            exit;
        }
    }

    protected function success(string $message = '', mixed $data = null): void {
        if ($this->request->isAjax()) {
            $this->json(array_filter(['success' => true, 'message' => $message, 'data' => $data]));
            exit;
        }
        if ($message) Session::flash('success', $message);
        $this->back();
        exit;
    }

    protected function error(string $message): void {
        if ($this->request->isAjax()) {
            $this->json(['error' => $message], 400);
            exit;
        }
        Session::flash('error', $message);
        $this->back();
        exit;
    }

    private function resolveStore(): ?array {
        if (!$this->currentUser) return null;

        $select = "SELECT s.*, sub.status as sub_status, sub.id as sub_id, p.name as plan_name,
                          p.products_limit, p.vendors_limit, p.storage_limit_mb, p.api_access,
                          p.marketplace_enabled, p.analytics
                   FROM stores s
                   LEFT JOIN subscriptions sub ON sub.store_id = s.id AND sub.status IN ('active','trialing')
                   LEFT JOIN plans p ON p.id = sub.plan_id";

        // Super admins never own a store directly. Their context is set
        // explicitly via /admin/stores/{id}/impersonate, which writes a
        // session key — keeps their role + menu intact.
        if (($this->currentUser['role'] ?? '') === 'super_admin') {
            $impersonateId = (int)Session::get('super_admin_active_store_id', 0);
            if ($impersonateId > 0) {
                return Database::fetch("$select WHERE s.id = ? LIMIT 1", [$impersonateId]);
            }
            return null;
        }

        return Database::fetch(
            "$select WHERE s.user_id = ? ORDER BY s.created_at DESC LIMIT 1",
            [$this->currentUser['id']]
        );
    }

    protected function paginate(string $sql, array $params, int $perPage = 20): array {
        $page = max(1, (int)($this->request->get('page', 1)));
        return Database::paginate($sql, $params, $page, $perPage);
    }

    protected function auditLog(string $action, string $modelType = '', int $modelId = 0, array $old = [], array $new = []): void {
        Database::insert('audit_logs', [
            'store_id'   => $this->currentStore['id'] ?? null,
            'user_id'    => $this->currentUser['id'] ?? null,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId ?: null,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}
