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
                $mergeCase->reviewed_by = Str::uuid(); // Mocking admin UUID
                $mergeCase->review_reason = $request->input('review_reason');
                $mergeCase->save();

                // 4. Audit Log
                $this->logAction($primary->id, $primary->health_id, 'approve_merge', 'success', $request);
                
            } else {
                // Reject Merge
                $mergeCase->status = 'rejected';
                $mergeCase->reviewed_by = Str::uuid();
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
        MedicalIdAccessEvent::create([
            'patient_id' => $patientId,
            'health_id' => $healthId,
            'actor_id' => Str::uuid(), 
            'actor_type' => 'admin',
            'facility_id' => Str::uuid(), 
            'access_type' => $accessType,
            'purpose' => 'identity_reconciliation',
            'result' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
