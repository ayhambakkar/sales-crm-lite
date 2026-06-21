<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;

class HealthController extends Controller
{
    public function show(): never
    {
        $this->json([
            'status' => 'ok',
            'app'    => Config::get('APP_NAME', 'Sales CRM Lite'),
        ]);
    }
}
