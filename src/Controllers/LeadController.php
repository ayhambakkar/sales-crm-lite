<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\LeadModel;
use App\Models\UserModel;

class LeadController extends Controller
{
    private LeadModel $leads;
    private UserModel $users;

    public function __construct()
    {
        $this->leads = new LeadModel();
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $user = $this->currentUser();
        $filters = LeadModel::normalizeFilters($_GET, $user);
        $sort = LeadModel::normalizeSort($_GET);
        $pagination = LeadModel::paginationFromQuery($_GET);
        $total = $this->leads->count($user, $filters);
        $totalPages = max(1, (int) ceil($total / $pagination['per_page']));

        if ($pagination['page'] > $totalPages) {
            $pagination['page'] = $totalPages;
            $pagination['offset'] = ($totalPages - 1) * $pagination['per_page'];
        }

        $this->render('leads/index', [
            'title' => 'Leads',
            'leads' => $this->leads->list($user, $filters, $sort, $pagination),
            'currentUser' => $user,
            'filters' => $filters,
            'sort' => $sort,
            'pagination' => array_merge($pagination, [
                'total' => $total,
                'total_pages' => $totalPages,
            ]),
            'statuses' => LeadModel::statuses(),
            'priorities' => LeadModel::priorities(),
            'assignees' => $this->assignees(),
            'hasActiveFilters' => LeadModel::hasActiveFilters($filters, $user),
        ]);
    }

    public function create(): void
    {
        $this->render('leads/create', [
            'title' => 'Create Lead',
            'errors' => [],
            'lead' => $this->emptyLead(),
            'statuses' => LeadModel::statuses(),
            'priorities' => LeadModel::priorities(),
            'assignees' => $this->assignees(),
            'currentUser' => $this->currentUser(),
        ]);
    }

    public function store(): void
    {
        $user = $this->currentUser();
        $input = $this->leadInput($user);
        $errors = $this->validateLead($input, $user);

        if ($errors !== []) {
            $this->render('leads/create', [
                'title' => 'Create Lead',
                'errors' => $errors,
                'lead' => $input,
                'statuses' => LeadModel::statuses(),
                'priorities' => LeadModel::priorities(),
                'assignees' => $this->assignees(),
                'currentUser' => $user,
            ]);
            return;
        }

        $leadId = $this->leads->create($input);

        Session::flash('success', 'Lead created successfully.');
        $this->redirect('/leads/' . $leadId);
    }

    public function show(string $id): void
    {
        $lead = $this->findLeadOrFail($id);

        $this->render('leads/show', [
            'title' => 'Lead Details',
            'lead' => $lead,
            'currentUser' => $this->currentUser(),
        ]);
    }

    public function edit(string $id): void
    {
        $lead = $this->findLeadOrFail($id);

        $this->render('leads/edit', [
            'title' => 'Edit Lead',
            'errors' => [],
            'lead' => $lead,
            'statuses' => LeadModel::statuses(),
            'priorities' => LeadModel::priorities(),
            'assignees' => $this->assignees(),
            'currentUser' => $this->currentUser(),
        ]);
    }

    public function update(string $id): void
    {
        $lead = $this->findLeadOrFail($id);
        $user = $this->currentUser();
        $input = $this->leadInput($user, $lead);
        $errors = $this->validateLead($input, $user);

        if ($errors !== []) {
            $this->render('leads/edit', [
                'title' => 'Edit Lead',
                'errors' => $errors,
                'lead' => array_merge($lead, $input),
                'statuses' => LeadModel::statuses(),
                'priorities' => LeadModel::priorities(),
                'assignees' => $this->assignees(),
                'currentUser' => $user,
            ]);
            return;
        }

        $this->leads->update((int) $lead['id'], $input);

        Session::flash('success', 'Lead updated successfully.');
        $this->redirect('/leads/' . (int) $lead['id']);
    }

    public function destroy(string $id): void
    {
        $lead = $this->findLeadOrFail($id);

        $this->leads->delete((int) $lead['id'], $this->currentUser());

        Session::flash('success', 'Lead deleted successfully.');
        $this->redirect('/leads');
    }

    /**
     * @param array<string, mixed>|null $existingLead
     * @return array<string, mixed>
     */
    private function leadInput(array $user, ?array $existingLead = null): array
    {
        $assignedUserId = trim((string) ($_POST['assigned_user_id'] ?? ''));

        if (! LeadModel::canAccessAll($user)) {
            $assignedUserId = (string) ($existingLead['assigned_user_id'] ?? $user['id']);
        }

        return [
            'assigned_user_id' => $assignedUserId === '' ? null : (int) $assignedUserId,
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'company' => $this->nullableString('company'),
            'email' => $this->nullableString('email'),
            'phone' => $this->nullableString('phone'),
            'source' => $this->nullableString('source'),
            'status' => trim((string) ($_POST['status'] ?? LeadModel::STATUS_NEW)),
            'priority' => trim((string) ($_POST['priority'] ?? LeadModel::PRIORITY_MEDIUM)),
            'estimated_value' => $this->nullableString('estimated_value'),
            'notes' => $this->nullableString('notes'),
        ];
    }

    /**
     * @param array<string, mixed> $lead
     * @param array<string, mixed> $user
     * @return array<string, string>
     */
    private function validateLead(array $lead, array $user): array
    {
        $errors = [];

        if ($lead['first_name'] === '') {
            $errors['first_name'] = 'First name is required.';
        } elseif (mb_strlen((string) $lead['first_name']) > 100) {
            $errors['first_name'] = 'First name may not exceed 100 characters.';
        }

        if ($lead['last_name'] === '') {
            $errors['last_name'] = 'Last name is required.';
        } elseif (mb_strlen((string) $lead['last_name']) > 100) {
            $errors['last_name'] = 'Last name may not exceed 100 characters.';
        }

        if ($lead['email'] !== null && ! filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address.';
        }

        if ($lead['phone'] !== null && mb_strlen((string) $lead['phone']) > 50) {
            $errors['phone'] = 'Phone may not exceed 50 characters.';
        }

        if ($lead['company'] !== null && mb_strlen((string) $lead['company']) > 150) {
            $errors['company'] = 'Company may not exceed 150 characters.';
        }

        if ($lead['source'] !== null && mb_strlen((string) $lead['source']) > 100) {
            $errors['source'] = 'Source may not exceed 100 characters.';
        }

        if (! LeadModel::isValidStatus((string) $lead['status'])) {
            $errors['status'] = 'Status is invalid.';
        }

        if (! LeadModel::isValidPriority((string) $lead['priority'])) {
            $errors['priority'] = 'Priority is invalid.';
        }

        if ($lead['estimated_value'] !== null && ! is_numeric($lead['estimated_value'])) {
            $errors['estimated_value'] = 'Estimated value must be numeric.';
        }

        if (LeadModel::canAccessAll($user) && $lead['assigned_user_id'] !== null) {
            $assignee = $this->users->findActiveById((int) $lead['assigned_user_id']);

            if ($assignee === null) {
                $errors['assigned_user_id'] = 'Assigned user must be active.';
            }
        }

        return $errors;
    }

    private function nullableString(string $key): ?string
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyLead(): array
    {
        return [
            'assigned_user_id' => LeadModel::canAccessAll($this->currentUser()) ? null : Auth::id(),
            'first_name' => '',
            'last_name' => '',
            'company' => '',
            'email' => '',
            'phone' => '',
            'source' => '',
            'status' => LeadModel::STATUS_NEW,
            'priority' => LeadModel::PRIORITY_MEDIUM,
            'estimated_value' => '',
            'notes' => '',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function assignees(): array
    {
        return LeadModel::canAccessAll($this->currentUser())
            ? $this->users->listActiveAssignableUsers()
            : [];
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
    private function findLeadOrFail(string $id): array
    {
        $leadId = (int) $id;

        if ($leadId <= 0) {
            $this->abort(404);
        }

        $lead = $this->leads->findById($leadId, $this->currentUser());

        if ($lead === null) {
            $this->abort(404);
        }

        return $lead;
    }
}
