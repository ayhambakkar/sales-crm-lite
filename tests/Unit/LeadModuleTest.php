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
        foreach (['list', 'findById', 'create', 'update', 'delete'] as $method) {
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
}
