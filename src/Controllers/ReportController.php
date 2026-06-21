<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ActivityModel;
use App\Models\ReportModel;
use App\Services\ActivityLogger;

class ReportController extends Controller
{
    private ReportModel $reports;
    private ActivityLogger $activityLogger;

    public function __construct()
    {
        $this->reports = new ReportModel();
        $this->activityLogger = new ActivityLogger();
    }

    public function index(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->abort(403);
        }

        $this->activityLogger->logSystemAction(
            ActivityModel::ACTION_VIEWED_REPORT,
            'User viewed reports dashboard.',
            [
                'scope' => ReportModel::canAccessGlobal($user) ? 'global' : 'assigned',
            ],
            (int) $user['id']
        );

        $this->render('reports/index', array_merge([
            'title' => 'Reports',
            'currentUser' => $user,
        ], $this->reports->overview($user)));
    }
}
