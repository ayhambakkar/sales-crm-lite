<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Session;

class AuthMiddleware
{
    /**
     * Enforce authentication on every protected route.
     *
     * Redirects to /login if:
     *   - No active session exists, or
     *   - The session has been inactive longer than SESSION_LIFETIME
     */
    public static function handle(): void
    {
        if (! Auth::check()) {
            Session::flash('error', 'Please sign in to continue.');
            self::redirectToLogin();
        }

        $lifetime     = (int) Config::get('SESSION_LIFETIME', 7200);
        $lastActivity = (int) Session::get('last_activity', 0);

        if ($lastActivity > 0 && (time() - $lastActivity) > $lifetime) {
            Auth::logout();
            Session::start();
            Session::flash('error', 'Your session has expired. Please sign in again.');
            self::redirectToLogin();
        }

        // Keep the activity timestamp current
        Session::set('last_activity', time());
    }

    private static function redirectToLogin(): never
    {
        header('Location: /login', true, 302);
        exit();
    }
}
