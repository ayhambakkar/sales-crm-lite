<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<int, array{method: string, path: string, controller: string, action: string, middleware: string[]}> */
    private array $routes = [];

    // ------------------------------------------------------------------
    // Route registration
    // ------------------------------------------------------------------

    public function get(string $path, string $controller, string $action, array $middleware = []): void
    {
        $this->add('GET', $path, $controller, $action, $middleware);
    }

    public function post(string $path, string $controller, string $action, array $middleware = []): void
    {
        $this->add('POST', $path, $controller, $action, $middleware);
    }

    private function add(
        string $method,
        string $path,
        string $controller,
        string $action,
        array $middleware
    ): void {
        $this->routes[] = compact('method', 'path', 'controller', 'action', 'middleware');
    }

    // ------------------------------------------------------------------
    // Dispatch
    // ------------------------------------------------------------------

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = $this->currentUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = [];

            if (! $this->match($route['path'], $uri, $params)) {
                continue;
            }

            $this->handle($route, $params);
            return;
        }

        $this->notFound();
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    /**
     * Resolve the current request URI, stripped of query string and
     * normalised so the root is always '/'.
     */
    private function currentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rawurldecode((string) $uri);
        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Match a route path pattern against the actual URI.
     * Converts {param} placeholders to named regex capture groups.
     *
     * @param  array<int, string> $params  Populated with captured values on match.
     */
    private function match(string $routePath, string $uri, array &$params): bool
    {
        // Convert {id}, {slug}, etc. to capture groups
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (! preg_match($pattern, $uri, $matches)) {
            return false;
        }

        // $matches[0] is the full string — drop it
        $params = array_slice($matches, 1);

        return true;
    }

    /**
     * Run middleware stack for a matched route, then dispatch to the controller.
     *
     * @param  array<string, mixed> $route
     * @param  array<int, string>   $params
     */
    private function handle(array $route, array $params): void
    {
        foreach ($route['middleware'] as $tag) {
            $this->runMiddleware($tag);
        }

        $class = 'App\\Controllers\\' . $route['controller'];

        if (! class_exists($class)) {
            $this->serverError("Controller not found: {$class}");
            return;
        }

        $controller = new $class();
        $action     = $route['action'];

        if (! method_exists($controller, $action)) {
            $this->serverError("Action not found: {$class}::{$action}");
            return;
        }

        $controller->$action(...$params);
    }

    /**
     * Map a middleware tag to its handler class and invoke it.
     * Unknown tags are silently ignored to avoid breaking valid routes.
     */
    private function runMiddleware(string $tag): void
    {
        match ($tag) {
            'auth'  => \App\Middleware\AuthMiddleware::handle(),
            'csrf'  => \App\Middleware\CsrfMiddleware::handle(),
            'admin' => $this->requireAdmin(),
            default => null,
        };
    }

    private function requireAdmin(): void
    {
        if (! \App\Core\Auth::isAdmin()) {
            http_response_code(403);
            $view = APP_ROOT . '/src/Views/errors/403.php';
            file_exists($view) ? include $view : print('403 — Forbidden');
            exit();
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        $view = APP_ROOT . '/src/Views/errors/404.php';
        file_exists($view) ? include $view : print('404 — Page Not Found');
    }

    private function serverError(string $message): void
    {
        http_response_code(500);

        if (Config::get('APP_ENV') === 'development') {
            echo '<pre>500 — Internal Server Error' . PHP_EOL . htmlspecialchars($message) . '</pre>';
        } else {
            $view = APP_ROOT . '/src/Views/errors/500.php';
            file_exists($view) ? include $view : print('500 — Internal Server Error');
        }
    }
}
