<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class FailedLoginModel extends Model
{
    public const MAX_ATTEMPTS = 5;
    public const LOCK_MINUTES = 15;

    public function isLocked(string $email, string $ipAddress): bool
    {
        return self::isLockThresholdReached($this->countRecentAttempts($email, $ipAddress));
    }

    public static function isLockThresholdReached(int $attempts): bool
    {
        return $attempts >= self::MAX_ATTEMPTS;
    }

    public static function lockWindowSeconds(): int
    {
        return self::LOCK_MINUTES * 60;
    }

    public function countRecentAttempts(string $email, string $ipAddress): int
    {
        $row = $this->findOne(
            'SELECT COUNT(*) AS total
             FROM   failed_logins
             WHERE  email = :email
               AND  ip_address = :ip_address
               AND  attempted_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)',
            [
                ':email'      => $email,
                ':ip_address' => $ipAddress,
            ]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function recordFailure(string $email, string $ipAddress): bool
    {
        return $this->execute(
            'INSERT INTO failed_logins (email, ip_address)
             VALUES (:email, :ip_address)',
            [
                ':email'      => $email,
                ':ip_address' => $ipAddress,
            ]
        );
    }

    public function clearFailures(string $email, string $ipAddress): bool
    {
        return $this->execute(
            'DELETE FROM failed_logins
             WHERE email = :email
               AND ip_address = :ip_address',
            [
                ':email'      => $email,
                ':ip_address' => $ipAddress,
            ]
        );
    }
}
