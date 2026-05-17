<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Partners\Models\Partner;
use App\Modules\Partners\Services\PartnerApplicationService;
use Illuminate\Support\Facades\Validator;

class PartnerGovernanceController extends Controller
{
    private PartnerApplicationService $applicationService;

    public function __construct(PartnerApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    public function listPartners(Request $request)
    {
        // Simple listing logic
        $status = $request->query('status');
        
        $query = Partner::query()->with(['documents', 'agreements'])->orderBy('created_at', 'desc');
        
        if ($status) {
            $query->where('status', $status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get()
        ]);
    }

    public function approvePartner(Request $request, string $id)
    {
        $partner = Partner::where('uuid', $id)->firstOrFail();
        
        $partner = $this->applicationService->approveApplication($partner);

        return response()->json([
            'status' => 'success',
            'message' => 'Partner application approved.',
            'partner' => $partner
        ]);
    }

    public function suspendPartner(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $partner = Partner::where('uuid', $id)->firstOrFail();
        
        $partner = $this->applicationService->suspendPartner($partner, $request->input('reason'));

        return response()->json([
            'status' => 'success',
            'message' => 'Partner has been suspended.',
            'partner' => $partner
        ]);
    }

    public function verifyDocument(Request $request, string $id, string $documentId)
    {
        $partner = Partner::where('uuid', $id)->firstOrFail();
        $document = \App\Modules\Partners\Models\PartnerDocument::where('id', $documentId)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        $service = app(\App\Modules\Partners\Services\PartnerVerificationService::class);
        $document = $service->verifyDocument($document, $request->user()?->uuid ?? 'system', $request->input('notes'));

        return response()->json([
            'status' => 'success',
            'message' => 'Document verified.',
            'document' => $document
        ]);
    }

    public function markAgreementSigned(Request $request, string $id, string $agreementId)
    {
        $partner = Partner::where('uuid', $id)->firstOrFail();
        $agreement = \App\Modules\Partners\Models\PartnerAgreement::where('id', $agreementId)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        $service = app(\App\Modules\Partners\Services\PartnerAgreementService::class);
        $agreement = $service->markAgreementSigned($agreement, $request->user()?->uuid ?? 'system');

        return response()->json([
            'status' => 'success',
            'message' => 'Agreement marked as signed.',
            'agreement' => $agreement
        ]);
    }

    public function certifyIntegration(Request $request, string $id, string $integrationId)
    {
        $partner = Partner::where('uuid', $id)->firstOrFail();
        $integration = \App\Modules\Partners\Models\PartnerIntegration::where('id', $integrationId)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        $service = app(\App\Modules\Partners\Services\PartnerIntegrationGovernanceService::class);
        $integration = $service->certifyIntegration($integration, $request->user()?->uuid ?? 'system');

        return response()->json([
            'status' => 'success',
            'message' => 'Integration certified.',
            'integration' => $integration
        ]);
    }

    public function enableProduction(Request $request, string $id, string $integrationId)
    {
        $partner = Partner::where('uuid', $id)->firstOrFail();
        $integration = \App\Modules\Partners\Models\PartnerIntegration::where('id', $integrationId)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        $service = app(\App\Modules\Partners\Services\PartnerIntegrationGovernanceService::class);
        
        try {
            $integration = $service->enableProduction($integration, $request->user()?->uuid ?? 'system');
            return response()->json([
                'status' => 'success',
                'message' => 'Production API access enabled.',
                'integration' => $integration
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
