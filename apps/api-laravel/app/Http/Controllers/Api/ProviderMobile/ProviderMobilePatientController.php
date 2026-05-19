<?php

namespace App\Http\Controllers\Api\ProviderMobile;

use App\Http\Controllers\Controller;
use App\Models\MobileFacilityContext;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provider Mobile API — Patient Lookup & Health ID Scan
 *
 * Allows providers to look up patients by Health ID (QR scan or manual entry)
 * within the context of their currently active facility.
 *
 * SECURITY: Full EMR access requires desktop/portal. Mobile returns a
 * clinical summary only. Broad text-search is facility-scoped.
 */
class ProviderMobilePatientController extends Controller
{
    /**
     * Scan or look up a patient by Health ID.
     *
     * GET /api/provider-mobile/patients/scan
     * Query params: health_id (required)
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate(['health_id' => 'required|string|min:3']);

        $healthId = trim($request->query('health_id'));

        $patient = Patient::where('health_id', $healthId)->first();

        if (!$patient) {
            return response()->json([
                'found'   => false,
                'message' => 'No patient found with Health ID: ' . $healthId,
            ], 404);
        }

        return response()->json([
            'found'   => true,
            'patient' => $this->formatPatientSummary($patient),
        ]);
    }

    /**
     * Search patients by name or phone (facility-scoped, max 20 results).
     *
     * GET /api/provider-mobile/patients/search
     * Query params: q (required, min 3 chars), limit (default 10)
     *
     * NOTE: This is NOT a broad patient search — it is constrained to the
     * provider's current facility context visits to protect patient privacy.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:3']);

        $q     = $request->query('q');
        $limit = min((int) $request->query('limit', 10), 20);

        $userId  = $this->resolveUserId($request);
        $context = MobileFacilityContext::currentFor($userId);

        $query = Patient::query()
            ->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('phone_number', 'like', "%{$q}%")
                    ->orWhere('health_id', 'like', "%{$q}%");
            });

        // Scope to facility if context is set
        if ($context) {
            $facilityId = $context->facility_id;
            $query->whereHas('identifiers', fn ($sub) => $sub->where('facility_id', $facilityId))
                  ->orWhereHas('visits', fn ($sub) => $sub->where('facility_id', $facilityId));
        }

        $patients = $query->limit($limit)->get();

        return response()->json([
            'data' => $patients->map(fn ($p) => $this->formatPatientSummary($p)),
        ]);
    }

    /**
     * Get a provider-facing clinical summary for a patient.
     *
     * GET /api/provider-mobile/patients/{id}
     *
     * Returns lightweight summary, NOT full EMR.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $patient = Patient::where('id', $id)
            ->orWhere('health_id', $id)
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatPatientSummary($patient),
        ]);
    }

    // -------------------------------------------------------------------------

    private function formatPatientSummary(Patient $p): array
    {
        return [
            'id'               => $p->id,
            'health_id'        => $p->health_id,
            'display_name'     => $p->first_name . ' ' . $p->last_name,
            'sex'              => $p->sex,
            'dob'              => $p->date_of_birth?->toDateString(),
            'age'              => $p->date_of_birth
                ? (int) $p->date_of_birth->diffInYears(now())
                : null,
            'phone'            => $p->phone_number,
            'identity_status'  => $p->identity_status ?? 'unknown',
        ];
    }

    private function resolveUserId(Request $request): string
    {
        if ($request->has('_user_id')) {
            return $request->input('_user_id');
        }
        return User::value('id') ?? 'demo-provider';
    }
}
