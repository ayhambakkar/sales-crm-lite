<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\LeadModel;
use PHPUnit\Framework\TestCase;

class LeadModuleTest extends TestCase
{
    public function testStatusValidation(): void
    {
        $this->assertTrue(LeadModel::isValidStatus('new'));
        $this->assertTrue(LeadModel::isValidStatus('contacted'));
        $this->assertTrue(LeadModel::isValidStatus('qualified'));
        $this->assertTrue(LeadModel::isValidStatus('lost'));
        $this->assertTrue(LeadModel::isValidStatus('converted'));
        $this->assertFalse(LeadModel::isValidStatus('won'));
    }

    public function testPriorityValidation(): void
    {
        $this->assertTrue(LeadModel::isValidPriority('low'));
        $this->assertTrue(LeadModel::isValidPriority('medium'));
        $this->assertTrue(LeadModel::isValidPriority('high'));
        $this->assertFalse(LeadModel::isValidPriority('urgent'));
    }

    public function testLeadModelMethodsExist(): void
    {
        foreach (['list', 'count', 'findById', 'create', 'update', 'delete'] as $method) {
            $this->assertTrue(method_exists(LeadModel::class, $method), $method);
        }
    }

    public function testSalesRepScopingLogic(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        $this->assertTrue(LeadModel::canAccessLead($salesRep, ['assigned_user_id' => 7]));
        $this->assertFalse(LeadModel::canAccessLead($salesRep, ['assigned_user_id' => 8]));
        $this->assertFalse(LeadModel::canAccessLead($salesRep, ['assigned_user_id' => null]));
        $this->assertFalse(LeadModel::canAccessAll($salesRep));
    }

    public function testAdminCanAccessAllLeadsRule(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        $this->assertTrue(LeadModel::canAccessAll($admin));
        $this->assertTrue(LeadModel::canAccessLead($admin, ['assigned_user_id' => 99]));
        $this->assertTrue(LeadModel::canAccessLead($admin, ['assigned_user_id' => null]));
    }

    public function testAllowedSortFields(): void
    {
        $this->assertTrue(LeadModel::isAllowedSortField('created_at'));
        $this->assertTrue(LeadModel::isAllowedSortField('status'));
        $this->assertTrue(LeadModel::isAllowedSortField('priority'));
        $this->assertTrue(LeadModel::isAllowedSortField('estimated_value'));
        $this->assertFalse(LeadModel::isAllowedSortField('last_name'));

        $this->assertSame(
            ['sort' => 'created_at', 'direction' => 'desc'],
            LeadModel::normalizeSort(['sort' => 'last_name', 'direction' => 'sideways'])
        );
    }

    public function testDefaultPaginationValues(): void
    {
        $this->assertSame(
            ['page' => 1, 'per_page' => 10, 'offset' => 0],
            LeadModel::paginationFromQuery([])
        );

        $this->assertSame(
            ['page' => 3, 'per_page' => 10, 'offset' => 20],
            LeadModel::paginationFromQuery(['page' => '3'])
        );
    }

    public function testSalesRepScopeCannotBeBypassedByQueryParameters(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        $filters = LeadModel::normalizeFilters(['assigned_user_id' => '99'], $salesRep);

        $this->assertSame(7, $filters['assigned_user_id']);
    }

    public function testFilterParameterValidation(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        $filters = LeadModel::normalizeFilters([
            'q' => '  Acme  ',
            'status' => 'not-real',
            'priority' => 'high',
            'assigned_user_id' => '12',
        ], $admin);

        $this->assertSame('Acme', $filters['q']);
        $this->assertSame('', $filters['status']);
        $this->assertSame('high', $filters['priority']);
        $this->assertSame(12, $filters['assigned_user_id']);
    }

    public function testSearchWhereClauseUsesUniquePdoPlaceholders(): void
    {
        $model = new class extends LeadModel {
            public function exposeBuildWhere(array $user, array $filters): array
            {
                return $this->buildWhere($user, $filters);
            }
        };

        [$whereSql, $params] = $model->exposeBuildWhere(
            ['id' => 1, 'role' => 'admin'],
            [
                'q' => 'Acme',
                'status' => '',
                'priority' => '',
                'assigned_user_id' => '',
            ]
        );

        $this->assertStringContainsString(':q_first_name', $whereSql);
        $this->assertStringContainsString(':q_last_name', $whereSql);
        $this->assertStringContainsString(':q_company', $whereSql);
        $this->assertStringContainsString(':q_email', $whereSql);
        $this->assertStringContainsString(':q_phone', $whereSql);
        $this->assertArrayNotHasKey(':q', $params);
        $this->assertSame('%Acme%', $params[':q_first_name']);
        $this->assertSame('%Acme%', $params[':q_phone']);
    }
}
