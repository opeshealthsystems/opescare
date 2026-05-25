<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\OperationalAnalyticsService;
use App\Modules\Analytics\Services\ReportExportService;
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
        private ReportExportService         $exports
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
}
