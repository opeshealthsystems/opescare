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

        $totalQueued = DB::table('queue_tickets')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $avgWaitMin = DB::table('queue_tickets')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereNotNull('called_at')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (called_at - checked_in_at))/60) as avg_wait')
            ->value('avg_wait');

        $byStatus = DB::table('queue_tickets')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $byPriority = DB::table('queue_tickets')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('priority_level, COUNT(*) as cnt')
            ->groupBy('priority_level')
            ->orderBy('priority_level')
            ->pluck('cnt', 'priority_level')
            ->toArray();

        $dailyTrend = DB::table('queue_tickets')
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

        // beds have no facility_id of their own — they are scoped via their ward.
        $bedsByFacility = fn ($q) => $q->join('wards', 'wards.id', '=', 'beds.ward_id')
            ->when($facilityId, fn ($qq) => $qq->where('wards.facility_id', $facilityId));

        $totalBeds      = DB::table('beds')->tap($bedsByFacility)->count();
        $occupiedBeds   = DB::table('beds')->tap($bedsByFacility)->where('beds.status', 'occupied')->count();
        $occupancyRate  = $totalBeds > 0 ? round($occupiedBeds / $totalBeds * 100, 1) : 0;

        $admissions = DB::table('admissions')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereBetween('admitted_at', [$from, $to])
            ->count();

        $discharges = DB::table('admissions')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereNotNull('discharged_at')
            ->whereBetween('discharged_at', [$from, $to])
            ->count();

        $avgLosHours = DB::table('admissions')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereNotNull('discharged_at')
            ->whereBetween('admitted_at', [$from, $to])
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (discharged_at - admitted_at))/3600) as avg_los')
            ->value('avg_los');

        $byWard = DB::table('beds')
            ->join('wards', 'wards.id', '=', 'beds.ward_id')
            ->when($facilityId, fn ($q) => $q->where('wards.facility_id', $facilityId))
            ->selectRaw("wards.name as ward_name, COUNT(*) as total_beds,
                SUM(CASE WHEN beds.status = 'occupied' THEN 1 ELSE 0 END) as occupied")
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

        // Payment mode breakdown lives on the payments table (invoices have no payment_mode).
        $byPaymentMode = DB::table('payments')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->where('status', 'completed')
            ->whereBetween('confirmed_at', [$from, $to])
            ->selectRaw('method as payment_mode, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('method')
            ->get()
            ->toArray();

        $outstandingAmount = DB::table('invoices')
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_amount');

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
        $withPhone        = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('phone_number')->count();
        $withAddress      = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('address')->count();
        $withDob          = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('date_of_birth')->count();
        // National health-insurance identifier on this schema is cnamgs_id; next-of-kin is stored in emergency_contact.
        $withNhis         = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('cnamgs_id')->count();
        $withNextOfKin    = DB::table('patients')->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))->whereNotNull('emergency_contact')->count();

        // Import history (import_batches has no facility_id — it links via its import job).
        $importStats = DB::table('import_batches')
            ->selectRaw('status, COUNT(*) as cnt, SUM(total_rows) as records')
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();

        $recentImports = DB::table('import_batches')
            ->selectRaw('*, total_rows as total_records')
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
                "ROUND(SUM(CASE WHEN status = 'overridden' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as rate"
            )
            ->value('rate');

        return view('portals.staff.analytics.data_quality', compact(
            'totalPatients', 'withPhone', 'withAddress', 'withDob',
            'withNhis', 'withNextOfKin', 'importStats', 'recentImports',
            'alertsByType', 'overrideRate'
        ));
    }
}
