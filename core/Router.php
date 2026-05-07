<?php

class Router {
    private array $routes = [];
    private array $namedRoutes = [];
    private array $middleware = [];
    private string $prefix = '';
    private array $groupMiddleware = [];

    public function get(string $path, $handler, string $name = ''): self {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    public function post(string $path, $handler, string $name = ''): self {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    public function put(string $path, $handler, string $name = ''): self {
        return $this->addRoute('PUT', $path, $handler, $name);
    }

    public function patch(string $path, $handler, string $name = ''): self {
        return $this->addRoute('PATCH', $path, $handler, $name);
    }

    public function delete(string $path, $handler, string $name = ''): self {
        return $this->addRoute('DELETE', $path, $handler, $name);
    }

    public function any(string $path, $handler, string $name = ''): self {
        foreach (['GET','POST','PUT','PATCH','DELETE'] as $method) {
            $this->addRoute($method, $path, $handler, $name);
        }
        return $this;
    }

    public function group(array $options, callable $callback): void {
        $prevPrefix     = $this->prefix;
        $prevMiddleware = $this->groupMiddleware;

        $this->prefix           .= $options['prefix'] ?? '';
        $this->groupMiddleware   = array_merge($this->groupMiddleware, $options['middleware'] ?? []);

        $callback($this);

        $this->prefix          = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    private function addRoute(string $method, string $path, $handler, string $name): self {
        $fullPath = rtrim($this->prefix . $path, '/') ?: '/';
        $pattern  = $this->compilePattern($fullPath);
        $route = [
            'method'     => $method,
            'path'       => $fullPath,
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $this->groupMiddleware,
            'name'       => $name,
        ];
        $this->routes[] = $route;
        if ($name) {
            $this->namedRoutes[$name] = $fullPath;
        }
        return $this;
    }

    private function compilePattern(string $path): string {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $path);
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\?\}/', '?([^/]*)?', $pattern);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void {
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';
        $method = strtoupper($method);

        // Support method override
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                $params = array_values(array_filter($matches, fn($v) => $v !== ''));

                // Run middleware chain
                $this->runMiddleware($route['middleware'], function() use ($route, $params) {
                    $this->callHandler($route['handler'], $params);
                });
                return;
            }
        }

        $this->handleNotFound();
    }

    private function runMiddleware(array $middleware, callable $final): void {
        $chain = array_reverse($middleware);
        $next  = $final;
        foreach ($chain as $mw) {
            $next = function() use ($mw, $next) {
                $instance = new $mw();
                $instance->handle($next);
            };
        }
        $next();
    }

    private function callHandler($handler, array $params): void {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler)) {
            [$class, $method] = explode('@', $handler);
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        throw new RuntimeException("Invalid route handler");
    }

    public function url(string $name, array $params = []): string {
        if (!isset($this->namedRoutes[$name])) {
            throw new RuntimeException("Route [$name] not defined");
        }
        $path = $this->namedRoutes[$name];
        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }
        return rtrim(config('app.url'), '/') . $path;
    }

    private function handleNotFound(): void {
        http_response_code(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            include VIEWS_PATH . '/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
        exit;
    }

    public function getRoutes(): array {
        return $this->routes;
    }
}
