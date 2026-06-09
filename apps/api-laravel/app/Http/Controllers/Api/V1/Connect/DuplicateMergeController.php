<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentityMergeCase;
use App\Models\Patient;
use App\Models\HealthIdAlias;
use App\Models\MedicalIdAccessEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DuplicateMergeController extends Controller
{
    public function listCases(Request $request)
    {
        $cases = IdentityMergeCase::with(['primaryPatient', 'secondaryPatient'])
            ->where('status', 'pending_review')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'cases' => $cases
        ]);
    }

    public function resolveCase(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'resolution' => 'required|string|in:approve,reject',
            'review_reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $mergeCase = IdentityMergeCase::where('uuid', $id)->firstOrFail();
        $primary = Patient::findOrFail($mergeCase->primary_patient_id);
        $secondary = Patient::findOrFail($mergeCase->secondary_patient_id);

        $resolution = $request->input('resolution');
        
        DB::beginTransaction();
        try {
            if ($resolution === 'approve') {
                // 1. Create Alias for Primary Patient using Secondary's Health ID
                HealthIdAlias::create([
                    'patient_id' => $primary->id,
                    'alias_type' => 'merged_health_id',
                    'alias_value' => $secondary->health_id,
                    'status' => 'active'
                ]);

                // 2. Mark secondary patient as merged
                $secondary->verification_status = 'merged';
                $secondary->identity_status = 'merged';
                $secondary->save();

                // 3. Mark case as merged
                $mergeCase->status = 'merged';
                // [FIX M-4] auth()->id() is null for B2B API clients.
                // Use the integration_client_id from the bearer token instead — meaningful audit trail.
                $mergeCase->reviewed_by = auth()->id()
                    ?? $request->attributes->get('integration_client_id')
                    ?? 'api:unknown';
                $mergeCase->review_reason = $request->input('review_reason');
                $mergeCase->save();

                // 4. Audit Log
                $this->logAction($primary->id, $primary->health_id, 'approve_merge', 'success', $request);
                
            } else {
                // Reject Merge
                $mergeCase->status = 'rejected';
                // [FIX M-4] auth()->id() is null for B2B API clients.
                // Use the integration_client_id from the bearer token instead — meaningful audit trail.
                $mergeCase->reviewed_by = auth()->id()
                    ?? $request->attributes->get('integration_client_id')
                    ?? 'api:unknown';
                $mergeCase->review_reason = $request->input('review_reason');
                $mergeCase->save();

                $this->logAction($primary->id, $primary->health_id, 'reject_merge', 'success', $request);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Merge case successfully {$resolution}d."
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during resolution: ' . $e->getMessage()
            ], 500);
        }
    }

    private function logAction(string $patientId, string $healthId, string $accessType, string $result, Request $request)
    {
        // [FIX M-4] actor_id and facility_id were both random Str::uuid() — no audit value.
        // Now use the real identities from the bearer token attributes.
        $actorId    = auth()->id()
            ?? $request->attributes->get('integration_client_id')
            ?? 'api:unknown';
        $facilityId = $request->attributes->get('facility_id') ?? 'unknown';

        MedicalIdAccessEvent::create([
            'patient_id'  => $patientId,
            'health_id'   => $healthId,
            'actor_id'    => $actorId,
            'actor_type'  => auth()->check() ? 'admin' : 'integration_client',
            'facility_id' => $facilityId,
            'access_type' => $accessType,
            'purpose'     => 'identity_reconciliation',
            'result'      => $result,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);
    }
}
