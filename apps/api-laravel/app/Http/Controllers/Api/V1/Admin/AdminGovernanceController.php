<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Governance\Services\EmergencyAccessService;
use App\Modules\Governance\Services\CorrectionRequestService;
use App\Modules\Governance\Services\DataExportService;
use App\Modules\Governance\Services\CountryPolicyService;
use App\Models\AccessLog;
use App\Models\EmergencyAccessEvent;
use App\Models\CorrectionRequest;
use App\Models\DataExportRequest;
use App\Models\SecurityIncident;
use App\Models\CountryPolicy;
use Carbon\Carbon;

class AdminGovernanceController extends Controller
{
    private $emergencyService;
    private $correctionService;
    private $exportService;
    private $policyService;

    public function __construct(
        EmergencyAccessService $emergencyService,
        CorrectionRequestService $correctionService,
        DataExportService $exportService,
        CountryPolicyService $policyService
    ) {
        $this->emergencyService = $emergencyService;
        $this->correctionService = $correctionService;
        $this->exportService = $exportService;
        $this->policyService = $policyService;
    }

    public function listAccessLogs(Request $request)
    {
        return response()->json(AccessLog::all(), 200);
    }

    public function listEmergencyAccessReviews(Request $request)
    {
        return response()->json(EmergencyAccessEvent::all(), 200);
    }

    public function reviewEmergencyAccess(Request $request, $id)
    {
        $reviewerId = $request->input('reviewer_id', '00000000-0000-0000-0000-000000000001');
        $status = $request->input('review_status');
        $comment = $request->input('comment');

        if (!$status) {
            return response()->json(['message' => 'review_status is required.'], 400);
        }

        $event = $this->emergencyService->reviewEmergencyAccess($id, $reviewerId, $status, $comment);

        return response()->json($event, 200);
    }

    public function listCorrectionRequests(Request $request)
    {
        return response()->json(CorrectionRequest::all(), 200);
    }

    public function approveCorrectionRequest(Request $request, $id)
    {
        $reviewerId = $request->input('reviewer_id', '00000000-0000-0000-0000-000000000001');
        $corr = $this->correctionService->approveRequest($id, $reviewerId);

        return response()->json($corr, 200);
    }

    public function rejectCorrectionRequest(Request $request, $id)
    {
        $reviewerId = $request->input('reviewer_id', '00000000-0000-0000-0000-000000000001');
        $corr = $this->correctionService->rejectRequest($id, $reviewerId);

        return response()->json($corr, 200);
    }

    public function listExportRequests(Request $request)
    {
        return response()->json(DataExportRequest::all(), 200);
    }

    public function approveExportRequest(Request $request, $id)
    {
        $approverId = $request->input('approver_id', '00000000-0000-0000-0000-000000000001');
        $exp = $this->exportService->approveExport($id, $approverId);

        return response()->json($exp, 200);
    }

    public function rejectExportRequest(Request $request, $id)
    {
        $exp = DataExportRequest::findOrFail($id);
        $exp->status = 'rejected';
        $exp->save();

        return response()->json($exp, 200);
    }

    public function listSecurityIncidents(Request $request)
    {
        return response()->json(SecurityIncident::all(), 200);
    }

    public function createSecurityIncident(Request $request)
    {
        $type = $request->input('incident_type');
        $severity = $request->input('severity');
        $summary = $request->input('summary');
        $creatorId = $request->input('created_by');

        if (!$type || !$severity || !$summary) {
            return response()->json(['message' => 'Validation failed.'], 400);
        }

        $inc = new SecurityIncident();
        $inc->incident_type = $type;
        $inc->severity = $severity;
        $inc->summary = $summary;
        $inc->status = 'new';
        $inc->detected_at = Carbon::now();
        $inc->created_by = $creatorId;
        $inc->save();

        return response()->json($inc, 201);
    }

    public function containSecurityIncident(Request $request, $id)
    {
        $inc = SecurityIncident::findOrFail($id);
        $inc->status = 'contained';
        $inc->contained_at = Carbon::now();
        $inc->save();

        return response()->json($inc, 200);
    }

    public function resolveSecurityIncident(Request $request, $id)
    {
        $inc = SecurityIncident::findOrFail($id);
        $inc->status = 'resolved';
        $inc->resolved_at = Carbon::now();
        $inc->save();

        return response()->json($inc, 200);
    }

    public function listCountryPolicies(Request $request)
    {
        return response()->json(CountryPolicy::all(), 200);
    }

    public function createCountryPolicy(Request $request)
    {
        $code = $request->input('country_code');
        $name = $request->input('name');
        $version = $request->input('version');
        $settings = $request->input('settings_json', []);

        if (!$code || !$name || !$version) {
            return response()->json(['message' => 'Validation failed.'], 400);
        }

        $policy = new CountryPolicy();
        $policy->country_code = strtoupper($code);
        $policy->name = $name;
        $policy->version = $version;
        $policy->effective_from = Carbon::now();
        $policy->settings_json = $settings;
        $policy->status = 'draft';
        $policy->save();

        return response()->json($policy, 201);
    }

    public function updateCountryPolicy(Request $request, $id)
    {
        $policy = CountryPolicy::findOrFail($id);
        $policy->update($request->only(['name', 'version', 'settings_json', 'status']));

        return response()->json($policy, 200);
    }

    public function publishCountryPolicy(Request $request, $id)
    {
        $policy = CountryPolicy::findOrFail($id);
        $published = $this->policyService->publishPolicy(
            $policy->country_code,
            $policy->name,
            $policy->version,
            $policy->settings_json
        );

        return response()->json($published, 200);
    }
}
