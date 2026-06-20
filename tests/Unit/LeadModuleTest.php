<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Model;
use App\Models\CustomerModel;
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
        foreach (['list', 'count', 'findById', 'findByIdForUser', 'findByIdForUpdate', 'create', 'update', 'delete', 'markConverted'] as $method) {
            $this->assertTrue(method_exists(LeadModel::class, $method), $method);
        }
    }

    public function testCannotConvertAlreadyConvertedLead(): void
    {
        $this->assertFalse(LeadModel::canConvert([
            'status' => LeadModel::STATUS_CONVERTED,
            'converted_customer_id' => 10,
            'converted_at' => '2026-06-20 10:00:00',
        ]));

        $this->assertFalse(LeadModel::canConvert([
            'status' => LeadModel::STATUS_CONVERTED,
            'converted_customer_id' => null,
            'converted_at' => null,
        ]));

        $this->assertTrue(LeadModel::canConvert([
            'status' => LeadModel::STATUS_QUALIFIED,
            'converted_customer_id' => null,
            'converted_at' => null,
        ]));
    }

    public function testSalesRepCannotConvertUnassignedLead(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];
        $lead = [
            'assigned_user_id' => 99,
            'status' => LeadModel::STATUS_QUALIFIED,
            'converted_customer_id' => null,
            'converted_at' => null,
        ];

        $this->assertFalse(LeadModel::canAccessLead($salesRep, $lead));
        $this->assertTrue(LeadModel::canConvert($lead));
    }

    public function testAdminCanConvertAnyLead(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];
        $lead = [
            'assigned_user_id' => 99,
            'status' => LeadModel::STATUS_QUALIFIED,
            'converted_customer_id' => null,
            'converted_at' => null,
        ];

        $this->assertTrue(LeadModel::canAccessLead($admin, $lead));
        $this->assertTrue(LeadModel::canConvert($lead));
    }

    public function testConversionMappingContainsExpectedFields(): void
    {
        $mapped = CustomerModel::dataFromLead([
            'assigned_user_id' => 7,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'company' => 'Analytical Engines LLC',
            'email' => 'ada@example.com',
            'phone' => '555-0100',
            'notes' => 'Interested in the premium plan.',
        ]);

        $this->assertSame(7, $mapped['assigned_user_id']);
        $this->assertSame('Ada', $mapped['first_name']);
        $this->assertSame('Lovelace', $mapped['last_name']);
        $this->assertSame('Analytical Engines LLC', $mapped['company']);
        $this->assertSame('ada@example.com', $mapped['email']);
        $this->assertSame('555-0100', $mapped['phone']);
        $this->assertSame('Interested in the premium plan.', $mapped['notes']);
        $this->assertSame(CustomerModel::STATUS_ACTIVE, $mapped['customer_status']);
        $this->assertNull($mapped['address']);
        $this->assertNull($mapped['city']);
    }

    public function testConversionUsesTransactionCapableModelMethods(): void
    {
        $model = new class extends Model {
        };

        foreach (['beginTransaction', 'commit', 'rollBack', 'inTransaction'] as $method) {
            $this->assertTrue(method_exists($model, $method), $method);
        }
    }

    public function testConvertedLeadStatusIsValidated(): void
    {
        $this->assertTrue(LeadModel::isValidStatus(LeadModel::STATUS_CONVERTED));
        $this->assertTrue(LeadModel::isConverted(['status' => LeadModel::STATUS_CONVERTED]));
        $this->assertFalse(LeadModel::canConvert(['status' => LeadModel::STATUS_CONVERTED]));
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
