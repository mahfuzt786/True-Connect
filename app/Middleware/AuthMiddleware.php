<?php

class AuthMiddleware {
    public function handle(callable $next): void {
        if (!Auth::check()) {
            Session::flash('intended', $_SERVER['REQUEST_URI'] ?? '/');
            if ((new Request())->isAjax()) {
                (new Response())->json(['error' => 'Unauthenticated', 'redirect' => '/login'], 401);
            }
            redirect('/login');
            exit;
        }
        $next();
    }
}
