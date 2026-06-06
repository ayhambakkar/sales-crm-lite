<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class CsrfMiddleware
{
    /**
     * Validate the CSRF token on POST requests.
     *
     * On GET requests this is a no-op — the token is generated lazily
     * by csrf_field() when a form is rendered.
     */
    public static function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        $submitted = $_POST['csrf_token'] ?? '';
        $expected  = Session::get('csrf_token', '');

        if ($expected === '' || ! hash_equals($expected, $submitted)) {
            http_response_code(403);
            exit('403 — Invalid security token. Please go back and try again.');
        }

        // Rotate the token after each successful POST to limit reuse window
        Session::set('csrf_token', bin2hex(random_bytes(32)));
    }
}
