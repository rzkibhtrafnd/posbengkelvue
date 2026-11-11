<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function admin(Request $request): Response
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'required_with:date_to', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'required_with:date_from', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $dashboard = $this->dashboardService->getAdminDashboard($dateFrom, $dateTo);

        return Inertia::render('Admin/Dashboard', [
            'dashboard' => $dashboard,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function cashier(Request $request): Response
    {
        $dashboard = $this->dashboardService->getCashierDashboard($request->user()->id);

        return Inertia::render('Kasir/Dashboard', compact('dashboard'));
    }
}
