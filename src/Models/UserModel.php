<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SALES_REP = 'sales_rep';
    public const PASSWORD_ALGO = PASSWORD_BCRYPT;
    public const PASSWORD_OPTIONS = ['cost' => 12];

    /**
     * @return array<int, string>
     */
    public static function roles(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_SALES_REP];
    }

    public static function isValidRole(string $role): bool
    {
        return in_array($role, self::roles(), true);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, self::PASSWORD_ALGO, self::PASSWORD_OPTIONS);
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function wouldDeactivateLastActiveAdmin(array $user, int $activeAdminCount): bool
    {
        return $user['role'] === self::ROLE_ADMIN
            && (int) $user['is_active'] === 1
            && $activeAdminCount <= 1;
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function wouldChangeLastActiveAdminToSalesRep(
        array $user,
        string $newRole,
        int $activeAdminCount
    ): bool {
        return $newRole === self::ROLE_SALES_REP
            && $user['role'] === self::ROLE_ADMIN
            && (int) $user['is_active'] === 1
            && $activeAdminCount <= 1;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAll(): array
    {
        return $this->findAll(
            'SELECT id, first_name, last_name, email, role, is_active, last_login_at, created_at, updated_at
             FROM   users
             ORDER  BY last_name ASC, first_name ASC, id ASC'
        );
    }

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

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = [':email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        return $this->findOne($sql, $params) !== null;
    }

    /**
     * @param array{first_name: string, last_name: string, email: string, role: string, password: string} $data
     */
    public function createUser(array $data): int
    {
        $this->query(
            'INSERT INTO users (first_name, last_name, email, password_hash, role, is_active)
             VALUES (:first_name, :last_name, :email, :password_hash, :role, 1)',
            [
                ':first_name'    => $data['first_name'],
                ':last_name'     => $data['last_name'],
                ':email'         => $data['email'],
                ':password_hash' => self::hashPassword($data['password']),
                ':role'          => $data['role'],
            ]
        );

        return $this->lastInsertId();
    }

    /**
     * @param array{first_name: string, last_name: string, email: string, role: string} $data
     */
    public function updateProfile(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE users
             SET    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    role = :role
             WHERE  id = :id',
            [
                ':id'         => $id,
                ':first_name' => $data['first_name'],
                ':last_name'  => $data['last_name'],
                ':email'      => $data['email'],
                ':role'       => $data['role'],
            ]
        );
    }

    public function updatePasswordHash(int $id, string $passwordHash): bool
    {
        return $this->execute(
            'UPDATE users
             SET    password_hash = :password_hash
             WHERE  id = :id',
            [
                ':id'            => $id,
                ':password_hash' => $passwordHash,
            ]
        );
    }

    public function resetPasswordHash(int $id, string $passwordHash): bool
    {
        return $this->updatePasswordHash($id, $passwordHash);
    }

    public function updateLastLoginAt(int $id): bool
    {
        return $this->execute(
            'UPDATE users
             SET    last_login_at = CURRENT_TIMESTAMP
             WHERE  id = :id',
            [':id' => $id]
        );
    }

    /**
     * Find a user by their primary key.
     *
     * @return array{id: int, first_name: string, last_name: string, email: string, role: string, is_active: int, last_login_at?: string, created_at?: string, updated_at?: string}|null
     */
    public function findById(int $id): ?array
    {
        return $this->findOne(
            'SELECT id, first_name, last_name, email, role, is_active, last_login_at, created_at, updated_at
             FROM   users
             WHERE  id = :id
             LIMIT  1',
            [':id' => $id]
        );
    }

    public function activate(int $id): bool
    {
        return $this->setActive($id, true);
    }

    public function deactivate(int $id): bool
    {
        return $this->setActive($id, false);
    }

    public function countActiveAdmins(): int
    {
        $row = $this->findOne(
            'SELECT COUNT(*) AS total
             FROM   users
             WHERE  role = :role
               AND  is_active = 1',
            [':role' => self::ROLE_ADMIN]
        );

        return (int) ($row['total'] ?? 0);
    }

    private function setActive(int $id, bool $active): bool
    {
        return $this->execute(
            'UPDATE users
             SET    is_active = :is_active
             WHERE  id = :id',
            [
                ':id'        => $id,
                ':is_active' => $active ? 1 : 0,
            ]
        );
    }
}
