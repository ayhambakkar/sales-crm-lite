<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\ExportController;
use App\Models\ActivityModel;
use App\Models\ExportModel;
use PHPUnit\Framework\TestCase;

class ExportModuleTest extends TestCase
{
    public function testExportControllerExists(): void
    {
        foreach (['leads', 'customers', 'followUps', 'activities', 'reportSummary'] as $method) {
            $this->assertTrue(method_exists(ExportController::class, $method), $method);
        }
    }

    public function testCsvResponseHelperExists(): void
    {
        $this->assertTrue(method_exists(ExportController::class, 'streamCsv'));
        $this->assertTrue(method_exists(ExportController::class, 'filename'));
    }

    public function testSalesRepExportScopeIsUserLimited(): void
    {
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        [$condition, $params] = ExportModel::scopeCondition('l', $salesRep, 'scope_user_id');

        $this->assertFalse(ExportModel::canAccessGlobal($salesRep));
        $this->assertSame('l.assigned_user_id = :scope_user_id', $condition);
        $this->assertSame([':scope_user_id' => 7], $params);
    }

    public function testAdminExportScopeCanAccessGlobalData(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];

        [$condition, $params] = ExportModel::scopeCondition('c', $admin, 'scope_user_id');

        $this->assertTrue(ExportModel::canAccessGlobal($admin));
        $this->assertSame('', $condition);
        $this->assertSame([], $params);
    }

    public function testExportFilenamesAreSafe(): void
    {
        $filename = ExportController::filename('../Leads Report?.csv');

        $this->assertStringEndsWith('.csv', $filename);
        $this->assertStringNotContainsString('/', $filename);
        $this->assertStringNotContainsString('..', $filename);
        $this->assertSame('export.csv', ExportController::filename(''));
    }

    public function testExportActivityActionIsValid(): void
    {
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_EXPORTED));
        $this->assertContains('exported', ActivityModel::actions());
    }

    public function testExportModelMethodsExist(): void
    {
        foreach (ExportModel::exportMethods() as $method) {
            $this->assertTrue(method_exists(ExportModel::class, $method), $method);
        }
    }
}
