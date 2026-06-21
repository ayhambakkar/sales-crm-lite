<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Logger;
use App\Models\ActivityModel;
use Throwable;

class ActivityLogger
{
    private ActivityModel $activities;

    public function __construct(?ActivityModel $activities = null)
    {
        $this->activities = $activities ?? new ActivityModel();
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logUserAction(
        string $action,
        int $userId,
        ?string $description = null,
        array $metadata = [],
        ?int $actorUserId = null
    ): void {
        $this->log(
            $actorUserId ?? Auth::id(),
            ActivityModel::ENTITY_USER,
            $userId,
            $action,
            $description,
            $metadata
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logLeadAction(
        string $action,
        int $leadId,
        ?string $description = null,
        array $metadata = [],
        ?int $actorUserId = null
    ): void {
        $this->log(
            $actorUserId ?? Auth::id(),
            ActivityModel::ENTITY_LEAD,
            $leadId,
            $action,
            $description,
            $metadata
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logCustomerAction(
        string $action,
        int $customerId,
        ?string $description = null,
        array $metadata = [],
        ?int $actorUserId = null
    ): void {
        $this->log(
            $actorUserId ?? Auth::id(),
            ActivityModel::ENTITY_CUSTOMER,
            $customerId,
            $action,
            $description,
            $metadata
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logFollowUpAction(
        string $action,
        int $followUpId,
        ?string $description = null,
        array $metadata = [],
        ?int $actorUserId = null
    ): void {
        $this->log(
            $actorUserId ?? Auth::id(),
            ActivityModel::ENTITY_FOLLOW_UP,
            $followUpId,
            $action,
            $description,
            $metadata
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logAuthAction(
        string $action,
        ?int $userId,
        ?string $description = null,
        array $metadata = []
    ): void {
        $this->log(
            $userId,
            ActivityModel::ENTITY_AUTH,
            $userId,
            $action,
            $description,
            $metadata
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logSystemAction(
        string $action,
        ?string $description = null,
        array $metadata = [],
        ?int $actorUserId = null
    ): void {
        $this->log(
            $actorUserId ?? Auth::id(),
            ActivityModel::ENTITY_SYSTEM,
            null,
            $action,
            $description,
            $metadata
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function log(
        ?int $userId,
        string $entityType,
        ?int $entityId,
        string $action,
        ?string $description,
        array $metadata
    ): void {
        try {
            $this->activities->log($userId, $entityType, $entityId, $action, $description, $metadata);
        } catch (Throwable $exception) {
            Logger::exception($exception);
        }
    }
}
