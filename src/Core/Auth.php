<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    /** Check whether a user is currently logged in. */
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    /** Return the authenticated user's ID, or null if not logged in. */
    public static function id(): ?int
    {
        $user = self::user();
        return $user !== null ? (int) $user['id'] : null;
    }

    /**
     * Return the authenticated user's data array, or null.
     *
     * @return array{id: int, first_name: string, last_name: string, email: string, role: string}|null
     */
    public static function user(): ?array
    {
        return Session::get('user');
    }

    /**
     * Persist the authenticated user in the session.
     * Regenerates the session ID to prevent session fixation.
     * Only stores fields needed at runtime — never stores the password hash.
     */
    public static function login(array $user): void
    {
        Session::regenerate();

        Session::set('user_id', $user['id']);
        Session::set('user', [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'role'       => $user['role'],
        ]);
        Session::set('last_activity', time());
    }

    /** Destroy the session (logout). */
    public static function logout(): void
    {
        Session::destroy();
    }

    /** Check whether the authenticated user has the Admin role. */
    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'admin';
    }
}
