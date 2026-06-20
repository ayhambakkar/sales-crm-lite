<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FollowUpModel;
use PHPUnit\Framework\TestCase;

class FollowUpModuleTest extends TestCase
{
    public function testStatusValidation(): void
    {
        $this->assertTrue(FollowUpModel::isValidStatus('open'));
        $this->assertTrue(FollowUpModel::isValidStatus('done'));
        $this->assertTrue(FollowUpModel::isValidStatus('cancelled'));
        $this->assertFalse(FollowUpModel::isValidStatus('pending'));
    }

    public function testPriorityValidation(): void
    {
        $this->assertTrue(FollowUpModel::isValidPriority('low'));
        $this->assertTrue(FollowUpModel::isValidPriority('medium'));
        $this->assertTrue(FollowUpModel::isValidPriority('high'));
        $this->assertFalse(FollowUpModel::isValidPriority('urgent'));
    }

    public function testExactlyOneParentValidation(): void
    {
        $this->assertTrue(FollowUpModel::hasExactlyOneParent(10, null));
        $this->assertTrue(FollowUpModel::hasExactlyOneParent(null, 20));
        $this->assertFalse(FollowUpModel::hasExactlyOneParent(null, null));
        $this->assertFalse(FollowUpModel::hasExactlyOneParent(10, 20));
    }

    public function testOverdueDetection(): void
    {
        $this->assertTrue(FollowUpModel::isOverdue([
            'status' => FollowUpModel::STATUS_OPEN,
            'due_at' => '2026-06-19 09:00:00',
        ], '2026-06-20 10:00:00'));

        $this->assertFalse(FollowUpModel::isOverdue([
            'status' => FollowUpModel::STATUS_DONE,
            'due_at' => '2026-06-19 09:00:00',
        ], '2026-06-20 10:00:00'));

        $this->assertFalse(FollowUpModel::isOverdue([
            'status' => FollowUpModel::STATUS_OPEN,
            'due_at' => '2026-06-21 09:00:00',
        ], '2026-06-20 10:00:00'));
    }

    public function testSalesRepFollowUpScopingCannotBeBypassed(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        $filters = FollowUpModel::normalizeFilters(['assigned_user_id' => '99'], $salesRep);

        $this->assertSame(7, $filters['assigned_user_id']);
        $this->assertTrue(FollowUpModel::canAccessFollowUp($salesRep, ['assigned_user_id' => 7]));
        $this->assertFalse(FollowUpModel::canAccessFollowUp($salesRep, ['assigned_user_id' => 99]));
        $this->assertFalse(FollowUpModel::canAccessAll($salesRep));
    }

    public function testAdminCanAccessAllFollowUps(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        $this->assertTrue(FollowUpModel::canAccessAll($admin));
        $this->assertTrue(FollowUpModel::canAccessFollowUp($admin, ['assigned_user_id' => 99]));
        $this->assertTrue(FollowUpModel::canAccessFollowUp($admin, ['assigned_user_id' => null]));
    }

    public function testModelMethodsExist(): void
    {
        foreach ([
            'list',
            'count',
            'findById',
            'create',
            'update',
            'delete',
            'markDone',
            'markCancelled',
            'listForLead',
            'listForCustomer',
        ] as $method) {
            $this->assertTrue(method_exists(FollowUpModel::class, $method), $method);
        }
    }

    public function testDefaultPaginationAndSortValues(): void
    {
        $this->assertSame(
            ['page' => 1, 'per_page' => 10, 'offset' => 0],
            FollowUpModel::paginationFromQuery([])
        );

        $this->assertSame(
            ['sort' => 'due_at', 'direction' => 'asc'],
            FollowUpModel::normalizeSort(['sort' => 'not-real', 'direction' => 'sideways'])
        );
    }
}
