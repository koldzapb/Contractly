<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    /**
     * Get dashboard data for the authenticated user.
     */
    public function __invoke(Request $request): DashboardResource
    {
        $data = $this->dashboardService->getDashboardData($request->user());

        return DashboardResource::make($data);
    }
}
