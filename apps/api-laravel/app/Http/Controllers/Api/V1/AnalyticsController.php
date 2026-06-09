<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\OperationalAnalyticsService;
use App\Modules\Analytics\Services\ProductAnalyticsService;
use App\Modules\Analytics\Services\ReportExportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AnalyticsController — Analytics & Reporting API.
 *
 * PRIVACY: All analytics endpoints return aggregated data only.
 * No patient-level data may be included in analytics responses
 * unless the requester has explicit permission with audit log.
 *
 * Exports are generated asynchronously — use the export status endpoint
 * to poll for readiness before downloading.
 */
class AnalyticsController extends Controller
{
    public function __construct(
        private OperationalAnalyticsService $analytics,
        private ReportExportService         $exports,
        private ProductAnalyticsService     $product
    ) {}

    public function facilityDashboard(Request $request, string $facilityId): JsonResponse
    {
        $clientFacilityId = $request->attributes->get('facility_id');
        if ($clientFacilityId && $clientFacilityId !== $facilityId) {
            return response()->json([
                'error'   => 'forbidden',
                'message' => 'You do not have access to analytics for this facility.',
            ], 403);
        }

        return response()->json(
            $this->analytics->getFacilityDashboard($facilityId, $request->all())
        );
    }

    public function appointmentStats(Request $request): JsonResponse
    {
        return response()->json(
            $this->analytics->getAppointmentStats($request->all())
        );
    }

    public function queueStats(Request $request): JsonResponse
    {
        return response()->json(
            $this->analytics->getQueueStats($request->all())
        );
    }

    public function billingStats(Request $request): JsonResponse
    {
        return response()->json(
            $this->analytics->getBillingStats($request->all())
        );
    }

    // ── Report Exports ─────────────────────────────────────────────────────

    public function requestExport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'report_type'  => ['required', 'string'],
            'format'       => ['required', 'in:csv,xlsx,pdf,json'],
            'facility_id'  => ['nullable', 'uuid'],
            'from'         => ['nullable', 'date'],
            'to'           => ['nullable', 'date'],
        ]);

        $export = $this->exports->requestExport(
            $validated['report_type'],
            $validated['format'],
            $request->user()->id,
            $validated,
            $validated['facility_id'] ?? null
        );

        return response()->json(['export_id' => $export->id, 'status' => $export->status], 202);
    }

    public function exportStatus(string $exportId): JsonResponse
    {
        $export = \App\Models\ReportExport::findOrFail($exportId);
        return response()->json([
            'export_id'    => $export->id,
            'status'       => $export->status,
            'download_url' => $export->isExpired() ? null : $export->download_url,
        ]);
    }

    // ── KPI / Metric Snapshots ─────────────────────────────────────────────

    /**
     * Latest daily KPI snapshots for a facility.
     * Used by the KPI dashboard widget — one value per metric for the most recent date.
     *
     * ?category=clinical|financial|operational|quality  (optional filter)
     */
    public function kpiSnapshots(Request $request, string $facilityId): JsonResponse
    {
        $clientFacilityId = $request->attributes->get('facility_id');
        if ($clientFacilityId && $clientFacilityId !== $facilityId) {
            return response()->json(['error' => 'forbidden', 'message' => 'Access denied for this facility.'], 403);
        }

        $snapshots = $this->product->latestDailySnapshots(
            $facilityId,
            $request->input('category')
        );

        return response()->json([
            'facility_id' => $facilityId,
            'data'        => $snapshots,
        ]);
    }

    /**
     * Metric trend data for a specific metric over a date range.
     * Returns a map of date → value for charting.
     *
     * ?metric_slug=patient_registrations&from=2026-05-01&to=2026-06-07
     */
    public function metricTrend(Request $request, string $facilityId): JsonResponse
    {
        $validated = $request->validate([
            'metric_slug' => ['required', 'string'],
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
        ]);

        $clientFacilityId = $request->attributes->get('facility_id');
        if ($clientFacilityId && $clientFacilityId !== $facilityId) {
            return response()->json(['error' => 'forbidden', 'message' => 'Access denied for this facility.'], 403);
        }

        $trend = $this->product->metricTrend(
            $facilityId,
            $validated['metric_slug'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to'])
        );

        return response()->json([
            'facility_id' => $facilityId,
            'metric_slug' => $validated['metric_slug'],
            'from'        => $validated['from'],
            'to'          => $validated['to'],
            'data'        => $trend,
        ]);
    }

    /**
     * Platform-wide summary for super_admin / hospital_director dashboards.
     * Returns aggregate counts across all facilities for a given date.
     *
     * ?date=2026-06-07  (defaults to today)
     */
    public function platformSummary(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = Carbon::parse($request->input('date', now()->toDateString()));

        return response()->json(
            $this->product->platformSummary($date)
        );
    }
}
