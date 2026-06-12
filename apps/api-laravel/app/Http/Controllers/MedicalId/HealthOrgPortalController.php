<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthOrgPortalController extends Controller
{
    private function facilityId(): ?string
    {
        return session('active_facility_id')
            ?? auth()->user()?->primary_facility_id
            ?? Facility::value('id');
    }

    // ------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------

    public function dashboard()
    {
        $stats = [
            'patients'      => Patient::count(),
            'facilities'    => Facility::where('status', 'active')->count(),
            'reports_draft' => $this->countPublicHealthReports('draft'),
            'reports_sent'  => $this->countPublicHealthReports('submitted'),
        ];

        return view('portals.healthorg.dashboard', compact('stats'));
    }

    // ------------------------------------------------------------------
    // Programs placeholder — lists facilities as program nodes
    // ------------------------------------------------------------------

    public function programs()
    {
        $facilities = Facility::whereIn('type', ['health_organization', 'clinic', 'hospital'])
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate(20);

        return view('portals.healthorg.programs', compact('facilities'));
    }

    // ------------------------------------------------------------------
    // Outreach — mobile clinic / outreach type facilities
    // ------------------------------------------------------------------

    public function outreach()
    {
        $sites = Facility::whereIn('type', ['health_organization', 'clinic'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('portals.healthorg.outreach', compact('sites'));
    }

    // ------------------------------------------------------------------
    // Public Health Reports — proxy to the API data
    // ------------------------------------------------------------------

    public function reports()
    {
        $reports = collect();

        if (DB::getSchemaBuilder()->hasTable('public_health_reports')) {
            $reports = DB::table('public_health_reports')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        return view('portals.healthorg.reports', compact('reports'));
    }

    // ------------------------------------------------------------------
    // Signals — outbreak / anomaly signals
    // ------------------------------------------------------------------

    public function signals()
    {
        $signals = collect();

        if (DB::getSchemaBuilder()->hasTable('public_health_signals')) {
            $signals = DB::table('public_health_signals')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        return view('portals.healthorg.signals', compact('signals'));
    }

    // ------------------------------------------------------------------

    private function countPublicHealthReports(string $status): int
    {
        if (!DB::getSchemaBuilder()->hasTable('public_health_reports')) {
            return 0;
        }
        return DB::table('public_health_reports')->where('status', $status)->count();
    }
}
