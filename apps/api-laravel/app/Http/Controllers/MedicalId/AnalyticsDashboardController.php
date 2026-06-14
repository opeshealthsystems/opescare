<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAlert;
use App\Models\DataImportBatch;
use App\Models\PatientQueueEntry;
use App\Models\WardBed;
use App\Models\WardAdmission;
use App\Modules\Analytics\Services\OperationalAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsDashboardController extends Controller
{
    public function __construct(
        private OperationalAnalyticsService $analytics,
    ) {}

    private function facilityId(): ?string
    {
        return session('active_facility_id') ?? auth()->user()?->primary_facility_id ?? null;
    }

    private function periodDates(string $period): array
    {
        $to   = Carbon::now();
        $from = match($period) {
            '7d'  => $to->copy()->subDays(7),
            '90d' => $to->copy()->subDays(90),
            '1y'  => $to->copy()->subYear(),
            default => $to->copy()->subDays(30),
        };
        return [$from, $to];
    }

    public function index(Request $request): View
    {
        $period     = in_array($request->input('period'), ['7d', '30d', '90d', '1y'])
            ? $request->input('period')
            : '30d';

        $facilityId = $this->facilityId();
        $snapshot   = $this->analytics->dashboardSnapshot($facilityId, $period);

        return view('portals.staff.analytics.index', compact('snapshot', 'period'));
    }

    // ── Queue Analytics ───────────────────────────────────────────────────────

    public function queue(Request $request): View
    {
        $period     = in_array($request->input('period'), ['7d', '30d', '90d', '1y']) ? $request->input('period') : '30d';
        $facilityId = $this->facilityId();
        [$from, $to] = $this->periodDates($period);

        $totalQueued = DB::table('patient_queue_entries')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $avgWaitMin = DB::table('patient_queue_entries')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereNotNull('called_at')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (called_at - created_at))/60) as avg_wait')
            ->value('avg_wait');

        $byStatus = DB::table('patient_queue_entries')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $byPriority = DB::table('patient_queue_entries')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('priority, COUNT(*) as cnt')
            ->groupBy('priority')
            ->orderBy('priority')
            ->pluck('cnt', 'priority')
            ->toArray();

        $dailyTrend = DB::table('patient_queue_entries')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        return view('portals.staff.analytics.queue', compact(
            'period', 'totalQueued', 'avgWaitMin', 'byStatus', 'byPriority', 'dailyTrend'
        ));
    }

    // ── Ward / Bed Analytics ──────────────────────────────────────────────────

    public function ward(Request $request): View
    {
        $period     = in_array($request->input('period'), ['7d', '30d', '90d', '1y']) ? $request->input('period') : '30d';
        $facilityId = $this->facilityId();
        [$from, $to] = $this->periodDates($period);

        $totalBeds      = DB::table('ward_beds')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->count();
        $occupiedBeds   = DB::table('ward_beds')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->where('status', 'occupied')->count();
        $occupancyRate  = $totalBeds > 0 ? round($occupiedBeds / $totalBeds * 100, 1) : 0;

        $admissions = DB::table('ward_admissions')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('admitted_at', [$from, $to])
            ->count();

        $discharges = DB::table('ward_admissions')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereNotNull('discharged_at')
            ->whereBetween('discharged_at', [$from, $to])
            ->count();

        $avgLosHours = DB::table('ward_admissions')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereNotNull('discharged_at')
            ->whereBetween('admitted_at', [$from, $to])
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (discharged_at - admitted_at))/3600) as avg_los')
            ->value('avg_los');

        $byWard = DB::table('ward_beds')
            ->join('wards', 'wards.id', '=', 'ward_beds.ward_id')
            ->when($facilityId, fn ($q) => $q->where('ward_beds.facility_id', $facilityId))
            ->selectRaw('wards.name as ward_name, COUNT(*) as total_beds,
                SUM(CASE WHEN ward_beds.status = "occupied" THEN 1 ELSE 0 END) as occupied')
            ->groupBy('wards.id', 'wards.name')
            ->get()
            ->toArray();

        return view('portals.staff.analytics.ward', compact(
            'period', 'totalBeds', 'occupiedBeds', 'occupancyRate',
            'admissions', 'discharges', 'avgLosHours', 'byWard'
        ));
    }

    // ── Financial Analytics ───────────────────────────────────────────────────

    public function financial(Request $request): View
    {
        $period     = in_array($request->input('period'), ['7d', '30d', '90d', '1y']) ? $request->input('period') : '30d';
        $facilityId = $this->facilityId();
        [$from, $to] = $this->periodDates($period);

        $revenue   = $this->analytics->revenueSummary($facilityId, $from, $to);
        $revTrend  = $this->analytics->revenueTrend($facilityId, $from, $to);

        $byPaymentMode = DB::table('invoices')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('payment_mode, SUM(total_amount) as total, COUNT(*) as cnt')
            ->groupBy('payment_mode')
            ->get()
            ->toArray();

        $outstandingAmount = DB::table('invoices')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');

        $outstandingCount = DB::table('invoices')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereIn('status', ['pending', 'partial'])
            ->count();

        $topServices = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->when($facilityId, fn ($q) => $q->where('invoices.facility_id', $facilityId))
            ->whereBetween('invoices.created_at', [$from, $to])
            ->selectRaw('invoice_items.description, SUM(invoice_items.unit_price * invoice_items.quantity) as revenue, COUNT(*) as cnt')
            ->groupBy('invoice_items.description')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->toArray();

        return view('portals.staff.analytics.financial', compact(
            'period', 'revenue', 'revTrend', 'byPaymentMode',
            'outstandingAmount', 'outstandingCount', 'topServices'
        ));
    }

    // ── Data Quality Analytics ────────────────────────────────────────────────

    public function dataQuality(Request $request): View
    {
        $facilityId = $this->facilityId();

        // Patient record completeness
        $totalPatients    = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->count();
        $withPhone        = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('phone')->count();
        $withAddress      = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('address')->count();
        $withDob          = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('date_of_birth')->count();
        $withNhis         = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('nhis_number')->count();
        $withNextOfKin    = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('next_of_kin_name')->count();

        // Import history
        $importStats = DB::table('data_import_batches')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->selectRaw('status, COUNT(*) as cnt, SUM(total_records) as records')
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();

        $recentImports = DB::table('data_import_batches')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        // CDSS alert distribution
        $alertsByType = ClinicalAlert::where('facility_id', $facilityId)
            ->whereDate('triggered_at', '>=', now()->subDays(30))
            ->selectRaw('alert_type, COUNT(*) as cnt')
            ->groupBy('alert_type')
            ->pluck('cnt', 'alert_type')
            ->toArray();

        $overrideRate = ClinicalAlert::where('facility_id', $facilityId)
            ->whereDate('triggered_at', '>=', now()->subDays(30))
            ->selectRaw(
                'ROUND(SUM(CASE WHEN status = "overridden" THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as rate'
            )
            ->value('rate');

        return view('portals.staff.analytics.data_quality', compact(
            'totalPatients', 'withPhone', 'withAddress', 'withDob',
            'withNhis', 'withNextOfKin', 'importStats', 'recentImports',
            'alertsByType', 'overrideRate'
        ));
    }
}
