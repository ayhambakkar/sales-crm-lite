<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DashboardModel;

class DashboardController extends Controller
{
    private DashboardModel $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
    }

    public function index(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->abort(403);
        }

        $this->render('dashboard/index', array_merge([
            'title' => 'Dashboard',
            'currentUser' => $user,
        ], $this->dashboard->overview($user)));
    }
}
