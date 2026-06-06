<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    /**
     * Find a user by email address.
     * Used exclusively by AuthController — never exposes the password hash outside of auth.
     *
     * @return array{id: int, first_name: string, last_name: string, email: string, password_hash: string, role: string, is_active: int}|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findOne(
            'SELECT id, first_name, last_name, email, password_hash, role, is_active
             FROM   users
             WHERE  email = :email
             LIMIT  1',
            [':email' => $email]
        );
    }

    /**
     * Find a user by their primary key.
     * Returns null if the user does not exist or is inactive.
     *
     * @return array{id: int, first_name: string, last_name: string, email: string, role: string, is_active: int}|null
     */
    public function findById(int $id): ?array
    {
        return $this->findOne(
            'SELECT id, first_name, last_name, email, role, is_active
             FROM   users
             WHERE  id = :id
             LIMIT  1',
            [':id' => $id]
        );
    }
}
