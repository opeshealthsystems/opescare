<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\MedicalIdAccessEvent;
use App\Models\HealthIdQrToken;

class AdminPortalController extends Controller
{
    private function facilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    public function index(Request $request)
    {
        $facilityId = $this->facilityId();

        $stats = [
            'total_ids'         => Patient::whereNotNull('health_id')
                                          ->where('facility_id', $facilityId)
                                          ->where('is_demo', false)
                                          ->count(),
            'active_tokens'     => HealthIdQrToken::where('status', 'active')->count(),
            'total_access_logs' => MedicalIdAccessEvent::where('facility_id', $facilityId)->count(),
            'denied_access'     => MedicalIdAccessEvent::where('facility_id', $facilityId)
                                                       ->where('result', 'denied')
                                                       ->count(),
        ];

        $recentLogs = MedicalIdAccessEvent::with('patient')
            ->where('facility_id', $facilityId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('portals.admin.index', compact('stats', 'recentLogs'));
    }
}
