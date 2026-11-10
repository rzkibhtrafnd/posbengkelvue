<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivityLogRequest;
use App\Services\ActivityLogService;
use App\Support\PaginationOptions;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    public function index(ActivityLogRequest $request): Response
    {
        $filters = $request->validated();
        $perPage = PaginationOptions::perPage($request);
        $filters['per_page'] = $perPage;
        $activityLogs = $this->activityLogService->getFilteredActivityLogs($filters, $perPage);

        return Inertia::render('Admin/ActivityLogs/Index', compact('activityLogs', 'filters'));
    }
}
