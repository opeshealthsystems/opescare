<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Governance\Services\ConsentService;
use App\Modules\Governance\Services\CorrectionRequestService;
use App\Modules\Governance\Services\DataExportService;
use App\Models\ConsentRequest;
use App\Models\AccessLog;
use App\Models\CorrectionRequest;
use App\Models\DataExportRequest;

class MobileGovernanceController extends Controller
{
    private $consentService;
    private $correctionService;
    private $exportService;

    public function __construct(
        ConsentService $consentService,
        CorrectionRequestService $correctionService,
        DataExportService $exportService
    ) {
        $this->consentService = $consentService;
        $this->correctionService = $correctionService;
        $this->exportService = $exportService;
    }

    public function listConsentRequests(Request $request)
    {
        // For testing / mobile API access, grab patient from session/query fallback
        $patientId = $request->query('patient_id', '00000000-0000-0000-0000-000000000003');
        $requests = ConsentRequest::where('patient_id', $patientId)->get();

        return response()->json($requests, 200);
    }

    public function approveConsent(Request $request, $id)
    {
        $userId = $request->input('user_id', '00000000-0000-0000-0000-000000000001');
        $grant = $this->consentService->approveConsent($id, $userId);

        return response()->json([
            'status' => 'granted',
            'consent_grant_id' => $grant->id,
            'message' => 'Consent request approved successfully.'
        ], 200);
    }

    public function denyConsent(Request $request, $id)
    {
        $consentRequest = $this->consentService->denyConsent($id);

        return response()->json([
            'status' => 'denied',
            'consent_request_id' => $consentRequest->id,
            'message' => 'Consent request denied.'
        ], 200);
    }

    public function revokeConsent(Request $request, $id)
    {
        $userId = $request->input('user_id', '00000000-0000-0000-0000-000000000001');
        $grant = $this->consentService->revokeConsent($id, $userId);

        return response()->json([
            'status' => 'revoked',
            'consent_grant_id' => $grant->id,
            'message' => 'Consent grant revoked.'
        ], 200);
    }

    public function listAccessLogs(Request $request)
    {
        $patientId = $request->query('patient_id', '00000000-0000-0000-0000-000000000003');
        $logs = AccessLog::where('patient_id', $patientId)->get();

        return response()->json($logs, 200);
    }

    public function createCorrectionRequest(Request $request)
    {
        $request->validate([
            'patient_id'            => 'required|string|max:255',
            'user_id'               => 'required|string|max:255',
            'resource_type'         => 'required|string|max:100',
            'resource_id'           => 'required|string|max:255',
            'reason'                => 'required|string|max:2000',
            'supporting_document_id' => 'nullable|string|max:255',
        ]);

        $patientId = $request->input('patient_id');
        $userId = $request->input('user_id');
        $resourceType = $request->input('resource_type');
        $resourceId = $request->input('resource_id');
        $reason = $request->input('reason');
        $docId = $request->input('supporting_document_id');

        if (!$patientId || !$userId || !$resourceType || !$resourceId || !$reason) {
            return response()->json(['message' => 'Validation failed.'], 400);
        }

        $corr = $this->correctionService->createRequest(
            $patientId,
            $userId,
            $resourceType,
            $resourceId,
            $reason,
            $docId
        );

        return response()->json($corr, 201);
    }

    public function createExportRequest(Request $request)
    {
        $patientId = $request->input('patient_id');
        $userId = $request->input('user_id');
        $type = $request->input('export_type');
        $scope = $request->input('scope', []);

        if (!$userId || !$type) {
            return response()->json(['message' => 'Validation failed.'], 400);
        }

        $exp = $this->exportService->requestExport($patientId, $userId, $type, $scope);

        return response()->json($exp, 201);
    }

    public function listExportRequests(Request $request)
    {
        $patientId = $request->query('patient_id');
        $userId = $request->query('user_id');

        $query = DataExportRequest::query();
        if ($patientId) {
            $query->where('patient_id', $patientId);
        }
        if ($userId) {
            $query->where('requested_by_user_id', $userId);
        }

        return response()->json($query->get(), 200);
    }

    public function downloadExport(Request $request, $id)
    {
        $userId = $request->query('user_id', '00000000-0000-0000-0000-000000000001');

        try {
            $exp = $this->exportService->downloadExport($id, $userId);
            return response()->json([
                'status' => 'downloaded',
                'file_path' => $exp->file_path,
                'message' => 'File downloaded successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
