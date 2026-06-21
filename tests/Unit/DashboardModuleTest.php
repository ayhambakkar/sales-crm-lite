<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\DashboardModel;
use PHPUnit\Framework\TestCase;

class DashboardModuleTest extends TestCase
{
    public function testAdminDashboardScopeCanAccessGlobalStats(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        [$condition, $params] = DashboardModel::scopeCondition('l', $admin, 'scope_user_id');

        $this->assertTrue(DashboardModel::canAccessGlobal($admin));
        $this->assertSame('', $condition);
        $this->assertSame([], $params);
    }

    public function testSalesRepDashboardScopeIsUserLimited(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        [$condition, $params] = DashboardModel::scopeCondition('c', $salesRep, 'scope_user_id');

        $this->assertFalse(DashboardModel::canAccessGlobal($salesRep));
        $this->assertSame('c.assigned_user_id = :scope_user_id', $condition);
        $this->assertSame([':scope_user_id' => 7], $params);
    }

    public function testConversionRateHandlesZeroLeads(): void
    {
        $this->assertSame(0.0, DashboardModel::conversionRate(0, 0));
        $this->assertSame(0.0, DashboardModel::conversionRate(0, 4));
        $this->assertSame(25.0, DashboardModel::conversionRate(8, 2));
    }

    public function testDashboardModelMethodsExist(): void
    {
        foreach (['overview', 'stats', 'latestLeads', 'latestCustomers', 'recentConversions', 'upcomingFollowUps', 'latestActivities'] as $method) {
            $this->assertTrue(method_exists(DashboardModel::class, $method), $method);
        }
    }

    public function testKpiResultShapeContainsExpectedKeys(): void
    {
        $stats = DashboardModel::emptyStats();

        foreach (DashboardModel::statKeys() as $key) {
            $this->assertArrayHasKey($key, $stats);
        }

        $this->assertSame([
            'total_leads',
            'open_leads',
            'new_leads',
            'qualified_leads',
            'converted_leads',
            'lost_leads',
            'total_customers',
            'active_customers',
            'vip_customers',
            'estimated_open_pipeline_value',
            'conversion_rate',
            'overdue_follow_ups',
            'due_today_follow_ups',
        ], DashboardModel::statKeys());
    }

    public function testDashboardFollowUpKpiShapeIncludesOverdueAndDueToday(): void
    {
        $stats = DashboardModel::emptyStats();

        $this->assertArrayHasKey('overdue_follow_ups', $stats);
        $this->assertArrayHasKey('due_today_follow_ups', $stats);
        $this->assertSame(0, $stats['overdue_follow_ups']);
        $this->assertSame(0, $stats['due_today_follow_ups']);
    }
}
