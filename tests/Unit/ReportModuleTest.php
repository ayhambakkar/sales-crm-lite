<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ReportModel;
use PHPUnit\Framework\TestCase;

class ReportModuleTest extends TestCase
{
    public function testReportModelMethodsExist(): void
    {
        foreach ([
            'overview',
            'leadsByStatus',
            'customersByStatus',
            'followUpsByStatus',
            'conversionRate',
            'openPipelineValue',
            'followUpCompletionRate',
            'leadsCreatedOverTime',
            'customersCreatedOverTime',
            'salesRepPerformance',
        ] as $method) {
            $this->assertTrue(method_exists(ReportModel::class, $method), $method);
        }
    }

    public function testConversionRateHandlesZeroLeads(): void
    {
        $this->assertSame(0.0, ReportModel::calculateConversionRate(0, 0));
        $this->assertSame(0.0, ReportModel::calculateConversionRate(0, 5));
        $this->assertSame(40.0, ReportModel::calculateConversionRate(10, 4));
    }

    public function testFollowUpCompletionRateHandlesZeroFollowUps(): void
    {
        $this->assertSame(0.0, ReportModel::calculateFollowUpCompletionRate(0, 0));
        $this->assertSame(0.0, ReportModel::calculateFollowUpCompletionRate(0, 3));
        $this->assertSame(75.0, ReportModel::calculateFollowUpCompletionRate(8, 6));
    }

    public function testAdminReportScopeCanAccessGlobalData(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        [$condition, $params] = ReportModel::scopeCondition('l', $admin, 'scope_user_id');

        $this->assertTrue(ReportModel::canAccessGlobal($admin));
        $this->assertSame('', $condition);
        $this->assertSame([], $params);
    }

    public function testSalesRepReportScopeIsUserLimited(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        [$condition, $params] = ReportModel::scopeCondition('c', $salesRep, 'scope_user_id');

        $this->assertFalse(ReportModel::canAccessGlobal($salesRep));
        $this->assertSame('c.assigned_user_id = :scope_user_id', $condition);
        $this->assertSame([':scope_user_id' => 7], $params);
    }

    public function testReportResultShapeContainsExpectedKeys(): void
    {
        $this->assertSame([
            'kpis',
            'leads_by_status',
            'customers_by_status',
            'follow_ups_by_status',
            'conversion_rate',
            'open_pipeline_value',
            'follow_up_completion_rate',
            'leads_created_over_time',
            'customers_created_over_time',
            'sales_rep_performance',
        ], ReportModel::resultKeys());

        foreach (['total_leads', 'total_customers', 'total_follow_ups', 'conversion_rate', 'open_pipeline_value', 'follow_up_completion_rate'] as $key) {
            $this->assertArrayHasKey($key, ReportModel::emptyKpis());
        }
    }
}
