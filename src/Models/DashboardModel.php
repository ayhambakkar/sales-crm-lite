<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class DashboardModel extends Model
{
    /**
     * @return array<int, string>
     */
    public static function statKeys(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public static function emptyStats(): array
    {
        return [
            'total_leads' => 0,
            'open_leads' => 0,
            'new_leads' => 0,
            'qualified_leads' => 0,
            'converted_leads' => 0,
            'lost_leads' => 0,
            'total_customers' => 0,
            'active_customers' => 0,
            'vip_customers' => 0,
            'estimated_open_pipeline_value' => 0.0,
            'conversion_rate' => 0.0,
            'overdue_follow_ups' => 0,
            'due_today_follow_ups' => 0,
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

    public static function conversionRate(int $totalLeads, int $convertedLeads): float
    {
        if ($totalLeads <= 0) {
            return 0.0;
        }

        return round(($convertedLeads / $totalLeads) * 100, 1);
    }

    /**
     * @param array<string, mixed> $user
     * @return array{
     *     stats: array<string, int|float>,
     *     latest_leads: array<int, array<string, mixed>>,
     *     latest_customers: array<int, array<string, mixed>>,
     *     recent_conversions: array<int, array<string, mixed>>,
     *     upcoming_follow_ups: array<int, array<string, mixed>>,
     *     latest_activities: array<int, array<string, mixed>>
     * }
     */
    public function overview(array $user): array
    {
        return [
            'stats' => $this->stats($user),
            'latest_leads' => $this->latestLeads($user),
            'latest_customers' => $this->latestCustomers($user),
            'recent_conversions' => $this->recentConversions($user),
            'upcoming_follow_ups' => $this->upcomingFollowUps($user),
            'latest_activities' => $this->latestActivities($user),
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, int|float>
     */
    public function stats(array $user): array
    {
        $leadStats = $this->leadStats($user);
        $customerStats = $this->customerStats($user);
        $followUpStats = $this->followUpStats($user);

        return array_merge(self::emptyStats(), $leadStats, $customerStats, $followUpStats, [
            'conversion_rate' => self::conversionRate(
                (int) $leadStats['total_leads'],
                (int) $leadStats['converted_leads']
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function latestLeads(array $user, int $limit = 5): array
    {
        [$scope, $params] = self::scopeCondition('l', $user, 'latest_leads_user_id');
        $sql = 'SELECT l.id, l.first_name, l.last_name, l.company, l.status, l.priority,
                       l.estimated_value, l.created_at,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   leads l
                LEFT   JOIN users u ON l.assigned_user_id = u.id'
            . self::whereSql([$scope])
            . ' ORDER BY l.created_at DESC, l.id DESC
                LIMIT ' . max(1, min(10, $limit));

        return $this->findAll($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function upcomingFollowUps(array $user, int $limit = 5): array
    {
        [$scope, $scopeParams] = self::scopeCondition('f', $user, 'upcoming_follow_ups_user_id');
        $params = array_merge([
            ':upcoming_status' => FollowUpModel::STATUS_OPEN,
        ], $scopeParams);

        $sql = 'SELECT f.id, f.title, f.due_at, f.priority, f.status,
                       f.lead_id, f.customer_id,
                       CONCAT(l.first_name, " ", l.last_name) AS lead_name,
                       l.company AS lead_company,
                       CONCAT(c.first_name, " ", c.last_name) AS customer_name,
                       c.company AS customer_company,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   follow_ups f
                LEFT   JOIN leads l ON f.lead_id = l.id
                LEFT   JOIN customers c ON f.customer_id = c.id
                LEFT   JOIN users u ON f.assigned_user_id = u.id'
            . self::whereSql(array_filter(['f.status = :upcoming_status', 'f.due_at >= NOW()', $scope]))
            . ' ORDER BY f.due_at ASC, f.id DESC
                LIMIT ' . max(1, min(10, $limit));

        return $this->findAll($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function latestCustomers(array $user, int $limit = 5): array
    {
        [$scope, $params] = self::scopeCondition('c', $user, 'latest_customers_user_id');
        $sql = 'SELECT c.id, c.first_name, c.last_name, c.company, c.customer_status,
                       c.city, c.created_at,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   customers c
                LEFT   JOIN users u ON c.assigned_user_id = u.id'
            . self::whereSql([$scope])
            . ' ORDER BY c.created_at DESC, c.id DESC
                LIMIT ' . max(1, min(10, $limit));

        return $this->findAll($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function recentConversions(array $user, int $limit = 5): array
    {
        [$scope, $scopeParams] = self::scopeCondition('l', $user, 'recent_conversions_user_id');
        $params = array_merge([
            ':converted_status' => LeadModel::STATUS_CONVERTED,
        ], $scopeParams);

        $sql = 'SELECT l.id, l.first_name, l.last_name, l.company, l.converted_at,
                       l.converted_customer_id,
                       CONCAT(c.first_name, " ", c.last_name) AS customer_name,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   leads l
                LEFT   JOIN customers c ON l.converted_customer_id = c.id
                LEFT   JOIN users u ON l.assigned_user_id = u.id'
            . self::whereSql(array_filter(['l.status = :converted_status', $scope]))
            . ' ORDER BY l.converted_at DESC, l.id DESC
                LIMIT ' . max(1, min(10, $limit));

        return $this->findAll($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function latestActivities(array $user, int $limit = 5): array
    {
        return (new ActivityModel())->latest($user, $limit);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, int|float>
     */
    private function leadStats(array $user): array
    {
        [$scope, $scopeParams] = self::scopeCondition('l', $user, 'lead_stats_user_id');
        $params = array_merge([
            ':status_new' => LeadModel::STATUS_NEW,
            ':status_qualified' => LeadModel::STATUS_QUALIFIED,
            ':status_converted' => LeadModel::STATUS_CONVERTED,
            ':status_lost' => LeadModel::STATUS_LOST,
            ':open_status_lost' => LeadModel::STATUS_LOST,
            ':open_status_converted' => LeadModel::STATUS_CONVERTED,
        ], $scopeParams);

        $row = $this->findOne(
            'SELECT COUNT(*) AS total_leads,
                    COALESCE(SUM(CASE WHEN l.status NOT IN (:open_status_lost, :open_status_converted) THEN 1 ELSE 0 END), 0) AS open_leads,
                    COALESCE(SUM(CASE WHEN l.status = :status_new THEN 1 ELSE 0 END), 0) AS new_leads,
                    COALESCE(SUM(CASE WHEN l.status = :status_qualified THEN 1 ELSE 0 END), 0) AS qualified_leads,
                    COALESCE(SUM(CASE WHEN l.status = :status_converted THEN 1 ELSE 0 END), 0) AS converted_leads,
                    COALESCE(SUM(CASE WHEN l.status = :status_lost THEN 1 ELSE 0 END), 0) AS lost_leads,
                    COALESCE(SUM(CASE WHEN l.status NOT IN (:open_value_lost, :open_value_converted) THEN l.estimated_value ELSE 0 END), 0) AS estimated_open_pipeline_value
             FROM   leads l'
            . self::whereSql([$scope]),
            array_merge($params, [
                ':open_value_lost' => LeadModel::STATUS_LOST,
                ':open_value_converted' => LeadModel::STATUS_CONVERTED,
            ])
        ) ?? [];

        return [
            'total_leads' => (int) ($row['total_leads'] ?? 0),
            'open_leads' => (int) ($row['open_leads'] ?? 0),
            'new_leads' => (int) ($row['new_leads'] ?? 0),
            'qualified_leads' => (int) ($row['qualified_leads'] ?? 0),
            'converted_leads' => (int) ($row['converted_leads'] ?? 0),
            'lost_leads' => (int) ($row['lost_leads'] ?? 0),
            'estimated_open_pipeline_value' => (float) ($row['estimated_open_pipeline_value'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, int>
     */
    private function customerStats(array $user): array
    {
        [$scope, $scopeParams] = self::scopeCondition('c', $user, 'customer_stats_user_id');
        $params = array_merge([
            ':status_active' => CustomerModel::STATUS_ACTIVE,
            ':status_vip' => CustomerModel::STATUS_VIP,
        ], $scopeParams);

        $row = $this->findOne(
            'SELECT COUNT(*) AS total_customers,
                    COALESCE(SUM(CASE WHEN c.customer_status = :status_active THEN 1 ELSE 0 END), 0) AS active_customers,
                    COALESCE(SUM(CASE WHEN c.customer_status = :status_vip THEN 1 ELSE 0 END), 0) AS vip_customers
             FROM   customers c'
            . self::whereSql([$scope]),
            $params
        ) ?? [];

        return [
            'total_customers' => (int) ($row['total_customers'] ?? 0),
            'active_customers' => (int) ($row['active_customers'] ?? 0),
            'vip_customers' => (int) ($row['vip_customers'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, int>
     */
    private function followUpStats(array $user): array
    {
        [$scope, $scopeParams] = self::scopeCondition('f', $user, 'follow_up_stats_user_id');
        $params = array_merge([
            ':overdue_status' => FollowUpModel::STATUS_OPEN,
            ':due_today_status' => FollowUpModel::STATUS_OPEN,
        ], $scopeParams);

        $row = $this->findOne(
            'SELECT COALESCE(SUM(CASE WHEN f.status = :overdue_status AND f.due_at < NOW() THEN 1 ELSE 0 END), 0) AS overdue_follow_ups,
                    COALESCE(SUM(CASE WHEN f.status = :due_today_status AND DATE(f.due_at) = CURRENT_DATE THEN 1 ELSE 0 END), 0) AS due_today_follow_ups
             FROM   follow_ups f'
            . self::whereSql([$scope]),
            $params
        ) ?? [];

        return [
            'overdue_follow_ups' => (int) ($row['overdue_follow_ups'] ?? 0),
            'due_today_follow_ups' => (int) ($row['due_today_follow_ups'] ?? 0),
        ];
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
