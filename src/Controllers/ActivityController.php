<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ActivityModel;
use App\Models\UserModel;

class ActivityController extends Controller
{
    private ActivityModel $activities;
    private UserModel $users;

    public function __construct()
    {
        $this->activities = new ActivityModel();
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $user = $this->currentUser();
        $filters = ActivityModel::normalizeFilters($_GET, $user);
        $pagination = ActivityModel::paginationFromQuery($_GET);
        $total = $this->activities->count($user, $filters);
        $totalPages = max(1, (int) ceil($total / $pagination['per_page']));

        if ($pagination['page'] > $totalPages) {
            $pagination['page'] = $totalPages;
            $pagination['offset'] = ($totalPages - 1) * $pagination['per_page'];
        }

        $this->render('activities/index', [
            'title' => 'Activity Log',
            'activities' => $this->activities->list($user, $filters, $pagination),
            'currentUser' => $user,
            'filters' => $filters,
            'pagination' => array_merge($pagination, [
                'total' => $total,
                'total_pages' => $totalPages,
            ]),
            'entityTypes' => ActivityModel::entityTypes(),
            'actions' => ActivityModel::actions(),
            'users' => ActivityModel::canAccessAll($user) ? $this->users->listAll() : [],
            'hasActiveFilters' => ActivityModel::hasActiveFilters($filters, $user),
        ]);
    }

    public function show(string $id): void
    {
        $activityId = (int) $id;

        if ($activityId <= 0) {
            $this->abort(404);
        }

        $activity = $this->activities->findById($activityId, $this->currentUser());

        if ($activity === null) {
            $this->abort(404);
        }

        $this->render('activities/show', [
            'title' => 'Activity Details',
            'activity' => $activity,
            'currentUser' => $this->currentUser(),
        ]);
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
}
