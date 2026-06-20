<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CustomerModel;
use PHPUnit\Framework\TestCase;

class CustomerModuleTest extends TestCase
{
    public function testCustomerStatusValidation(): void
    {
        $this->assertTrue(CustomerModel::isValidStatus('active'));
        $this->assertTrue(CustomerModel::isValidStatus('inactive'));
        $this->assertTrue(CustomerModel::isValidStatus('vip'));
        $this->assertFalse(CustomerModel::isValidStatus('prospect'));
    }

    public function testCustomerModelMethodsExist(): void
    {
        foreach (['list', 'count', 'findById', 'create', 'update', 'delete'] as $method) {
            $this->assertTrue(method_exists(CustomerModel::class, $method), $method);
        }
    }

    public function testSalesRepCustomerScopingCannotBeBypassed(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        $filters = CustomerModel::normalizeFilters(['assigned_user_id' => '99'], $salesRep);

        $this->assertSame(7, $filters['assigned_user_id']);
        $this->assertTrue(CustomerModel::canAccessCustomer($salesRep, ['assigned_user_id' => 7]));
        $this->assertFalse(CustomerModel::canAccessCustomer($salesRep, ['assigned_user_id' => 99]));
        $this->assertFalse(CustomerModel::canAccessAll($salesRep));
    }

    public function testAdminCanAccessAllCustomersRule(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        $this->assertTrue(CustomerModel::canAccessAll($admin));
        $this->assertTrue(CustomerModel::canAccessCustomer($admin, ['assigned_user_id' => 99]));
        $this->assertTrue(CustomerModel::canAccessCustomer($admin, ['assigned_user_id' => null]));
    }

    public function testFilterParameterValidation(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        $filters = CustomerModel::normalizeFilters([
            'q' => '  Acme  ',
            'customer_status' => 'not-real',
            'assigned_user_id' => '12',
        ], $admin);

        $this->assertSame('Acme', $filters['q']);
        $this->assertSame('', $filters['customer_status']);
        $this->assertSame(12, $filters['assigned_user_id']);
    }

    public function testDefaultPaginationValues(): void
    {
        $this->assertSame(
            ['page' => 1, 'per_page' => 10, 'offset' => 0],
            CustomerModel::paginationFromQuery([])
        );

        $this->assertSame(
            ['page' => 4, 'per_page' => 10, 'offset' => 30],
            CustomerModel::paginationFromQuery(['page' => '4'])
        );
    }

    public function testAllowedSortFields(): void
    {
        $this->assertTrue(CustomerModel::isAllowedSortField('created_at'));
        $this->assertTrue(CustomerModel::isAllowedSortField('company'));
        $this->assertTrue(CustomerModel::isAllowedSortField('customer_status'));
        $this->assertFalse(CustomerModel::isAllowedSortField('email'));

        $this->assertSame(
            ['sort' => 'created_at', 'direction' => 'desc'],
            CustomerModel::normalizeSort(['sort' => 'email', 'direction' => 'sideways'])
        );
    }

    public function testSearchWhereClauseUsesUniquePdoPlaceholders(): void
    {
        $model = new class extends CustomerModel {
            public function exposeBuildWhere(array $user, array $filters): array
            {
                return $this->buildWhere($user, $filters);
            }
        };

        [$whereSql, $params] = $model->exposeBuildWhere(
            ['id' => 1, 'role' => 'admin'],
            [
                'q' => 'Acme',
                'customer_status' => '',
                'assigned_user_id' => '',
            ]
        );

        $this->assertStringContainsString(':q_first_name', $whereSql);
        $this->assertStringContainsString(':q_last_name', $whereSql);
        $this->assertStringContainsString(':q_company', $whereSql);
        $this->assertStringContainsString(':q_email', $whereSql);
        $this->assertStringContainsString(':q_phone', $whereSql);
        $this->assertStringContainsString(':q_city', $whereSql);
        $this->assertArrayNotHasKey(':q', $params);
        $this->assertSame('%Acme%', $params[':q_first_name']);
        $this->assertSame('%Acme%', $params[':q_city']);
    }
}
