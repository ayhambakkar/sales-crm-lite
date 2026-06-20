<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\UserModel;
use PHPUnit\Framework\TestCase;

class UserManagementTest extends TestCase
{
    public function testRoleValidation(): void
    {
        $this->assertTrue(UserModel::isValidRole('admin'));
        $this->assertTrue(UserModel::isValidRole('sales_rep'));
        $this->assertFalse(UserModel::isValidRole('manager'));
    }

    public function testLastActiveAdminCannotBeDeactivated(): void
    {
        $admin = ['role' => 'admin', 'is_active' => 1];
        $salesRep = ['role' => 'sales_rep', 'is_active' => 1];

        $this->assertTrue(UserModel::wouldDeactivateLastActiveAdmin($admin, 1));
        $this->assertFalse(UserModel::wouldDeactivateLastActiveAdmin($admin, 2));
        $this->assertFalse(UserModel::wouldDeactivateLastActiveAdmin($salesRep, 1));
    }

    public function testLastActiveAdminCannotBeChangedToSalesRep(): void
    {
        $admin = ['role' => 'admin', 'is_active' => 1];

        $this->assertTrue(UserModel::wouldChangeLastActiveAdminToSalesRep($admin, 'sales_rep', 1));
        $this->assertFalse(UserModel::wouldChangeLastActiveAdminToSalesRep($admin, 'sales_rep', 2));
        $this->assertFalse(UserModel::wouldChangeLastActiveAdminToSalesRep($admin, 'admin', 1));
    }

    public function testPasswordHashingBehavior(): void
    {
        $hash = UserModel::hashPassword('secure-password');

        $this->assertNotSame('secure-password', $hash);
        $this->assertTrue(password_verify('secure-password', $hash));
        $this->assertTrue(str_starts_with($hash, '$2y$'));
    }

    public function testUserManagementModelMethodsExist(): void
    {
        $methods = [
            'listAll',
            'createUser',
            'updateProfile',
            'activate',
            'deactivate',
            'resetPasswordHash',
            'countActiveAdmins',
            'emailExists',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(method_exists(UserModel::class, $method), $method);
        }
    }
}
