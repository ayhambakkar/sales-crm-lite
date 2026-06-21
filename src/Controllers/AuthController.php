<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Session;
use App\Models\ActivityModel;
use App\Models\FailedLoginModel;
use App\Models\UserModel;
use App\Services\ActivityLogger;
use Throwable;

class AuthController extends Controller
{
    private const PASSWORD_ALGO = PASSWORD_BCRYPT;
    private const PASSWORD_OPTIONS = ['cost' => 12];

    private UserModel $users;
    private FailedLoginModel $failedLogins;
    private ActivityLogger $activityLogger;

    public function __construct()
    {
        $this->users        = new UserModel();
        $this->failedLogins = new FailedLoginModel();
        $this->activityLogger = new ActivityLogger();
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
        $email    = substr(trim(strtolower((string) ($_POST['email'] ?? ''))), 0, 255);
        $password = $_POST['password'] ?? '';
        $ip       = $this->clientIp();

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email and password are required.');
            Session::flash('_old_email', $email);
            $this->redirect('/login');
        }

        try {
            if ($this->failedLogins->isLocked($email, $ip)) {
                Session::flash('error', 'Too many login attempts. Please try again in 15 minutes.');
                Session::flash('_old_email', $email);
                $this->redirect('/login');
            }

            $user = $this->users->findByEmail($email);
        } catch (Throwable $exception) {
            $this->handleAuthException($exception);
        }

        // Same message for "user not found" and "wrong password" — prevents user enumeration
        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            $isLocked = false;

            try {
                $this->failedLogins->recordFailure($email, $ip);
                $isLocked = $this->failedLogins->isLocked($email, $ip);
            } catch (Throwable $exception) {
                $this->handleAuthException($exception);
            }

            $message = $isLocked
                ? 'Too many login attempts. Please try again in 15 minutes.'
                : 'Invalid email or password.';

            Session::flash('error', $message);
            Session::flash('_old_email', $email);
            $this->redirect('/login');
        }

        if ((int) $user['is_active'] === 0) {
            Session::flash('error', 'This account has been deactivated. Contact your administrator.');
            $this->redirect('/login');
        }

        try {
            $this->failedLogins->clearFailures($email, $ip);

            if (password_needs_rehash($user['password_hash'], self::PASSWORD_ALGO, self::PASSWORD_OPTIONS)) {
                $newHash = password_hash($password, self::PASSWORD_ALGO, self::PASSWORD_OPTIONS);
                $this->users->updatePasswordHash((int) $user['id'], $newHash);
            }

            $this->users->updateLastLoginAt((int) $user['id']);
        } catch (Throwable $exception) {
            $this->handleAuthException($exception);
        }

        $this->activityLogger->logAuthAction(
            ActivityModel::ACTION_LOGGED_IN,
            (int) $user['id'],
            'User logged in.',
            [
                'email' => $user['email'],
                'ip_address' => $ip,
            ]
        );

        Auth::login($user);
        $this->redirect('/');
    }

    private function clientIp(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
    }

    private function handleAuthException(Throwable $exception): never
    {
        if (Config::get('APP_ENV') === 'development') {
            throw $exception;
        }

        Logger::exception($exception);
        Session::flash('error', 'A system error occurred. Please try again later.');
        $this->redirect('/login');
    }

    /** POST /logout */
    public function logout(): void
    {
        $user = Auth::user();

        if ($user !== null) {
            $this->activityLogger->logAuthAction(
                ActivityModel::ACTION_LOGGED_OUT,
                (int) $user['id'],
                'User logged out.',
                ['email' => $user['email'] ?? null]
            );
        }

        Auth::logout();
        $this->redirect('/login');
    }
}
