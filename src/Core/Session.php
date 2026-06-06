<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    /**
     * Start a session with hardened cookie settings.
     * Safe to call multiple times — exits early if already active.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = Config::get('APP_ENV') === 'production';

        session_set_cookie_params([
            'lifetime' => 0,               // until browser close
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,         // HTTPS-only in production
            'httponly' => true,            // not accessible via JavaScript
            'samesite' => 'Strict',        // no cross-site submission
        ]);

        ini_set('session.use_strict_mode',  '1');
        ini_set('session.use_only_cookies', '1');

        $name = Config::get('SESSION_NAME', 'crm_session');
        session_name($name);

        session_start();
    }

    /** Read a value from the session. */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /** Write a value to the session. */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** Check whether a key is set (even if value is null). */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /** Remove a single key from the session. */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Store a flash value — available on the very next request, then gone.
     * Used for success/error messages after redirects.
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Read and immediately remove a flash value.
     * Returns $default if the key was never set.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /** Destroy the session entirely (logout, etc.). */
    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /** Regenerate the session ID — call on privilege escalation (login). */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
