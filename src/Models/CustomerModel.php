<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CustomerModel extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_VIP = 'vip';

    public const DEFAULT_PER_PAGE = 10;

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_VIP];
    }

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::statuses(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function allowedSortFields(): array
    {
        return ['created_at', 'company', 'customer_status'];
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
     * @return array{q: string, customer_status: string, assigned_user_id: int|string}
     */
    public static function normalizeFilters(array $query, array $user): array
    {
        $status = (string) ($query['customer_status'] ?? '');
        $assignedUserId = '';

        if (! self::canAccessAll($user)) {
            $assignedUserId = (int) ($user['id'] ?? 0);
        } elseif (($query['assigned_user_id'] ?? '') !== '') {
            $assignedUserId = max(0, (int) $query['assigned_user_id']);
            $assignedUserId = $assignedUserId > 0 ? $assignedUserId : '';
        }

        return [
            'q' => substr(trim((string) ($query['q'] ?? '')), 0, 100),
            'customer_status' => self::isValidStatus($status) ? $status : '',
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
     * @param array{q: string, customer_status: string, assigned_user_id: int|string} $filters
     */
    public static function hasActiveFilters(array $filters, array $user): bool
    {
        return $filters['q'] !== ''
            || $filters['customer_status'] !== ''
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
     * @param array<string, mixed> $customer
     */
    public static function canAccessCustomer(array $user, array $customer): bool
    {
        if (self::canAccessAll($user)) {
            return true;
        }

        return ($user['role'] ?? '') === UserModel::ROLE_SALES_REP
            && isset($customer['assigned_user_id'])
            && (int) $customer['assigned_user_id'] === (int) ($user['id'] ?? 0);
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

        $sql = 'SELECT c.id, c.assigned_user_id, c.first_name, c.last_name, c.company,
                       c.email, c.phone, c.city, c.country, c.customer_status,
                       c.created_at, c.updated_at,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   customers c
                LEFT   JOIN users u ON c.assigned_user_id = u.id'
            . $whereSql
            . ' ORDER BY ' . $this->sortColumn($sort['sort']) . ' ' . strtoupper($sort['direction']) . ', c.id DESC'
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
             FROM   customers c'
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
        $sql = 'SELECT c.*, CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   customers c
                LEFT   JOIN users u ON c.assigned_user_id = u.id
                WHERE  c.id = :id';
        $params = [':id' => $id];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND c.assigned_user_id = :user_id';
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
            'INSERT INTO customers (
                assigned_user_id, first_name, last_name, company, email, phone,
                address, city, postal_code, country, customer_status, notes
             ) VALUES (
                :assigned_user_id, :first_name, :last_name, :company, :email, :phone,
                :address, :city, :postal_code, :country, :customer_status, :notes
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
            'UPDATE customers
             SET    assigned_user_id = :assigned_user_id,
                    first_name = :first_name,
                    last_name = :last_name,
                    company = :company,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    city = :city,
                    postal_code = :postal_code,
                    country = :country,
                    customer_status = :customer_status,
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
        $sql = 'DELETE FROM customers WHERE id = :id';
        $params = [':id' => $id];

        if (! self::canAccessAll($user)) {
            $sql .= ' AND assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        return $this->execute($sql, $params);
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
            ':address' => $data['address'],
            ':city' => $data['city'],
            ':postal_code' => $data['postal_code'],
            ':country' => $data['country'],
            ':customer_status' => $data['customer_status'],
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
            $clauses[] = 'c.assigned_user_id = :scoped_user_id';
            $params[':scoped_user_id'] = (int) ($user['id'] ?? 0);
        } elseif (($filters['assigned_user_id'] ?? '') !== '') {
            $clauses[] = 'c.assigned_user_id = :assigned_user_id';
            $params[':assigned_user_id'] = (int) $filters['assigned_user_id'];
        }

        if (($filters['customer_status'] ?? '') !== '') {
            $clauses[] = 'c.customer_status = :customer_status';
            $params[':customer_status'] = $filters['customer_status'];
        }

        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(c.first_name LIKE :q_first_name
                OR c.last_name LIKE :q_last_name
                OR c.company LIKE :q_company
                OR c.email LIKE :q_email
                OR c.phone LIKE :q_phone
                OR c.city LIKE :q_city)';

            $search = '%' . $filters['q'] . '%';
            $params[':q_first_name'] = $search;
            $params[':q_last_name'] = $search;
            $params[':q_company'] = $search;
            $params[':q_email'] = $search;
            $params[':q_phone'] = $search;
            $params[':q_city'] = $search;
        }

        if ($clauses === []) {
            return ['', $params];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    private function sortColumn(string $sort): string
    {
        return match ($sort) {
            'company' => 'c.company',
            'customer_status' => 'c.customer_status',
            default => 'c.created_at',
        };
    }
}
