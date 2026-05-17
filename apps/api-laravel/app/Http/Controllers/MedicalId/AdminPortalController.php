<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\MedicalIdAccessEvent;
use App\Models\HealthIdQrToken;

class AdminPortalController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_ids' => Patient::whereNotNull('health_id')->count(),
            'active_tokens' => HealthIdQrToken::where('status', 'active')->count(),
            'total_access_logs' => MedicalIdAccessEvent::count(),
            'denied_access' => MedicalIdAccessEvent::where('result', 'denied')->count(),
        ];

        $recentLogs = MedicalIdAccessEvent::with('patient')->orderBy('created_at', 'desc')->limit(10)->get();

        return view('portals.admin.index', compact('stats', 'recentLogs'));
    }
}
