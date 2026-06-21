<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ExportModel extends Model
{
    /**
     * @param array<string, mixed> $user
     */
    public static function canAccessGlobal(array $user): bool
    {
        return ($user['role'] ?? '') === UserModel::ROLE_ADMIN;
    }

    /**
     * @param array<string, mixed> $user
     * @return array{0: string, 1: array<string, int>}
     */
    public static function scopeCondition(string $tableAlias, array $user, string $paramName): array
    {
        if (self::canAccessGlobal($user)) {
            return ['', []];
        }

        $placeholder = ':' . $paramName;

        return [
            $tableAlias . '.assigned_user_id = ' . $placeholder,
            [$placeholder => (int) ($user['id'] ?? 0)],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function exportMethods(): array
    {
        return ['leads', 'customers', 'followUps', 'activities', 'reportSummary'];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function leads(array $user): array
    {
        [$scope, $params] = self::scopeCondition('l', $user, 'export_leads_user_id');

        return $this->findAll(
            'SELECT l.id,
                    CONCAT(l.first_name, " ", l.last_name) AS name,
                    l.company,
                    l.email,
                    l.phone,
                    l.source,
                    l.status,
                    l.priority,
                    l.estimated_value,
                    CONCAT(u.first_name, " ", u.last_name) AS assigned_user,
                    l.converted_customer_id,
                    l.converted_at,
                    l.created_at
             FROM   leads l
             LEFT   JOIN users u ON l.assigned_user_id = u.id'
            . self::whereSql([$scope])
            . ' ORDER  BY l.created_at DESC, l.id DESC',
            $params
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function customers(array $user): array
    {
        [$scope, $params] = self::scopeCondition('c', $user, 'export_customers_user_id');

        return $this->findAll(
            'SELECT c.id,
                    CONCAT(c.first_name, " ", c.last_name) AS name,
                    c.company,
                    c.email,
                    c.phone,
                    c.city,
                    c.country,
                    c.customer_status AS status,
                    CONCAT(u.first_name, " ", u.last_name) AS assigned_user,
                    c.created_at
             FROM   customers c
             LEFT   JOIN users u ON c.assigned_user_id = u.id'
            . self::whereSql([$scope])
            . ' ORDER  BY c.created_at DESC, c.id DESC',
            $params
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function followUps(array $user): array
    {
        [$scope, $params] = self::scopeCondition('f', $user, 'export_follow_ups_user_id');

        return $this->findAll(
            'SELECT f.id,
                    f.title,
                    CASE WHEN f.lead_id IS NOT NULL THEN "Lead" ELSE "Customer" END AS related_type,
                    CASE
                        WHEN f.lead_id IS NOT NULL THEN CONCAT(l.first_name, " ", l.last_name)
                        ELSE CONCAT(c.first_name, " ", c.last_name)
                    END AS related_name,
                    CONCAT(u.first_name, " ", u.last_name) AS assigned_user,
                    f.status,
                    f.priority,
                    f.due_at,
                    f.completed_at,
                    f.created_at
             FROM   follow_ups f
             LEFT   JOIN leads l ON f.lead_id = l.id
             LEFT   JOIN customers c ON f.customer_id = c.id
             LEFT   JOIN users u ON f.assigned_user_id = u.id'
            . self::whereSql([$scope])
            . ' ORDER  BY f.created_at DESC, f.id DESC',
            $params
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function activities(array $user): array
    {
        [$scope, $params] = ActivityModel::scopeCondition($user);

        return $this->findAll(
            'SELECT a.id,
                    CONCAT(u.first_name, " ", u.last_name) AS user,
                    a.entity_type,
                    a.entity_id,
                    a.action,
                    a.description,
                    a.created_at
             FROM   activities a
             LEFT   JOIN users u ON a.user_id = u.id'
            . self::whereSql([$scope])
            . ' ORDER  BY a.created_at DESC, a.id DESC',
            $params
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, int|float>
     */
    public function reportSummary(array $user): array
    {
        $report = new ReportModel();
        $leadsByStatus = $report->leadsByStatus($user);
        $customersByStatus = $report->customersByStatus($user);
        $followUpsByStatus = $report->followUpsByStatus($user);

        $convertedLeads = self::countByStatus($leadsByStatus, LeadModel::STATUS_CONVERTED);
        $openFollowUps = self::countByStatus($followUpsByStatus, FollowUpModel::STATUS_OPEN);
        $completedFollowUps = self::countByStatus($followUpsByStatus, FollowUpModel::STATUS_DONE);

        return [
            'total_leads' => self::totalCount($leadsByStatus),
            'converted_leads' => $convertedLeads,
            'conversion_rate' => $report->conversionRate($user),
            'total_customers' => self::totalCount($customersByStatus),
            'open_follow_ups' => $openFollowUps,
            'completed_follow_ups' => $completedFollowUps,
            'follow_up_completion_rate' => $report->followUpCompletionRate($user),
            'open_pipeline_value' => $report->openPipelineValue($user),
        ];
    }

    /**
     * @param array<int, array{status: string, count: int}> $rows
     */
    private static function countByStatus(array $rows, string $status): int
    {
        foreach ($rows as $row) {
            if ($row['status'] === $status) {
                return (int) $row['count'];
            }
        }

        return 0;
    }

    /**
     * @param array<int, array{status: string, count: int}> $rows
     */
    private static function totalCount(array $rows): int
    {
        return array_sum(array_map(static fn (array $row): int => (int) $row['count'], $rows));
    }

    /**
     * @param array<int, string> $conditions
     */
    private static function whereSql(array $conditions): string
    {
        $conditions = array_values(array_filter($conditions));

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }
}
