<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\CustomerModel;
use App\Models\FollowUpModel;
use App\Models\UserModel;

class CustomerController extends Controller
{
    private CustomerModel $customers;
    private FollowUpModel $followUps;
    private UserModel $users;

    public function __construct()
    {
        $this->customers = new CustomerModel();
        $this->followUps = new FollowUpModel();
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $user = $this->currentUser();
        $filters = CustomerModel::normalizeFilters($_GET, $user);
        $sort = CustomerModel::normalizeSort($_GET);
        $pagination = CustomerModel::paginationFromQuery($_GET);
        $total = $this->customers->count($user, $filters);
        $totalPages = max(1, (int) ceil($total / $pagination['per_page']));

        if ($pagination['page'] > $totalPages) {
            $pagination['page'] = $totalPages;
            $pagination['offset'] = ($totalPages - 1) * $pagination['per_page'];
        }

        $this->render('customers/index', [
            'title' => 'Customers',
            'customers' => $this->customers->list($user, $filters, $sort, $pagination),
            'currentUser' => $user,
            'filters' => $filters,
            'sort' => $sort,
            'pagination' => array_merge($pagination, [
                'total' => $total,
                'total_pages' => $totalPages,
            ]),
            'statuses' => CustomerModel::statuses(),
            'assignees' => $this->assignees(),
            'hasActiveFilters' => CustomerModel::hasActiveFilters($filters, $user),
        ]);
    }

    public function create(): void
    {
        $this->render('customers/create', [
            'title' => 'Create Customer',
            'errors' => [],
            'customer' => $this->emptyCustomer(),
            'statuses' => CustomerModel::statuses(),
            'assignees' => $this->assignees(),
            'currentUser' => $this->currentUser(),
        ]);
    }

    public function store(): void
    {
        $user = $this->currentUser();
        $input = $this->customerInput($user);
        $errors = $this->validateCustomer($input, $user);

        if ($errors !== []) {
            $this->render('customers/create', [
                'title' => 'Create Customer',
                'errors' => $errors,
                'customer' => $input,
                'statuses' => CustomerModel::statuses(),
                'assignees' => $this->assignees(),
                'currentUser' => $user,
            ]);
            return;
        }

        $customerId = $this->customers->create($input);

        Session::flash('success', 'Customer created successfully.');
        $this->redirect('/customers/' . $customerId);
    }

    public function show(string $id): void
    {
        $customer = $this->findCustomerOrFail($id);
        $user = $this->currentUser();

        $this->render('customers/show', [
            'title' => 'Customer Details',
            'customer' => $customer,
            'currentUser' => $user,
            'followUps' => $this->followUps->listForCustomer((int) $customer['id'], $user),
        ]);
    }

    public function edit(string $id): void
    {
        $customer = $this->findCustomerOrFail($id);

        $this->render('customers/edit', [
            'title' => 'Edit Customer',
            'errors' => [],
            'customer' => $customer,
            'statuses' => CustomerModel::statuses(),
            'assignees' => $this->assignees(),
            'currentUser' => $this->currentUser(),
        ]);
    }

    public function update(string $id): void
    {
        $customer = $this->findCustomerOrFail($id);
        $user = $this->currentUser();
        $input = $this->customerInput($user, $customer);
        $errors = $this->validateCustomer($input, $user);

        if ($errors !== []) {
            $this->render('customers/edit', [
                'title' => 'Edit Customer',
                'errors' => $errors,
                'customer' => array_merge($customer, $input),
                'statuses' => CustomerModel::statuses(),
                'assignees' => $this->assignees(),
                'currentUser' => $user,
            ]);
            return;
        }

        $this->customers->update((int) $customer['id'], $input);

        Session::flash('success', 'Customer updated successfully.');
        $this->redirect('/customers/' . (int) $customer['id']);
    }

    public function destroy(string $id): void
    {
        $customer = $this->findCustomerOrFail($id);

        $this->customers->delete((int) $customer['id'], $this->currentUser());

        Session::flash('success', 'Customer deleted successfully.');
        $this->redirect('/customers');
    }

    /**
     * @param array<string, mixed>|null $existingCustomer
     * @return array<string, mixed>
     */
    private function customerInput(array $user, ?array $existingCustomer = null): array
    {
        $assignedUserId = trim((string) ($_POST['assigned_user_id'] ?? ''));

        if (! CustomerModel::canAccessAll($user)) {
            $assignedUserId = (string) ($existingCustomer['assigned_user_id'] ?? $user['id']);
        }

        return [
            'assigned_user_id' => $assignedUserId === '' ? null : (int) $assignedUserId,
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'company' => $this->nullableString('company'),
            'email' => $this->nullableString('email'),
            'phone' => $this->nullableString('phone'),
            'address' => $this->nullableString('address'),
            'city' => $this->nullableString('city'),
            'postal_code' => $this->nullableString('postal_code'),
            'country' => $this->nullableString('country'),
            'customer_status' => trim((string) ($_POST['customer_status'] ?? CustomerModel::STATUS_ACTIVE)),
            'notes' => $this->nullableString('notes'),
        ];
    }

    /**
     * @param array<string, mixed> $customer
     * @param array<string, mixed> $user
     * @return array<string, string>
     */
    private function validateCustomer(array $customer, array $user): array
    {
        $errors = [];

        if ($customer['first_name'] === '') {
            $errors['first_name'] = 'First name is required.';
        } elseif (mb_strlen((string) $customer['first_name']) > 100) {
            $errors['first_name'] = 'First name may not exceed 100 characters.';
        }

        if ($customer['last_name'] === '') {
            $errors['last_name'] = 'Last name is required.';
        } elseif (mb_strlen((string) $customer['last_name']) > 100) {
            $errors['last_name'] = 'Last name may not exceed 100 characters.';
        }

        if ($customer['email'] !== null && ! filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address.';
        }

        if ($customer['phone'] !== null && mb_strlen((string) $customer['phone']) > 50) {
            $errors['phone'] = 'Phone may not exceed 50 characters.';
        }

        if ($customer['company'] !== null && mb_strlen((string) $customer['company']) > 150) {
            $errors['company'] = 'Company may not exceed 150 characters.';
        }

        if ($customer['address'] !== null && mb_strlen((string) $customer['address']) > 255) {
            $errors['address'] = 'Address may not exceed 255 characters.';
        }

        if ($customer['city'] !== null && mb_strlen((string) $customer['city']) > 100) {
            $errors['city'] = 'City may not exceed 100 characters.';
        }

        if ($customer['postal_code'] !== null && mb_strlen((string) $customer['postal_code']) > 30) {
            $errors['postal_code'] = 'Postal code may not exceed 30 characters.';
        }

        if ($customer['country'] !== null && mb_strlen((string) $customer['country']) > 100) {
            $errors['country'] = 'Country may not exceed 100 characters.';
        }

        if (! CustomerModel::isValidStatus((string) $customer['customer_status'])) {
            $errors['customer_status'] = 'Customer status is invalid.';
        }

        if (CustomerModel::canAccessAll($user) && $customer['assigned_user_id'] !== null) {
            $assignee = $this->users->findActiveById((int) $customer['assigned_user_id']);

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
    private function emptyCustomer(): array
    {
        return [
            'assigned_user_id' => CustomerModel::canAccessAll($this->currentUser()) ? null : Auth::id(),
            'first_name' => '',
            'last_name' => '',
            'company' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'postal_code' => '',
            'country' => '',
            'customer_status' => CustomerModel::STATUS_ACTIVE,
            'notes' => '',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function assignees(): array
    {
        return CustomerModel::canAccessAll($this->currentUser())
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
    private function findCustomerOrFail(string $id): array
    {
        $customerId = (int) $id;

        if ($customerId <= 0) {
            $this->abort(404);
        }

        $customer = $this->customers->findById($customerId, $this->currentUser());

        if ($customer === null) {
            $this->abort(404);
        }

        return $customer;
    }
}
