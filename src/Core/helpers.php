<?php

declare(strict_types=1);

/**
 * Global helper functions — autoloaded via composer.json "files" key.
 *
 * These functions are available everywhere in the application without import.
 * Keep this file lean: only truly global, stateless utilities belong here.
 *
 * Functions will be implemented in Milestone 1.1 (Core bootstrap).
 */

if (! function_exists('e')) {
    /**
     * Escape a value for safe HTML output (XSS prevention).
     * Use on every variable echoed in a view template — no exceptions.
     *
     * @param  mixed $value
     * @return string
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('url')) {
    /**
     * Build an absolute URL from a relative path.
     *
     * @param  string $path  e.g. '/leads', '/leads/42/edit'
     * @return string
     */
    function url(string $path = '/'): string
    {
        $base = rtrim((string) (getenv('APP_URL') ?: 'http://localhost:8000'), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (! function_exists('csrf_field')) {
    /**
     * Render a hidden CSRF token input field.
     * Include in every HTML form that submits a POST request.
     *
     * @return string  HTML string — safe to echo directly
     */
    function csrf_field(): string
    {
        // TODO: integrate with Session::get('csrf_token') in Milestone 1.2
        $token = $_SESSION['csrf_token'] ?? '';
        return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
    }
}

if (! function_exists('old')) {
    /**
     * Retrieve a previously submitted form value (repopulate after validation failure).
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function old(string $key, mixed $default = ''): mixed
    {
        // TODO: integrate with Session::getFlash('old_input') in Milestone 1.2
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (! function_exists('dd')) {
    /**
     * Dump one or more values and terminate execution.
     * Development-only — remove all dd() calls before deploying to production.
     *
     * @param  mixed ...$vars
     * @return never
     */
    function dd(mixed ...$vars): never
    {
        header('Content-Type: text/plain; charset=UTF-8');
        foreach ($vars as $var) {
            var_dump($var);
            echo PHP_EOL;
        }
        exit(1);
    }
}
