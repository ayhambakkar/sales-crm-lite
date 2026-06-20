<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\UserModel;

class UserController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $this->render('users/index', [
            'title' => 'Users',
            'users' => $this->users->listAll(),
            'currentUserId' => Auth::id(),
        ]);
    }

    public function create(): void
    {
        $this->render('users/create', [
            'title' => 'Create User',
            'errors' => [],
            'user' => $this->emptyUser(),
            'roles' => UserModel::roles(),
        ]);
    }

    public function store(): void
    {
        $input = $this->userInput();
        $input['password'] = (string) ($_POST['password'] ?? '');

        $errors = $this->validateUser($input, true);

        if ($errors !== []) {
            $this->render('users/create', [
                'title' => 'Create User',
                'errors' => $errors,
                'user' => $input,
                'roles' => UserModel::roles(),
            ]);
            return;
        }

        $this->users->createUser($input);

        Session::flash('success', 'User created successfully.');
        $this->redirect('/users');
    }

    public function edit(string $id): void
    {
        $user = $this->findUserOrFail($id);

        $this->render('users/edit', [
            'title' => 'Edit User',
            'errors' => [],
            'passwordErrors' => [],
            'user' => $user,
            'roles' => UserModel::roles(),
            'currentUserId' => Auth::id(),
        ]);
    }

    public function update(string $id): void
    {
        $user = $this->findUserOrFail($id);
        $userId = (int) $user['id'];
        $input = $this->userInput();

        $errors = $this->validateUser($input, false, $userId);

        if (UserModel::wouldChangeLastActiveAdminToSalesRep(
            $user,
            $input['role'],
            $this->users->countActiveAdmins()
        )) {
            $errors['role'] = 'The last active admin cannot be changed to a Sales Rep.';
        }

        if ($errors !== []) {
            $this->render('users/edit', [
                'title' => 'Edit User',
                'errors' => $errors,
                'passwordErrors' => [],
                'user' => array_merge($user, $input),
                'roles' => UserModel::roles(),
                'currentUserId' => Auth::id(),
            ]);
            return;
        }

        $this->users->updateProfile($userId, $input);

        Session::flash('success', 'User updated successfully.');
        $this->redirect('/users');
    }

    public function deactivate(string $id): void
    {
        $user = $this->findUserOrFail($id);
        $userId = (int) $user['id'];

        if ($userId === Auth::id()) {
            Session::flash('error', 'You cannot deactivate your own account.');
            $this->redirect('/users');
        }

        if (UserModel::wouldDeactivateLastActiveAdmin($user, $this->users->countActiveAdmins())) {
            Session::flash('error', 'The last active admin cannot be deactivated.');
            $this->redirect('/users');
        }

        $this->users->deactivate($userId);

        Session::flash('success', 'User deactivated successfully.');
        $this->redirect('/users');
    }

    public function activate(string $id): void
    {
        $user = $this->findUserOrFail($id);

        $this->users->activate((int) $user['id']);

        Session::flash('success', 'User activated successfully.');
        $this->redirect('/users');
    }

    public function resetPassword(string $id): void
    {
        $user = $this->findUserOrFail($id);
        $password = (string) ($_POST['password'] ?? '');
        $passwordErrors = [];

        if ($password === '') {
            $passwordErrors['password'] = 'Password is required.';
        } elseif (mb_strlen($password) < 8) {
            $passwordErrors['password'] = 'Password must be at least 8 characters.';
        }

        if ($passwordErrors !== []) {
            $this->render('users/edit', [
                'title' => 'Edit User',
                'errors' => [],
                'passwordErrors' => $passwordErrors,
                'user' => $user,
                'roles' => UserModel::roles(),
                'currentUserId' => Auth::id(),
            ]);
            return;
        }

        $this->users->resetPasswordHash((int) $user['id'], UserModel::hashPassword($password));

        Session::flash('success', 'Password reset successfully.');
        $this->redirect('/users/' . (int) $user['id'] . '/edit');
    }

    /**
     * @return array{first_name: string, last_name: string, email: string, role: string}
     */
    private function userInput(): array
    {
        return [
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'email' => substr(trim(strtolower((string) ($_POST['email'] ?? ''))), 0, 255),
            'role' => trim((string) ($_POST['role'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    private function validateUser(array $input, bool $creating, ?int $excludeId = null): array
    {
        $errors = [];

        if ($input['first_name'] === '') {
            $errors['first_name'] = 'First name is required.';
        } elseif (mb_strlen((string) $input['first_name']) > 100) {
            $errors['first_name'] = 'First name may not exceed 100 characters.';
        }

        if ($input['last_name'] === '') {
            $errors['last_name'] = 'Last name is required.';
        } elseif (mb_strlen((string) $input['last_name']) > 100) {
            $errors['last_name'] = 'Last name may not exceed 100 characters.';
        }

        if ($input['email'] === '') {
            $errors['email'] = 'Email is required.';
        } elseif (! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address.';
        } elseif ($this->users->emailExists((string) $input['email'], $excludeId)) {
            $errors['email'] = 'A user with this email already exists.';
        }

        if ($input['role'] === '') {
            $errors['role'] = 'Role is required.';
        } elseif (! UserModel::isValidRole((string) $input['role'])) {
            $errors['role'] = 'Role must be Admin or Sales Rep.';
        }

        if ($creating) {
            $password = (string) ($input['password'] ?? '');

            if ($password === '') {
                $errors['password'] = 'Password is required.';
            } elseif (mb_strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            }
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    private function emptyUser(): array
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'role' => UserModel::ROLE_SALES_REP,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function findUserOrFail(string $id): array
    {
        $userId = (int) $id;

        if ($userId <= 0) {
            $this->abort(404);
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->abort(404);
        }

        return $user;
    }
}
