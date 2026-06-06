<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\UserModel;
use PDOException;

class AuthController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    /** GET /login */
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'title' => 'Sign In',
            'error' => Session::getFlash('error'),
            'info'  => Session::getFlash('info'),
            'email' => Session::getFlash('_old_email') ?? '',
        ], 'layouts/guest');
    }

    /** POST /login */
    public function login(): void
    {
        $email    = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email and password are required.');
            Session::flash('_old_email', $email);
            $this->redirect('/login');
        }

        try {
            $user = $this->users->findByEmail($email);
        } catch (PDOException) {
            Session::flash('error', 'A system error occurred. Please try again later.');
            $this->redirect('/login');
        }

        // Same message for "user not found" and "wrong password" — prevents user enumeration
        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'Invalid email or password.');
            Session::flash('_old_email', $email);
            $this->redirect('/login');
        }

        if ((int) $user['is_active'] === 0) {
            Session::flash('error', 'This account has been deactivated. Contact your administrator.');
            $this->redirect('/login');
        }

        Auth::login($user);
        $this->redirect('/');
    }

    /** POST /logout */
    public function logout(): void
    {
        Auth::logout();
        Session::start();
        Session::flash('info', 'You have been signed out.');
        $this->redirect('/login');
    }
}
