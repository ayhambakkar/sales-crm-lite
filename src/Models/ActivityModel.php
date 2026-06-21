<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;

class ActivityModel extends Model
{
    public const ENTITY_USER = 'user';
    public const ENTITY_LEAD = 'lead';
    public const ENTITY_CUSTOMER = 'customer';
    public const ENTITY_FOLLOW_UP = 'follow_up';
    public const ENTITY_AUTH = 'auth';
    public const ENTITY_SYSTEM = 'system';

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_CONVERTED = 'converted';
    public const ACTION_COMPLETED = 'completed';
    public const ACTION_CANCELLED = 'cancelled';
    public const ACTION_LOGGED_IN = 'logged_in';
    public const ACTION_LOGGED_OUT = 'logged_out';
    public const ACTION_PASSWORD_RESET = 'password_reset';
    public const ACTION_DEACTIVATED = 'deactivated';
    public const ACTION_ACTIVATED = 'activated';
    public const ACTION_VIEWED_REPORT = 'viewed_report';

    public const DEFAULT_PER_PAGE = 20;

    private const SENSITIVE_METADATA_KEYS = [
        'password',
        'password_hash',
        'current_password',
        'new_password',
        'token',
        'csrf_token',
        '_token',
    ];

    /**
     * @return array<int, string>
     */
    public static function entityTypes(): array
    {
        return [
            self::ENTITY_USER,
            self::ENTITY_LEAD,
            self::ENTITY_CUSTOMER,
            self::ENTITY_FOLLOW_UP,
            self::ENTITY_AUTH,
            self::ENTITY_SYSTEM,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function actions(): array
    {
        return [
            self::ACTION_CREATED,
            self::ACTION_UPDATED,
            self::ACTION_DELETED,
            self::ACTION_CONVERTED,
            self::ACTION_COMPLETED,
            self::ACTION_CANCELLED,
            self::ACTION_LOGGED_IN,
            self::ACTION_LOGGED_OUT,
            self::ACTION_PASSWORD_RESET,
            self::ACTION_DEACTIVATED,
            self::ACTION_ACTIVATED,
            self::ACTION_VIEWED_REPORT,
        ];
    }

    public static function isValidEntityType(string $entityType): bool
    {
        return in_array($entityType, self::entityTypes(), true);
    }

    public static function isValidAction(string $action): bool
    {
        return in_array($action, self::actions(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function resultKeys(): array
    {
        return [
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
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     *
     * @throws JsonException
     */
    public function log(
        ?int $userId,
        string $entityType,
        ?int $entityId,
        string $action,
        ?string $description = null,
        array $metadata = []
    ): int {
        if (! self::isValidEntityType($entityType)) {
            throw new InvalidArgumentException('Invalid activity entity type.');
        }

        if (! self::isValidAction($action)) {
            throw new InvalidArgumentException('Invalid activity action.');
        }

        $metadata = self::sanitizeMetadata($metadata);
        $metadataJson = $metadata === []
            ? null
            : json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $this->query(
            'INSERT INTO activities (
                user_id, entity_type, entity_id, action, description, metadata
             ) VALUES (
                :user_id, :entity_type, :entity_id, :action, :description, :metadata
             )',
            [
                ':user_id' => $userId,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':action' => $action,
                ':description' => $description,
                ':metadata' => $metadataJson,
            ]
        );

        return $this->lastInsertId();
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $user
     * @return array{entity_type: string, action: string, user_id: int|string, date_from: string, date_to: string}
     */
    public static function normalizeFilters(array $query, array $user): array
    {
        $entityType = (string) ($query['entity_type'] ?? '');
        $action = (string) ($query['action'] ?? '');
        $userId = '';

        if (self::canAccessAll($user) && ($query['user_id'] ?? '') !== '') {
            $candidateUserId = max(0, (int) $query['user_id']);
            $userId = $candidateUserId > 0 ? $candidateUserId : '';
        }

        return [
            'entity_type' => self::isValidEntityType($entityType) ? $entityType : '',
            'action' => self::isValidAction($action) ? $action : '',
            'user_id' => $userId,
            'date_from' => self::normalizeDate((string) ($query['date_from'] ?? '')),
            'date_to' => self::normalizeDate((string) ($query['date_to'] ?? '')),
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
     * @param array{entity_type: string, action: string, user_id: int|string, date_from: string, date_to: string} $filters
     */
    public static function hasActiveFilters(array $filters, array $user): bool
    {
        return $filters['entity_type'] !== ''
            || $filters['action'] !== ''
            || $filters['date_from'] !== ''
            || $filters['date_to'] !== ''
            || (self::canAccessAll($user) && $filters['user_id'] !== '');
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
     * @return array{0: string, 1: array<string, mixed>}
     */
    public static function scopeCondition(array $user): array
    {
        if (self::canAccessAll($user)) {
            return ['', []];
        }

        $userId = (int) ($user['id'] ?? 0);

        return [
            '(a.user_id = :activity_scope_user_id
                OR (a.entity_type = :activity_scope_user_entity_type AND a.entity_id = :activity_scope_user_entity_id)
                OR EXISTS (
                    SELECT 1
                    FROM   leads activity_scope_l
                    WHERE  activity_scope_l.id = a.entity_id
                      AND  a.entity_type = :activity_scope_lead_type
                      AND  activity_scope_l.assigned_user_id = :activity_scope_lead_user_id
                )
                OR EXISTS (
                    SELECT 1
                    FROM   customers activity_scope_c
                    WHERE  activity_scope_c.id = a.entity_id
                      AND  a.entity_type = :activity_scope_customer_type
                      AND  activity_scope_c.assigned_user_id = :activity_scope_customer_user_id
                )
                OR EXISTS (
                    SELECT 1
                    FROM   follow_ups activity_scope_f
                    WHERE  activity_scope_f.id = a.entity_id
                      AND  a.entity_type = :activity_scope_follow_up_type
                      AND  activity_scope_f.assigned_user_id = :activity_scope_follow_up_user_id
                )
            )',
            [
                ':activity_scope_user_id' => $userId,
                ':activity_scope_user_entity_type' => self::ENTITY_USER,
                ':activity_scope_user_entity_id' => $userId,
                ':activity_scope_lead_type' => self::ENTITY_LEAD,
                ':activity_scope_lead_user_id' => $userId,
                ':activity_scope_customer_type' => self::ENTITY_CUSTOMER,
                ':activity_scope_customer_user_id' => $userId,
                ':activity_scope_follow_up_type' => self::ENTITY_FOLLOW_UP,
                ':activity_scope_follow_up_user_id' => $userId,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public static function sanitizeMetadata(array $metadata): array
    {
        $clean = [];

        foreach ($metadata as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, self::SENSITIVE_METADATA_KEYS, true)) {
                continue;
            }

            $clean[$key] = is_array($value) ? self::sanitizeMetadata($value) : $value;
        }

        return $clean;
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $filters
     * @param array<string, int> $pagination
     * @return array<int, array<string, mixed>>
     */
    public function list(array $user, array $filters = [], array $pagination = []): array
    {
        $filters = array_replace(self::normalizeFilters([], $user), $filters);
        $pagination = array_replace(self::paginationFromQuery([]), $pagination);
        [$whereSql, $params] = $this->buildWhere($user, $filters);

        $sql = $this->baseSelect()
            . $whereSql
            . ' ORDER BY a.created_at DESC, a.id DESC'
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
             FROM   activities a'
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
        [$scopeSql, $scopeParams] = self::scopeCondition($user);
        $clauses = ['a.id = :id'];
        $params = array_merge([':id' => $id], $scopeParams);

        if ($scopeSql !== '') {
            $clauses[] = $scopeSql;
        }

        return $this->findOne(
            $this->baseSelect() . ' WHERE ' . implode(' AND ', $clauses) . ' LIMIT 1',
            $params
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function latest(array $user, int $limit = 5): array
    {
        return $this->list($user, [], [
            'page' => 1,
            'per_page' => max(1, min(20, $limit)),
            'offset' => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function forEntity(string $entityType, int $entityId, array $user, int $limit = 10): array
    {
        if (! self::isValidEntityType($entityType) || $entityId <= 0) {
            return [];
        }

        return $this->list($user, [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ], [
            'page' => 1,
            'per_page' => max(1, min(20, $limit)),
            'offset' => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $viewer
     * @return array<int, array<string, mixed>>
     */
    public function forUser(int $userId, array $viewer, int $limit = 20): array
    {
        if ($userId <= 0) {
            return [];
        }

        return $this->list($viewer, [
            'user_id' => $userId,
        ], [
            'page' => 1,
            'per_page' => max(1, min(50, $limit)),
            'offset' => 0,
        ]);
    }

    public static function entityLabel(string $entityType): string
    {
        return ucwords(str_replace('_', ' ', $entityType));
    }

    public static function actionLabel(string $action): string
    {
        return ucwords(str_replace('_', ' ', $action));
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
        [$scopeSql, $scopeParams] = self::scopeCondition($user);

        if ($scopeSql !== '') {
            $clauses[] = $scopeSql;
            $params = array_merge($params, $scopeParams);
        }

        if (($filters['entity_type'] ?? '') !== '') {
            $clauses[] = 'a.entity_type = :entity_type';
            $params[':entity_type'] = $filters['entity_type'];
        }

        if (($filters['entity_id'] ?? '') !== '') {
            $clauses[] = 'a.entity_id = :entity_id';
            $params[':entity_id'] = (int) $filters['entity_id'];
        }

        if (($filters['action'] ?? '') !== '') {
            $clauses[] = 'a.action = :action';
            $params[':action'] = $filters['action'];
        }

        if (($filters['user_id'] ?? '') !== '') {
            $filterUserId = (int) $filters['user_id'];

            if (self::canAccessAll($user) || $filterUserId === (int) ($user['id'] ?? 0)) {
                $clauses[] = 'a.user_id = :filter_user_id';
                $params[':filter_user_id'] = $filterUserId;
            } else {
                $clauses[] = '1 = 0';
            }
        }

        if (($filters['date_from'] ?? '') !== '') {
            $clauses[] = 'a.created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (($filters['date_to'] ?? '') !== '') {
            $clauses[] = 'a.created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if ($clauses === []) {
            return ['', $params];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    private function baseSelect(): string
    {
        return 'SELECT a.id, a.user_id, a.entity_type, a.entity_id, a.action,
                       a.description, a.metadata, a.created_at,
                       CONCAT(u.first_name, " ", u.last_name) AS actor_name,
                       u.email AS actor_email
                FROM   activities a
                LEFT   JOIN users u ON a.user_id = u.id';
    }

    private static function normalizeDate(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date instanceof DateTimeImmutable
            && ($errors === false || ((int) $errors['warning_count'] === 0 && (int) $errors['error_count'] === 0))
        ) {
            return $date->format('Y-m-d');
        }

        return '';
    }
}
