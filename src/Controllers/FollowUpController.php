<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityModel;
use App\Models\CustomerModel;
use App\Models\FollowUpModel;
use App\Models\LeadModel;
use App\Models\UserModel;
use App\Services\ActivityLogger;
use DateTimeImmutable;

class FollowUpController extends Controller
{
    private FollowUpModel $followUps;
    private LeadModel $leads;
    private CustomerModel $customers;
    private UserModel $users;
    private ActivityModel $activities;
    private ActivityLogger $activityLogger;

    public function __construct()
    {
        $this->followUps = new FollowUpModel();
        $this->leads = new LeadModel();
        $this->customers = new CustomerModel();
        $this->users = new UserModel();
        $this->activities = new ActivityModel();
        $this->activityLogger = new ActivityLogger();
    }

    public function index(): void
    {
        $user = $this->currentUser();
        $filters = FollowUpModel::normalizeFilters($_GET, $user);
        $sort = FollowUpModel::normalizeSort($_GET);
        $pagination = FollowUpModel::paginationFromQuery($_GET);
        $total = $this->followUps->count($user, $filters);
        $totalPages = max(1, (int) ceil($total / $pagination['per_page']));

        if ($pagination['page'] > $totalPages) {
            $pagination['page'] = $totalPages;
            $pagination['offset'] = ($totalPages - 1) * $pagination['per_page'];
        }

        $this->render('follow-ups/index', [
            'title' => 'Follow-Ups',
            'followUps' => $this->followUps->list($user, $filters, $sort, $pagination),
            'currentUser' => $user,
            'filters' => $filters,
            'sort' => $sort,
            'pagination' => array_merge($pagination, [
                'total' => $total,
                'total_pages' => $totalPages,
            ]),
            'statuses' => FollowUpModel::statuses(),
            'priorities' => FollowUpModel::priorities(),
            'assignees' => $this->assignees(),
            'hasActiveFilters' => FollowUpModel::hasActiveFilters($filters, $user),
        ]);
    }

    public function create(): void
    {
        $this->render('follow-ups/create', $this->formData([
            'title' => 'Create Follow-Up',
            'errors' => [],
            'followUp' => $this->emptyFollowUp(),
        ]));
    }

    public function store(): void
    {
        $user = $this->currentUser();
        $input = $this->followUpInput($user);
        $errors = $this->validateFollowUp($input, $user);

        if ($errors !== []) {
            $this->render('follow-ups/create', $this->formData([
                'title' => 'Create Follow-Up',
                'errors' => $errors,
                'followUp' => $input,
            ]));
            return;
        }

        $followUpId = $this->followUps->create($input);

        $this->activityLogger->logFollowUpAction(
            ActivityModel::ACTION_CREATED,
            $followUpId,
            'Created follow-up ' . $input['title'] . '.',
            [
                'assigned_user_id' => $input['assigned_user_id'],
                'lead_id' => $input['lead_id'],
                'customer_id' => $input['customer_id'],
                'status' => $input['status'],
                'priority' => $input['priority'],
                'due_at' => $input['due_at'],
            ]
        );

        Session::flash('success', 'Follow-up created successfully.');
        $this->redirect('/follow-ups/' . $followUpId);
    }

    public function show(string $id): void
    {
        $followUp = $this->findFollowUpOrFail($id);

        $this->render('follow-ups/show', [
            'title' => 'Follow-Up Details',
            'followUp' => $followUp,
            'currentUser' => $this->currentUser(),
            'activityItems' => $this->activities->forEntity(ActivityModel::ENTITY_FOLLOW_UP, (int) $followUp['id'], $this->currentUser(), 5),
        ]);
    }

    public function edit(string $id): void
    {
        $followUp = $this->findFollowUpOrFail($id);

        $this->render('follow-ups/edit', $this->formData([
            'title' => 'Edit Follow-Up',
            'errors' => [],
            'followUp' => $followUp,
        ]));
    }

    public function update(string $id): void
    {
        $followUp = $this->findFollowUpOrFail($id);
        $user = $this->currentUser();
        $input = $this->followUpInput($user, $followUp);
        $errors = $this->validateFollowUp($input, $user);

        if ($errors !== []) {
            $this->render('follow-ups/edit', $this->formData([
                'title' => 'Edit Follow-Up',
                'errors' => $errors,
                'followUp' => array_merge($followUp, $input),
            ]));
            return;
        }

        $this->followUps->update((int) $followUp['id'], $input);

        $this->activityLogger->logFollowUpAction(
            ActivityModel::ACTION_UPDATED,
            (int) $followUp['id'],
            'Updated follow-up ' . $input['title'] . '.',
            [
                'assigned_user_id' => $input['assigned_user_id'],
                'lead_id' => $input['lead_id'],
                'customer_id' => $input['customer_id'],
                'status' => $input['status'],
                'priority' => $input['priority'],
                'previous_status' => $followUp['status'],
                'previous_priority' => $followUp['priority'],
                'due_at' => $input['due_at'],
            ]
        );

        Session::flash('success', 'Follow-up updated successfully.');
        $this->redirect('/follow-ups/' . (int) $followUp['id']);
    }

    public function markDone(string $id): void
    {
        $followUp = $this->findFollowUpOrFail($id);
        $this->followUps->markDone((int) $followUp['id'], $this->currentUser());

        $this->activityLogger->logFollowUpAction(
            ActivityModel::ACTION_COMPLETED,
            (int) $followUp['id'],
            'Marked follow-up ' . $followUp['title'] . ' as done.',
            [
                'assigned_user_id' => $followUp['assigned_user_id'],
                'previous_status' => $followUp['status'],
            ]
        );

        Session::flash('success', 'Follow-up marked as done.');
        $this->redirect('/follow-ups/' . (int) $followUp['id']);
    }

    public function markCancelled(string $id): void
    {
        $followUp = $this->findFollowUpOrFail($id);
        $this->followUps->markCancelled((int) $followUp['id'], $this->currentUser());

        $this->activityLogger->logFollowUpAction(
            ActivityModel::ACTION_CANCELLED,
            (int) $followUp['id'],
            'Cancelled follow-up ' . $followUp['title'] . '.',
            [
                'assigned_user_id' => $followUp['assigned_user_id'],
                'previous_status' => $followUp['status'],
            ]
        );

        Session::flash('success', 'Follow-up cancelled.');
        $this->redirect('/follow-ups/' . (int) $followUp['id']);
    }

    public function destroy(string $id): void
    {
        $followUp = $this->findFollowUpOrFail($id);
        $user = $this->currentUser();

        $this->followUps->delete((int) $followUp['id'], $user);

        $this->activityLogger->logFollowUpAction(
            ActivityModel::ACTION_DELETED,
            (int) $followUp['id'],
            'Deleted follow-up ' . $followUp['title'] . '.',
            [
                'assigned_user_id' => $followUp['assigned_user_id'],
                'lead_id' => $followUp['lead_id'],
                'customer_id' => $followUp['customer_id'],
                'status' => $followUp['status'],
                'priority' => $followUp['priority'],
            ]
        );

        Session::flash('success', 'Follow-up deleted successfully.');
        $this->redirect('/follow-ups');
    }

    /**
     * @param array<string, mixed>|null $existingFollowUp
     * @return array<string, mixed>
     */
    private function followUpInput(array $user, ?array $existingFollowUp = null): array
    {
        $assignedUserId = trim((string) ($_POST['assigned_user_id'] ?? ''));

        if (! FollowUpModel::canAccessAll($user)) {
            $assignedUserId = (string) ($existingFollowUp['assigned_user_id'] ?? $user['id']);
        }

        $status = trim((string) ($_POST['status'] ?? FollowUpModel::STATUS_OPEN));
        $completedAt = null;

        if ($status === FollowUpModel::STATUS_DONE) {
            $completedAt = (string) ($existingFollowUp['completed_at'] ?? date('Y-m-d H:i:s'));
        }

        return [
            'assigned_user_id' => $assignedUserId === '' ? null : (int) $assignedUserId,
            'lead_id' => $this->nullableId('lead_id'),
            'customer_id' => $this->nullableId('customer_id'),
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => $this->nullableString('description'),
            'due_at' => $this->normalizeDateTime((string) ($_POST['due_at'] ?? '')),
            'status' => $status,
            'priority' => trim((string) ($_POST['priority'] ?? FollowUpModel::PRIORITY_MEDIUM)),
            'completed_at' => $completedAt,
        ];
    }

    /**
     * @param array<string, mixed> $followUp
     * @param array<string, mixed> $user
     * @return array<string, string>
     */
    private function validateFollowUp(array $followUp, array $user): array
    {
        $errors = [];

        if ($followUp['title'] === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen((string) $followUp['title']) > 150) {
            $errors['title'] = 'Title may not exceed 150 characters.';
        }

        if ($followUp['due_at'] === null) {
            $errors['due_at'] = 'Due date is required and must be valid.';
        }

        if (! FollowUpModel::isValidStatus((string) $followUp['status'])) {
            $errors['status'] = 'Status is invalid.';
        }

        if (! FollowUpModel::isValidPriority((string) $followUp['priority'])) {
            $errors['priority'] = 'Priority is invalid.';
        }

        if (! FollowUpModel::hasExactlyOneParent($followUp['lead_id'], $followUp['customer_id'])) {
            $errors['parent'] = 'Choose exactly one related lead or customer.';
        } elseif ($followUp['lead_id'] !== null) {
            if ($this->leads->findById((int) $followUp['lead_id'], $user) === null) {
                $errors['parent'] = 'Selected lead is unavailable.';
            }
        } elseif ($followUp['customer_id'] !== null) {
            if ($this->customers->findById((int) $followUp['customer_id'], $user) === null) {
                $errors['parent'] = 'Selected customer is unavailable.';
            }
        }

        if (FollowUpModel::canAccessAll($user) && $followUp['assigned_user_id'] !== null) {
            $assignee = $this->users->findActiveById((int) $followUp['assigned_user_id']);

            if ($assignee === null) {
                $errors['assigned_user_id'] = 'Assigned user must be active.';
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function formData(array $overrides): array
    {
        $user = $this->currentUser();

        return array_merge([
            'statuses' => FollowUpModel::statuses(),
            'priorities' => FollowUpModel::priorities(),
            'assignees' => $this->assignees(),
            'leadOptions' => $this->leadOptions(),
            'customerOptions' => $this->customerOptions(),
            'currentUser' => $user,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyFollowUp(): array
    {
        return [
            'assigned_user_id' => FollowUpModel::canAccessAll($this->currentUser()) ? null : Auth::id(),
            'lead_id' => $this->queryNullableId('lead_id'),
            'customer_id' => $this->queryNullableId('customer_id'),
            'title' => '',
            'description' => '',
            'due_at' => '',
            'status' => FollowUpModel::STATUS_OPEN,
            'priority' => FollowUpModel::PRIORITY_MEDIUM,
            'completed_at' => null,
        ];
    }

    private function normalizeDateTime(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if (
                $date instanceof DateTimeImmutable
                && ($errors === false || ((int) $errors['warning_count'] === 0 && (int) $errors['error_count'] === 0))
            ) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    private function nullableId(string $key): ?int
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        return $value === '' || (int) $value <= 0 ? null : (int) $value;
    }

    private function queryNullableId(string $key): ?int
    {
        $value = trim((string) ($_GET[$key] ?? ''));
        return $value === '' || (int) $value <= 0 ? null : (int) $value;
    }

    private function nullableString(string $key): ?string
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function assignees(): array
    {
        return FollowUpModel::canAccessAll($this->currentUser())
            ? $this->users->listActiveAssignableUsers()
            : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function leadOptions(): array
    {
        return $this->leads->list(
            $this->currentUser(),
            [],
            ['sort' => 'created_at', 'direction' => 'desc'],
            ['per_page' => 100, 'offset' => 0]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function customerOptions(): array
    {
        return $this->customers->list(
            $this->currentUser(),
            [],
            ['sort' => 'created_at', 'direction' => 'desc'],
            ['per_page' => 100, 'offset' => 0]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function currentUser(): array
    {
        $user = Auth::user();

        if ($user === null) {
            $this->abort(403);
        }

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function findFollowUpOrFail(string $id): array
    {
        $followUpId = (int) $id;

        if ($followUpId <= 0) {
            $this->abort(404);
        }

        $followUp = $this->followUps->findById($followUpId, $this->currentUser());

        if ($followUp === null) {
            $this->abort(404);
        }

        return $followUp;
    }
}
