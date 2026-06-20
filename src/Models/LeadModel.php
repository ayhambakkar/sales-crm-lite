<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LeadModel extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_LOST = 'lost';
    public const STATUS_CONVERTED = 'converted';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public const DEFAULT_PER_PAGE = 10;

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_QUALIFIED,
            self::STATUS_LOST,
            self::STATUS_CONVERTED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function priorities(): array
    {
        return [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH];
    }

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::statuses(), true);
    }

    public static function isValidPriority(string $priority): bool
    {
        return in_array($priority, self::priorities(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function allowedSortFields(): array
    {
        return ['created_at', 'status', 'priority', 'estimated_value'];
    }

    public static function isAllowedSortField(string $field): bool
    {
        return in_array($field, self::allowedSortFields(), true);
    }

    /**
     * @param array<string, mixed> $query
     * @return array{sort: string, direction: string}
     */
    public static function normalizeSort(array $query): array
    {
        $sort = (string) ($query['sort'] ?? 'created_at');
        $direction = strtolower((string) ($query['direction'] ?? 'desc'));

        if (! self::isAllowedSortField($sort)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        return [
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $user
     * @return array{q: string, status: string, priority: string, assigned_user_id: int|string}
     */
    public static function normalizeFilters(array $query, array $user): array
    {
        $status = (string) ($query['status'] ?? '');
        $priority = (string) ($query['priority'] ?? '');
        $assignedUserId = '';

        if (! self::canAccessAll($user)) {
            $assignedUserId = (int) ($user['id'] ?? 0);
        } elseif (($query['assigned_user_id'] ?? '') !== '') {
            $assignedUserId = max(0, (int) $query['assigned_user_id']);
            $assignedUserId = $assignedUserId > 0 ? $assignedUserId : '';
        }

        return [
            'q' => substr(trim((string) ($query['q'] ?? '')), 0, 100),
            'status' => self::isValidStatus($status) ? $status : '',
            'priority' => self::isValidPriority($priority) ? $priority : '',
            'assigned_user_id' => $assignedUserId,
        ];
    }

    /**
     * @param array<string, mixed> $query
     * @return array{page: int, per_page: int, offset: int}
     */
    public static function paginationFromQuery(array $query): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = self::DEFAULT_PER_PAGE;

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => ($page - 1) * $perPage,
        ];
    }

    /**
     * @param array{q: string, status: string, priority: string, assigned_user_id: int|string} $filters
     */
    public static function hasActiveFilters(array $filters, array $user): bool
    {
        return $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['priority'] !== ''
            || (self::canAccessAll($user) && $filters['assigned_user_id'] !== '');
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function canAccessAll(array $user): bool
    {
        return ($user['role'] ?? '') === UserModel::ROLE_ADMIN;
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $lead
     */
    public static function canAccessLead(array $user, array $lead): bool
    {
        if (self::canAccessAll($user)) {
            return true;
        }

        return ($user['role'] ?? '') === UserModel::ROLE_SALES_REP
            && isset($lead['assigned_user_id'])
            && (int) $lead['assigned_user_id'] === (int) ($user['id'] ?? 0);
    }

    /**
     * @param array<string, mixed> $lead
     */
    public static function isConverted(array $lead): bool
    {
        return ($lead['status'] ?? '') === self::STATUS_CONVERTED
            || ! empty($lead['converted_customer_id'])
            || ! empty($lead['converted_at']);
    }

    /**
     * @param array<string, mixed> $lead
     */
    public static function canConvert(array $lead): bool
    {
        return ! self::isConverted($lead);
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $filters
     * @param array<string, string> $sort
     * @param array<string, int> $pagination
     * @return array<int, array<string, mixed>>
     */
    public function list(array $user, array $filters = [], array $sort = [], array $pagination = []): array
    {
        $filters = array_replace(self::normalizeFilters([], $user), $filters);
        $sort = self::normalizeSort($sort);
        $pagination = array_replace(self::paginationFromQuery([]), $pagination);
        [$whereSql, $params] = $this->buildWhere($user, $filters);

        $sql = 'SELECT l.id, l.assigned_user_id, l.first_name, l.last_name, l.company,
                       l.email, l.phone, l.source, l.status, l.priority, l.estimated_value,
                       l.converted_customer_id, l.converted_at, l.created_at, l.updated_at,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   leads l
                LEFT   JOIN users u ON l.assigned_user_id = u.id'
            . $whereSql
            . ' ORDER BY ' . $this->sortColumn($sort['sort']) . ' ' . strtoupper($sort['direction']) . ', l.id DESC'
            . ' LIMIT ' . (int) $pagination['per_page']
            . ' OFFSET ' . (int) $pagination['offset'];

        return $this->findAll($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $filters
     */
    public function count(array $user, array $filters = []): int
    {
        $filters = array_replace(self::normalizeFilters([], $user), $filters);
        [$whereSql, $params] = $this->buildWhere($user, $filters);

        $row = $this->findOne(
            'SELECT COUNT(*) AS total
             FROM   leads l'
            . $whereSql,
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>|null
     */
    public function findById(int $id, array $user): ?array
    {
        $sql = 'SELECT l.*, CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   leads l
                LEFT   JOIN users u ON l.assigned_user_id = u.id
                WHERE  l.id = :id';
        $params = [':id' => $id];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND l.assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        $sql .= ' LIMIT 1';

        return $this->findOne($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>|null
     */
    public function findByIdForUser(int $id, array $user): ?array
    {
        return $this->findById($id, $user);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>|null
     */
    public function findByIdForUpdate(int $id, array $user): ?array
    {
        $sql = 'SELECT l.*, CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   leads l
                LEFT   JOIN users u ON l.assigned_user_id = u.id
                WHERE  l.id = :id';
        $params = [':id' => $id];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND l.assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        $sql .= ' LIMIT 1 FOR UPDATE';

        return $this->findOne($sql, $params);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO leads (
                assigned_user_id, first_name, last_name, company, email, phone,
                source, status, priority, estimated_value, notes
             ) VALUES (
                :assigned_user_id, :first_name, :last_name, :company, :email, :phone,
                :source, :status, :priority, :estimated_value, :notes
             )',
            $this->params($data)
        );

        return $this->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE leads
             SET    assigned_user_id = :assigned_user_id,
                    first_name = :first_name,
                    last_name = :last_name,
                    company = :company,
                    email = :email,
                    phone = :phone,
                    source = :source,
                    status = :status,
                    priority = :priority,
                    estimated_value = :estimated_value,
                    notes = :notes
             WHERE  id = :id',
            array_merge([':id' => $id], $this->params($data))
        );
    }

    /**
     * @param array<string, mixed> $user
     */
    public function delete(int $id, array $user): bool
    {
        $sql = 'DELETE FROM leads WHERE id = :id';
        $params = [':id' => $id];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        return $this->execute($sql, $params);
    }

    public function markConverted(int $id, int $customerId): bool
    {
        return $this->execute(
            'UPDATE leads
             SET    status = :converted_status_set,
                    converted_customer_id = :customer_id,
                    converted_at = NOW()
             WHERE  id = :id
             AND    status <> :converted_status_where
             AND    converted_customer_id IS NULL
             AND    converted_at IS NULL',
            [
                ':id' => $id,
                ':customer_id' => $customerId,
                ':converted_status_set' => self::STATUS_CONVERTED,
                ':converted_status_where' => self::STATUS_CONVERTED,
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function params(array $data): array
    {
        return [
            ':assigned_user_id' => $data['assigned_user_id'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':company' => $data['company'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':source' => $data['source'],
            ':status' => $data['status'],
            ':priority' => $data['priority'],
            ':estimated_value' => $data['estimated_value'],
            ':notes' => $data['notes'],
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function buildWhere(array $user, array $filters): array
    {
        $clauses = [];
        $params = [];

        if (! self::canAccessAll($user)) {
            $clauses[] = 'l.assigned_user_id = :scoped_user_id';
            $params[':scoped_user_id'] = (int) ($user['id'] ?? 0);
        } elseif (($filters['assigned_user_id'] ?? '') !== '') {
            $clauses[] = 'l.assigned_user_id = :assigned_user_id';
            $params[':assigned_user_id'] = (int) $filters['assigned_user_id'];
        }

        if (($filters['status'] ?? '') !== '') {
            $clauses[] = 'l.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (($filters['priority'] ?? '') !== '') {
            $clauses[] = 'l.priority = :priority';
            $params[':priority'] = $filters['priority'];
        }

        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(l.first_name LIKE :q_first_name
                OR l.last_name LIKE :q_last_name
                OR l.company LIKE :q_company
                OR l.email LIKE :q_email
                OR l.phone LIKE :q_phone)';

            $search = '%' . $filters['q'] . '%';
            $params[':q_first_name'] = $search;
            $params[':q_last_name'] = $search;
            $params[':q_company'] = $search;
            $params[':q_email'] = $search;
            $params[':q_phone'] = $search;
        }

        if ($clauses === []) {
            return ['', $params];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    private function sortColumn(string $sort): string
    {
        return match ($sort) {
            'status' => 'l.status',
            'priority' => 'l.priority',
            'estimated_value' => 'l.estimated_value',
            default => 'l.created_at',
        };
    }
}
