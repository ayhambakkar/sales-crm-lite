<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class FollowUpModel extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public const DEFAULT_PER_PAGE = 10;

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [self::STATUS_OPEN, self::STATUS_DONE, self::STATUS_CANCELLED];
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
        return ['due_at', 'priority', 'status', 'created_at'];
    }

    public static function isAllowedSortField(string $field): bool
    {
        return in_array($field, self::allowedSortFields(), true);
    }

    public static function hasExactlyOneParent(mixed $leadId, mixed $customerId): bool
    {
        $hasLead = $leadId !== null && $leadId !== '' && (int) $leadId > 0;
        $hasCustomer = $customerId !== null && $customerId !== '' && (int) $customerId > 0;

        return $hasLead !== $hasCustomer;
    }

    /**
     * @param array<string, mixed> $followUp
     */
    public static function isOverdue(array $followUp, ?string $now = null): bool
    {
        if (($followUp['status'] ?? '') !== self::STATUS_OPEN || empty($followUp['due_at'])) {
            return false;
        }

        $dueAt = strtotime((string) $followUp['due_at']);
        $nowAt = strtotime($now ?? date('Y-m-d H:i:s'));

        return $dueAt !== false && $nowAt !== false && $dueAt < $nowAt;
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
     * @param array<string, mixed> $followUp
     */
    public static function canAccessFollowUp(array $user, array $followUp): bool
    {
        if (self::canAccessAll($user)) {
            return true;
        }

        return ($user['role'] ?? '') === UserModel::ROLE_SALES_REP
            && isset($followUp['assigned_user_id'])
            && (int) $followUp['assigned_user_id'] === (int) ($user['id'] ?? 0);
    }

    /**
     * @param array<string, mixed> $query
     * @return array{sort: string, direction: string}
     */
    public static function normalizeSort(array $query): array
    {
        $sort = (string) ($query['sort'] ?? 'due_at');
        $direction = strtolower((string) ($query['direction'] ?? 'asc'));

        if (! self::isAllowedSortField($sort)) {
            $sort = 'due_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return [
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $user
     * @return array{q: string, status: string, priority: string, assigned_user_id: int|string, overdue: bool}
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
            'overdue' => (string) ($query['overdue'] ?? '') === '1',
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
     * @param array{q: string, status: string, priority: string, assigned_user_id: int|string, overdue: bool} $filters
     * @param array<string, mixed> $user
     */
    public static function hasActiveFilters(array $filters, array $user): bool
    {
        return $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['priority'] !== ''
            || $filters['overdue']
            || (self::canAccessAll($user) && $filters['assigned_user_id'] !== '');
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

        $sql = $this->baseSelect()
            . $whereSql
            . ' ORDER BY ' . $this->sortColumn($sort['sort']) . ' ' . strtoupper($sort['direction']) . ', f.id DESC'
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
             FROM   follow_ups f
             LEFT   JOIN leads l ON f.lead_id = l.id
             LEFT   JOIN customers c ON f.customer_id = c.id'
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
        $params = [':id' => $id];
        $sql = $this->baseSelect() . ' WHERE f.id = :id';

        if (! self::canAccessAll($user)) {
            $sql .= ' AND f.assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        $sql .= ' LIMIT 1';

        return $this->findOne($sql, $params);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO follow_ups (
                assigned_user_id, lead_id, customer_id, title, description,
                due_at, status, priority, completed_at
             ) VALUES (
                :assigned_user_id, :lead_id, :customer_id, :title, :description,
                :due_at, :status, :priority, :completed_at
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
            'UPDATE follow_ups
             SET    assigned_user_id = :assigned_user_id,
                    lead_id = :lead_id,
                    customer_id = :customer_id,
                    title = :title,
                    description = :description,
                    due_at = :due_at,
                    status = :status,
                    priority = :priority,
                    completed_at = :completed_at
             WHERE  id = :id',
            array_merge([':id' => $id], $this->params($data))
        );
    }

    /**
     * @param array<string, mixed> $user
     */
    public function delete(int $id, array $user): bool
    {
        $sql = 'DELETE FROM follow_ups WHERE id = :id';
        $params = [':id' => $id];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        return $this->execute($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     */
    public function markDone(int $id, array $user): bool
    {
        $sql = 'UPDATE follow_ups
                SET    status = :status_done,
                       completed_at = NOW()
                WHERE  id = :id';
        $params = [
            ':id' => $id,
            ':status_done' => self::STATUS_DONE,
        ];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        return $this->execute($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     */
    public function markCancelled(int $id, array $user): bool
    {
        $sql = 'UPDATE follow_ups
                SET    status = :status_cancelled,
                       completed_at = NULL
                WHERE  id = :id';
        $params = [
            ':id' => $id,
            ':status_cancelled' => self::STATUS_CANCELLED,
        ];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        return $this->execute($sql, $params);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function listForLead(int $leadId, array $user, int $limit = 10): array
    {
        return $this->listForParent('lead_id', $leadId, $user, $limit);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function listForCustomer(int $customerId, array $user, int $limit = 10): array
    {
        return $this->listForParent('customer_id', $customerId, $user, $limit);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function params(array $data): array
    {
        return [
            ':assigned_user_id' => $data['assigned_user_id'],
            ':lead_id' => $data['lead_id'],
            ':customer_id' => $data['customer_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':due_at' => $data['due_at'],
            ':status' => $data['status'],
            ':priority' => $data['priority'],
            ':completed_at' => $data['completed_at'],
        ];
    }

    private function baseSelect(): string
    {
        return 'SELECT f.id, f.assigned_user_id, f.lead_id, f.customer_id,
                       f.title, f.description, f.due_at, f.status, f.priority,
                       f.completed_at, f.created_at, f.updated_at,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to,
                       CONCAT(l.first_name, " ", l.last_name) AS lead_name,
                       l.company AS lead_company,
                       CONCAT(c.first_name, " ", c.last_name) AS customer_name,
                       c.company AS customer_company
                FROM   follow_ups f
                LEFT   JOIN users u ON f.assigned_user_id = u.id
                LEFT   JOIN leads l ON f.lead_id = l.id
                LEFT   JOIN customers c ON f.customer_id = c.id';
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
            $clauses[] = 'f.assigned_user_id = :scoped_user_id';
            $params[':scoped_user_id'] = (int) ($user['id'] ?? 0);
        } elseif (($filters['assigned_user_id'] ?? '') !== '') {
            $clauses[] = 'f.assigned_user_id = :assigned_user_id';
            $params[':assigned_user_id'] = (int) $filters['assigned_user_id'];
        }

        if (($filters['status'] ?? '') !== '') {
            $clauses[] = 'f.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (($filters['priority'] ?? '') !== '') {
            $clauses[] = 'f.priority = :priority';
            $params[':priority'] = $filters['priority'];
        }

        if (! empty($filters['overdue'])) {
            $clauses[] = 'f.status = :overdue_status AND f.due_at < NOW()';
            $params[':overdue_status'] = self::STATUS_OPEN;
        }

        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(f.title LIKE :q_title
                OR f.description LIKE :q_description
                OR l.first_name LIKE :q_lead_first_name
                OR l.last_name LIKE :q_lead_last_name
                OR l.company LIKE :q_lead_company
                OR c.first_name LIKE :q_customer_first_name
                OR c.last_name LIKE :q_customer_last_name
                OR c.company LIKE :q_customer_company)';

            $search = '%' . $filters['q'] . '%';
            $params[':q_title'] = $search;
            $params[':q_description'] = $search;
            $params[':q_lead_first_name'] = $search;
            $params[':q_lead_last_name'] = $search;
            $params[':q_lead_company'] = $search;
            $params[':q_customer_first_name'] = $search;
            $params[':q_customer_last_name'] = $search;
            $params[':q_customer_company'] = $search;
        }

        if ($clauses === []) {
            return ['', $params];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    private function listForParent(string $column, int $parentId, array $user, int $limit): array
    {
        $sql = $this->baseSelect() . ' WHERE f.' . $column . ' = :parent_id';
        $params = [':parent_id' => $parentId];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND f.assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        $sql .= ' ORDER BY f.status ASC, f.due_at ASC, f.id DESC LIMIT ' . max(1, min(20, $limit));

        return $this->findAll($sql, $params);
    }

    private function sortColumn(string $sort): string
    {
        return match ($sort) {
            'priority' => 'f.priority',
            'status' => 'f.status',
            'created_at' => 'f.created_at',
            default => 'f.due_at',
        };
    }
}
