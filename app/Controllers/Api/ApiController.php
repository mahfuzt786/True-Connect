<?php
namespace Api;

use Auth;
use CSRF;
use Database;
use Request;
use Response;
use Session;
use Validator;

abstract class ApiController {
    protected Request $request;
    protected Response $response;
    protected ?array $user = null;

    public function __construct() {
        $this->request  = new Request();
        $this->response = new Response();
        $this->user     = Auth::user();
    }

    protected function json(mixed $data, int $code = 200): void {
        $this->response->json($data, $code);
    }

    protected function ok(mixed $data = null, string $message = ''): void {
        $this->json(array_filter(['success' => true, 'message' => $message, 'data' => $data], fn($v) => $v !== null && $v !== ''));
    }

    protected function error(string $message, int $code = 400, array $extra = []): void {
        $this->json(array_merge(['error' => $message], $extra), $code);
    }

    protected function validate(array $rules): array {
        $v = new Validator($this->request->all(), $rules);
        if ($v->fails()) $this->error('Validation failed', 422, ['errors' => $v->errors()]);
        return $v->validated();
    }

    protected function paginated(string $sql, array $params, int $perPage = 20): array {
        $page = max(1, (int)$this->request->get('page', 1));
        return Database::paginate($sql, $params, $page, $perPage);
    }

    protected function requireUser(): void {
        if (!$this->user) $this->error('Unauthenticated', 401);
    }
}
