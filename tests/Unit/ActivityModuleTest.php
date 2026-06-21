<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ActivityModel;
use PHPUnit\Framework\TestCase;

class ActivityModuleTest extends TestCase
{
    public function testActivityModelMethodsExist(): void
    {
        foreach (['log', 'list', 'count', 'latest', 'forEntity', 'forUser', 'findById'] as $method) {
            $this->assertTrue(method_exists(ActivityModel::class, $method), $method);
        }
    }

    public function testValidEntityTypeValidation(): void
    {
        $this->assertTrue(ActivityModel::isValidEntityType(ActivityModel::ENTITY_USER));
        $this->assertTrue(ActivityModel::isValidEntityType(ActivityModel::ENTITY_LEAD));
        $this->assertTrue(ActivityModel::isValidEntityType(ActivityModel::ENTITY_CUSTOMER));
        $this->assertTrue(ActivityModel::isValidEntityType(ActivityModel::ENTITY_FOLLOW_UP));
        $this->assertTrue(ActivityModel::isValidEntityType(ActivityModel::ENTITY_AUTH));
        $this->assertTrue(ActivityModel::isValidEntityType(ActivityModel::ENTITY_SYSTEM));
        $this->assertFalse(ActivityModel::isValidEntityType('invoice'));
    }

    public function testValidActionValidation(): void
    {
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_CREATED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_UPDATED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_DELETED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_CONVERTED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_COMPLETED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_CANCELLED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_LOGGED_IN));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_LOGGED_OUT));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_PASSWORD_RESET));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_DEACTIVATED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_ACTIVATED));
        $this->assertTrue(ActivityModel::isValidAction(ActivityModel::ACTION_VIEWED_REPORT));
        $this->assertFalse(ActivityModel::isValidAction('exported'));
    }

    public function testMetadataDoesNotRequireSensitiveFields(): void
    {
        $metadata = ActivityModel::sanitizeMetadata([
            'email' => 'admin@example.com',
            'password' => 'secret',
            'password_hash' => 'hash',
            'nested' => [
                'role' => 'admin',
                'new_password' => 'another-secret',
            ],
        ]);

        $this->assertSame('admin@example.com', $metadata['email']);
        $this->assertSame('admin', $metadata['nested']['role']);
        $this->assertArrayNotHasKey('password', $metadata);
        $this->assertArrayNotHasKey('password_hash', $metadata);
        $this->assertArrayNotHasKey('new_password', $metadata['nested']);
    }

    public function testAdminActivityScopeCanAccessAllActivities(): void
    {
        [$condition, $params] = ActivityModel::scopeCondition(['id' => 1, 'role' => 'admin']);

        $this->assertTrue(ActivityModel::canAccessAll(['id' => 1, 'role' => 'admin']));
        $this->assertSame('', $condition);
        $this->assertSame([], $params);
    }

    public function testSalesRepOwnActivityScope(): void
    {
        [$condition, $params] = ActivityModel::scopeCondition(['id' => 7, 'role' => 'sales_rep']);

        $this->assertFalse(ActivityModel::canAccessAll(['id' => 7, 'role' => 'sales_rep']));
        $this->assertStringContainsString('a.user_id = :activity_scope_user_id', $condition);
        $this->assertStringContainsString('activity_scope_l.assigned_user_id = :activity_scope_lead_user_id', $condition);
        $this->assertStringContainsString('activity_scope_c.assigned_user_id = :activity_scope_customer_user_id', $condition);
        $this->assertStringContainsString('activity_scope_f.assigned_user_id = :activity_scope_follow_up_user_id', $condition);
        $this->assertSame(7, $params[':activity_scope_user_id']);
        $this->assertSame(7, $params[':activity_scope_user_entity_id']);
        $this->assertSame(7, $params[':activity_scope_lead_user_id']);
        $this->assertSame(7, $params[':activity_scope_customer_user_id']);
        $this->assertSame(7, $params[':activity_scope_follow_up_user_id']);
    }

    public function testActivityResultShapeContainsExpectedKeys(): void
    {
        $this->assertSame([
            'id',
            'user_id',
            'entity_type',
            'entity_id',
            'action',
            'description',
            'metadata',
            'created_at',
            'actor_name',
            'actor_email',
        ], ActivityModel::resultKeys());
    }

    public function testFilterNormalizationAndDefaultPagination(): void
    {
        $admin = ['id' => 1, 'role' => 'admin'];
        $salesRep = ['id' => 7, 'role' => 'sales_rep'];

        $filters = ActivityModel::normalizeFilters([
            'entity_type' => 'lead',
            'action' => 'converted',
            'user_id' => '9',
            'date_from' => '2026-06-01',
            'date_to' => 'not-a-date',
        ], $admin);

        $this->assertSame('lead', $filters['entity_type']);
        $this->assertSame('converted', $filters['action']);
        $this->assertSame(9, $filters['user_id']);
        $this->assertSame('2026-06-01', $filters['date_from']);
        $this->assertSame('', $filters['date_to']);
        $this->assertSame('', ActivityModel::normalizeFilters(['user_id' => '99'], $salesRep)['user_id']);
        $this->assertSame(['page' => 1, 'per_page' => 20, 'offset' => 0], ActivityModel::paginationFromQuery([]));
    }

    public function testSalesRepForUserFilterDoesNotBroadenScope(): void
    {
        $model = new class extends ActivityModel {
            public function exposeBuildWhere(array $user, array $filters): array
            {
                return $this->buildWhere($user, $filters);
            }
        };

        [$whereSql, $params] = $model->exposeBuildWhere(
            ['id' => 7, 'role' => 'sales_rep'],
            ['user_id' => 99]
        );

        $this->assertStringContainsString('1 = 0', $whereSql);
        $this->assertArrayNotHasKey(':filter_user_id', $params);
    }
}
