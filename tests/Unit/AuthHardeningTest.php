<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FailedLoginModel;
use App\Models\UserModel;
use PHPUnit\Framework\TestCase;

class AuthHardeningTest extends TestCase
{
    public function testFailedLoginLockPolicy(): void
    {
        $this->assertFalse(FailedLoginModel::isLockThresholdReached(4));
        $this->assertTrue(FailedLoginModel::isLockThresholdReached(5));
        $this->assertSame(900, FailedLoginModel::lockWindowSeconds());
    }

    public function testUserModelSupportsLoginAuditAndPasswordLifecycle(): void
    {
        $this->assertTrue(method_exists(UserModel::class, 'updateLastLoginAt'));
        $this->assertTrue(method_exists(UserModel::class, 'updatePasswordHash'));
    }
}
