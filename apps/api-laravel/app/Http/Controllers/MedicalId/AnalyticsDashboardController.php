<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Modules\Analytics\Services\OperationalAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsDashboardController extends Controller
{
    public function __construct(
        private OperationalAnalyticsService $analytics,
    ) {}

    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    public function index(Request $request): View
    {
        $period     = in_array($request->input('period'), ['7d', '30d', '90d', '1y'])
            ? $request->input('period')
            : '30d';

        $facilityId = $this->demoFacilityId();
        $snapshot   = $this->analytics->dashboardSnapshot($facilityId, $period);

        return view('portals.staff.analytics.index', compact('snapshot', 'period'));
    }
}
