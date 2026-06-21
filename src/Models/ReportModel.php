<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ReportModel extends Model
{
    /**
     * @return array<int, string>
     */
    public static function resultKeys(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public static function emptyKpis(): array
    {
        return [
            'total_leads' => 0,
            'total_customers' => 0,
            'total_follow_ups' => 0,
            'conversion_rate' => 0.0,
            'open_pipeline_value' => 0.0,
            'follow_up_completion_rate' => 0.0,
        ];
    }

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

    public static function calculateConversionRate(int $totalLeads, int $convertedLeads): float
    {
        if ($totalLeads <= 0) {
            return 0.0;
        }

        return round(($convertedLeads / $totalLeads) * 100, 1);
    }

    public static function calculateFollowUpCompletionRate(int $totalFollowUps, int $completedFollowUps): float
    {
        if ($totalFollowUps <= 0) {
            return 0.0;
        }

        return round(($completedFollowUps / $totalFollowUps) * 100, 1);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    public function overview(array $user): array
    {
        $leadsByStatus = $this->leadsByStatus($user);
        $customersByStatus = $this->customersByStatus($user);
        $followUpsByStatus = $this->followUpsByStatus($user);
        $conversionRate = $this->conversionRate($user);
        $openPipelineValue = $this->openPipelineValue($user);
        $followUpCompletionRate = $this->followUpCompletionRate($user);

        return [
            'kpis' => array_merge(self::emptyKpis(), [
                'total_leads' => self::totalCount($leadsByStatus),
                'total_customers' => self::totalCount($customersByStatus),
                'total_follow_ups' => self::totalCount($followUpsByStatus),
                'conversion_rate' => $conversionRate,
                'open_pipeline_value' => $openPipelineValue,
                'follow_up_completion_rate' => $followUpCompletionRate,
            ]),
            'leads_by_status' => $leadsByStatus,
            'customers_by_status' => $customersByStatus,
            'follow_ups_by_status' => $followUpsByStatus,
            'conversion_rate' => $conversionRate,
            'open_pipeline_value' => $openPipelineValue,
            'follow_up_completion_rate' => $followUpCompletionRate,
            'leads_created_over_time' => $this->leadsCreatedOverTime($user),
            'customers_created_over_time' => $this->customersCreatedOverTime($user),
            'sales_rep_performance' => $this->salesRepPerformance($user),
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array{status: string, count: int}>
     */
    public function leadsByStatus(array $user): array
    {
        return $this->statusCounts(
            'leads',
            'l',
            'status',
            LeadModel::statuses(),
            $user,
            'leads_by_status_user_id'
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array{status: string, count: int}>
     */
    public function customersByStatus(array $user): array
    {
        return $this->statusCounts(
            'customers',
            'c',
            'customer_status',
            CustomerModel::statuses(),
            $user,
            'customers_by_status_user_id'
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array{status: string, count: int}>
     */
    public function followUpsByStatus(array $user): array
    {
        return $this->statusCounts(
            'follow_ups',
            'f',
            'status',
            FollowUpModel::statuses(),
            $user,
            'follow_ups_by_status_user_id'
        );
    }

    /**
     * @param array<string, mixed> $user
     */
    public function conversionRate(array $user): float
    {
        [$scope, $params] = self::scopeCondition('l', $user, 'conversion_rate_user_id');
        $params = array_merge([
            ':converted_status' => LeadModel::STATUS_CONVERTED,
        ], $params);

        $row = $this->findOne(
            'SELECT COUNT(*) AS total_leads,
                    COALESCE(SUM(CASE WHEN l.status = :converted_status THEN 1 ELSE 0 END), 0) AS converted_leads
             FROM   leads l'
            . self::whereSql([$scope]),
            $params
        ) ?? [];

        return self::calculateConversionRate(
            (int) ($row['total_leads'] ?? 0),
            (int) ($row['converted_leads'] ?? 0)
        );
    }

    /**
     * @param array<string, mixed> $user
     */
    public function openPipelineValue(array $user): float
    {
        [$scope, $scopeParams] = self::scopeCondition('l', $user, 'open_pipeline_user_id');
        $params = array_merge([
            ':lost_status' => LeadModel::STATUS_LOST,
            ':converted_status' => LeadModel::STATUS_CONVERTED,
        ], $scopeParams);

        $row = $this->findOne(
            'SELECT COALESCE(SUM(l.estimated_value), 0) AS total
             FROM   leads l'
            . self::whereSql(array_filter([
                'l.status NOT IN (:lost_status, :converted_status)',
                $scope,
            ])),
            $params
        ) ?? [];

        return (float) ($row['total'] ?? 0);
    }

    /**
     * @param array<string, mixed> $user
     */
    public function followUpCompletionRate(array $user): float
    {
        [$scope, $params] = self::scopeCondition('f', $user, 'follow_up_completion_user_id');
        $params = array_merge([
            ':done_status' => FollowUpModel::STATUS_DONE,
        ], $params);

        $row = $this->findOne(
            'SELECT COUNT(*) AS total_follow_ups,
                    COALESCE(SUM(CASE WHEN f.status = :done_status THEN 1 ELSE 0 END), 0) AS completed_follow_ups
             FROM   follow_ups f'
            . self::whereSql([$scope]),
            $params
        ) ?? [];

        return self::calculateFollowUpCompletionRate(
            (int) ($row['total_follow_ups'] ?? 0),
            (int) ($row['completed_follow_ups'] ?? 0)
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array{period: string, count: int}>
     */
    public function leadsCreatedOverTime(array $user, int $days = 30): array
    {
        return $this->createdOverTime('leads', 'l', $user, 'leads_created_time_user_id', $days);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array{period: string, count: int}>
     */
    public function customersCreatedOverTime(array $user, int $days = 30): array
    {
        return $this->createdOverTime('customers', 'c', $user, 'customers_created_time_user_id', $days);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function salesRepPerformance(array $user): array
    {
        if (! self::canAccessGlobal($user)) {
            return [];
        }

        return $this->findAll(
            'SELECT u.id,
                    CONCAT(u.first_name, " ", u.last_name) AS name,
                    u.email,
                    COALESCE(l.total_leads, 0) AS total_leads,
                    COALESCE(l.converted_leads, 0) AS converted_leads,
                    COALESCE(l.open_pipeline_value, 0) AS open_pipeline_value,
                    COALESCE(c.total_customers, 0) AS total_customers,
                    COALESCE(f.total_follow_ups, 0) AS total_follow_ups,
                    COALESCE(f.completed_follow_ups, 0) AS completed_follow_ups
             FROM   users u
             LEFT   JOIN (
                    SELECT assigned_user_id,
                           COUNT(*) AS total_leads,
                           SUM(CASE WHEN status = :lead_converted_status THEN 1 ELSE 0 END) AS converted_leads,
                           SUM(CASE WHEN status NOT IN (:lead_lost_status, :lead_converted_value_status)
                                    THEN COALESCE(estimated_value, 0)
                                    ELSE 0
                               END) AS open_pipeline_value
                    FROM   leads
                    WHERE  assigned_user_id IS NOT NULL
                    GROUP  BY assigned_user_id
             ) l ON l.assigned_user_id = u.id
             LEFT   JOIN (
                    SELECT assigned_user_id,
                           COUNT(*) AS total_customers
                    FROM   customers
                    WHERE  assigned_user_id IS NOT NULL
                    GROUP  BY assigned_user_id
             ) c ON c.assigned_user_id = u.id
             LEFT   JOIN (
                    SELECT assigned_user_id,
                           COUNT(*) AS total_follow_ups,
                           SUM(CASE WHEN status = :follow_up_done_status THEN 1 ELSE 0 END) AS completed_follow_ups
                    FROM   follow_ups
                    WHERE  assigned_user_id IS NOT NULL
                    GROUP  BY assigned_user_id
             ) f ON f.assigned_user_id = u.id
             WHERE  u.role = :sales_rep_role
               AND  u.is_active = 1
             ORDER  BY total_leads DESC, total_customers DESC, u.last_name ASC, u.first_name ASC',
            [
                ':lead_converted_status' => LeadModel::STATUS_CONVERTED,
                ':lead_lost_status' => LeadModel::STATUS_LOST,
                ':lead_converted_value_status' => LeadModel::STATUS_CONVERTED,
                ':follow_up_done_status' => FollowUpModel::STATUS_DONE,
                ':sales_rep_role' => UserModel::ROLE_SALES_REP,
            ]
        );
    }

    /**
     * @param array<int, array{status: string, count: int}> $rows
     */
    private static function totalCount(array $rows): int
    {
        return array_sum(array_map(static fn (array $row): int => (int) $row['count'], $rows));
    }

    /**
     * @param array<int, string> $statuses
     * @param array<string, mixed> $user
     * @return array<int, array{status: string, count: int}>
     */
    private function statusCounts(
        string $table,
        string $alias,
        string $statusColumn,
        array $statuses,
        array $user,
        string $paramName
    ): array {
        [$scope, $params] = self::scopeCondition($alias, $user, $paramName);
        $rows = $this->findAll(
            'SELECT ' . $alias . '.' . $statusColumn . ' AS status,
                    COUNT(*) AS count
             FROM   ' . $table . ' ' . $alias
            . self::whereSql([$scope])
            . ' GROUP  BY ' . $alias . '.' . $statusColumn,
            $params
        );

        $counts = array_fill_keys($statuses, 0);

        foreach ($rows as $row) {
            if (array_key_exists((string) $row['status'], $counts)) {
                $counts[(string) $row['status']] = (int) $row['count'];
            }
        }

        return array_map(
            static fn (string $status): array => ['status' => $status, 'count' => $counts[$status]],
            $statuses
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array{period: string, count: int}>
     */
    private function createdOverTime(
        string $table,
        string $alias,
        array $user,
        string $paramName,
        int $days
    ): array {
        [$scope, $scopeParams] = self::scopeCondition($alias, $user, $paramName);
        $days = max(1, min(365, $days));
        $params = array_merge([
            ':start_date' => date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days')),
        ], $scopeParams);

        $rows = $this->findAll(
            'SELECT DATE(' . $alias . '.created_at) AS period,
                    COUNT(*) AS count
             FROM   ' . $table . ' ' . $alias
            . self::whereSql(array_filter([
                $alias . '.created_at >= :start_date',
                $scope,
            ]))
            . ' GROUP  BY DATE(' . $alias . '.created_at)
                ORDER  BY period ASC',
            $params
        );

        return array_map(
            static fn (array $row): array => [
                'period' => (string) $row['period'],
                'count' => (int) $row['count'],
            ],
            $rows
        );
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
