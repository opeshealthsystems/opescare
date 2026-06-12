<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\ConsentRequest;
use App\Models\CorrectionRequest;
use App\Models\DataExportRequest;
use App\Modules\Governance\Services\ConsentService;
use App\Modules\Governance\Services\CorrectionRequestService;
use App\Modules\Governance\Services\DataExportService;
use Illuminate\Http\Request;

/**
 * MobileGovernanceController
 *
 * Handles patient-facing governance actions (consent, data corrections, exports)
 * from the mobile app. The mobile app authenticates via AuthenticateMobilePatient
 * middleware which injects patient_id and user_id into request attributes.
 *
 * SECURITY:
 *  - user_id / patient_id MUST come from $request->attributes (set by middleware).
 *    NEVER from request body, query string, or hardcoded fallback.
 *  - Hardcoded UUID fallbacks '00000000-...' have been removed entirely.
 *    Missing identity → 401 IDENTITY_UNRESOLVABLE.
 */
class MobileGovernanceController extends Controller
{
    public function __construct(
        private readonly ConsentService $consentService,
        private readonly CorrectionRequestService $correctionService,
        private readonly DataExportService $exportService
    ) {}

    // ── Consent ───────────────────────────────────────────────────────────────

    public function listConsentRequests(Request $request)
    {
        $patientId = $request->attributes->get('patient_id');
        if (!$patientId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'Patient identity could not be resolved from session.'], 401);
        }

        $requests = ConsentRequest::where('patient_id', $patientId)
            ->latest()
            ->get();

        return response()->json($requests, 200);
    }

    public function approveConsent(Request $request, $id)
    {
        // [C-1 FIX] user_id from middleware — never from request body fallback
        $userId = $request->attributes->get('user_id') ?? $request->attributes->get('patient_user_id');
        if (!$userId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'User identity could not be resolved from session.'], 401);
        }

        $grant = $this->consentService->approveConsent($id, $userId);

        return response()->json([
            'status'           => 'granted',
            'consent_grant_id' => $grant->id,
            'message'          => 'Consent request approved successfully.',
        ], 200);
    }

    public function denyConsent(Request $request, $id)
    {
        $consentRequest = $this->consentService->denyConsent($id);

        return response()->json([
            'status'             => 'denied',
            'consent_request_id' => $consentRequest->id,
            'message'            => 'Consent request denied.',
        ], 200);
    }

    public function revokeConsent(Request $request, $id)
    {
        // [C-1 FIX] user_id from middleware — never from request body fallback
        $userId = $request->attributes->get('user_id') ?? $request->attributes->get('patient_user_id');
        if (!$userId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'User identity could not be resolved from session.'], 401);
        }

        $grant = $this->consentService->revokeConsent($id, $userId);

        return response()->json([
            'status'           => 'revoked',
            'consent_grant_id' => $grant->id,
            'message'          => 'Consent grant revoked.',
        ], 200);
    }

    // ── Access logs ───────────────────────────────────────────────────────────

    public function listAccessLogs(Request $request)
    {
        $patientId = $request->attributes->get('patient_id');
        if (!$patientId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'Patient identity could not be resolved from session.'], 401);
        }

        $logs = AccessLog::where('patient_id', $patientId)
            ->latest()
            ->paginate(50);

        return response()->json($logs, 200);
    }

    // ── Correction requests ───────────────────────────────────────────────────

    public function createCorrectionRequest(Request $request)
    {
        $userId = $request->attributes->get('user_id') ?? $request->attributes->get('patient_user_id');
        $patientId = $request->attributes->get('patient_id');

        if (!$userId || !$patientId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'User/patient identity could not be resolved from session.'], 401);
        }

        $validated = $request->validate([
            'resource_type'          => ['required', 'string', 'max:100'],
            'resource_id'            => ['required', 'string', 'uuid'],
            'reason'                 => ['required', 'string', 'min:10', 'max:2000'],
            'supporting_document_id' => ['nullable', 'string', 'uuid'],
        ]);

        $corr = $this->correctionService->createRequest(
            $patientId,
            $userId,
            $validated['resource_type'],
            $validated['resource_id'],
            $validated['reason'],
            $validated['supporting_document_id'] ?? null
        );

        return response()->json($corr, 201);
    }

    // ── Data export ───────────────────────────────────────────────────────────

    public function createExportRequest(Request $request)
    {
        $userId = $request->attributes->get('user_id') ?? $request->attributes->get('patient_user_id');
        $patientId = $request->attributes->get('patient_id');

        if (!$userId || !$patientId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'User/patient identity could not be resolved from session.'], 401);
        }

        $validated = $request->validate([
            'export_type' => ['required', 'string', 'in:full_record,encounters,prescriptions,lab_results,imaging'],
            'scope'       => ['nullable', 'array'],
        ]);

        $exp = $this->exportService->requestExport(
            $patientId,
            $userId,
            $validated['export_type'],
            $validated['scope'] ?? []
        );

        return response()->json($exp, 201);
    }

    public function listExportRequests(Request $request)
    {
        $patientId = $request->attributes->get('patient_id');
        if (!$patientId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'Patient identity could not be resolved from session.'], 401);
        }

        $requests = DataExportRequest::where('patient_id', $patientId)
            ->latest()
            ->get();

        return response()->json($requests, 200);
    }

    public function downloadExport(Request $request, $id)
    {
        // [C-1 FIX] user_id from middleware — never from query string fallback
        $userId = $request->attributes->get('user_id') ?? $request->attributes->get('patient_user_id');
        if (!$userId) {
            return response()->json(['error' => 'IDENTITY_UNRESOLVABLE', 'message' => 'User identity could not be resolved from session.'], 401);
        }

        try {
            $exp = $this->exportService->downloadExport($id, $userId);

            return response()->json([
                'status'    => 'downloaded',
                'file_path' => $exp->file_path,
                'message'   => 'File downloaded successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
