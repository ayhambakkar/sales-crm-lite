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
     * @param array<string, mixed> $user
     * @return array<int, array<string, mixed>>
     */
    public function list(array $user): array
    {
        $sql = 'SELECT l.id, l.assigned_user_id, l.first_name, l.last_name, l.company,
                       l.email, l.phone, l.source, l.status, l.priority, l.estimated_value,
                       l.created_at, l.updated_at,
                       CONCAT(u.first_name, " ", u.last_name) AS assigned_to
                FROM   leads l
                LEFT   JOIN users u ON l.assigned_user_id = u.id';
        $params = [];

        if (! self::canAccessAll($user)) {
            $sql .= ' WHERE l.assigned_user_id = :user_id';
            $params[':user_id'] = (int) $user['id'];
        }

        $sql .= ' ORDER BY l.created_at DESC, l.id DESC';

        return $this->findAll($sql, $params);
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
}
