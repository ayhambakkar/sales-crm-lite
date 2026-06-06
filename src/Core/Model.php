<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    /** Shared PDO connection — created once, reused across all models. */
    private static ?PDO $connection = null;

    // ------------------------------------------------------------------
    // Connection
    // ------------------------------------------------------------------

    /**
     * Return the PDO singleton, creating it on first call.
     * All PDO options are set here — nowhere else in the codebase.
     */
    protected function db(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $host    = Config::get('DB_HOST', '127.0.0.1');
        $port    = Config::get('DB_PORT', '3306');
        $name    = Config::get('DB_NAME', '');
        $user    = Config::get('DB_USER', '');
        $pass    = Config::get('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        self::$connection = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,  // real prepared statements
        ]);

        return self::$connection;
    }

    // ------------------------------------------------------------------
    // Query helpers
    // ------------------------------------------------------------------

    /**
     * Prepare and execute a statement.
     * This is the single choke-point for all DB interaction — every
     * other method calls this one, so prepared statements are enforced.
     *
     * @param  array<int|string, mixed> $params
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Return all matching rows as an array of associative arrays.
     *
     * @param  array<int|string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function findAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Return the first matching row, or null if none found.
     *
     * @param  array<int|string, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function findOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Execute a write statement (INSERT / UPDATE / DELETE).
     * Returns true if at least one row was affected.
     *
     * @param  array<int|string, mixed> $params
     */
    protected function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params)->rowCount() > 0;
    }

    /**
     * Return the auto-increment ID of the last INSERT.
     */
    protected function lastInsertId(): int
    {
        return (int) $this->db()->lastInsertId();
    }
}
