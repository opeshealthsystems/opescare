<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile Patient API — Patient Profile & Timeline
 */
class MobilePatientController extends Controller
{
    /**
     * Get the authenticated patient's profile.
     *
     * GET /api/mobile/me
     */
    public function getMe(Request $request): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $patient   = Patient::find($patientId);

        if (!$patient) {
            // Demo fallback
            return response()->json([
                'health_id'            => 'OC-CMR-7KQ9-MP42-X8D1',
                'display_name'         => 'John D.',
                'first_name'           => 'John',
                'last_name'            => 'Doe',
                'phone'                => '+237 600-000-000',
                'email'                => null,
                'dob'                  => '1990-04-12',
                'sex'                  => 'male',
                'digital_qr_reference' => 'qr_ref_opescare_johndoe_1002',
                'status'               => 'active',
            ]);
        }

        return response()->json([
            'health_id'    => $patient->health_id,
            'display_name' => trim($patient->first_name . ' ' . substr($patient->last_name, 0, 1) . '.'),
            'first_name'   => $patient->first_name,
            'last_name'    => $patient->last_name,
            'phone'        => $patient->phone_number,
            'email'        => $patient->email ?? null,
            'dob'          => $patient->date_of_birth?->toDateString(),
            'sex'          => $patient->sex,
            'status'       => $patient->identity_status ?? 'active',
        ]);
    }

    /**
     * Get the patient's digital Health ID card details (for wallet display).
     *
     * GET /api/mobile/health-id-card
     */
    public function getHealthIdCard(Request $request): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $patient   = Patient::find($patientId);

        if (!$patient) {
            return response()->json([
                'health_id'      => 'OC-CMR-7KQ9-MP42-X8D1',
                'display_name'   => 'John D.',
                'sex'            => 'male',
                'dob'            => '1990-04-12',
                'blood_type'     => null,
                'qr_payload'     => base64_encode('{"hid":"OC-CMR-7KQ9-MP42-X8D1","ts":"' . now()->toIso8601String() . '"}'),
                'card_issued_at' => now()->subYear()->toDateString(),
                'status'         => 'active',
            ]);
        }

        $qrPayload = base64_encode(json_encode([
            'hid' => $patient->health_id,
            'fn'  => $patient->first_name,
            'ln'  => substr($patient->last_name, 0, 1),
            'dob' => $patient->date_of_birth?->toDateString(),
            'sex' => $patient->sex,
            'ts'  => now()->toIso8601String(),
        ]));

        return response()->json([
            'health_id'    => $patient->health_id,
            'display_name' => $patient->first_name . ' ' . $patient->last_name,
            'sex'          => $patient->sex,
            'dob'          => $patient->date_of_birth?->toDateString(),
            'blood_type'   => null, // populated from allergy/clinical records when available
            'qr_payload'   => $qrPayload,
            'status'       => $patient->identity_status ?? 'active',
        ]);
    }

    /**
     * Get the patient's clinical timeline (visits, labs, prescriptions, appointments).
     *
     * GET /api/mobile/timeline
     * Query params: limit (default 20)
     */
    public function getTimeline(Request $request): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $limit     = min((int) $request->query('limit', 20), 100);

        $events = collect();

        // Visits
        $visits = Visit::where('patient_id', $patientId)
            ->with('facility:id,name')
            ->latest('created_at')
            ->take($limit)
            ->get();

        foreach ($visits as $v) {
            $events->push([
                'event_type'    => 'visit',
                'id'            => $v->id,
                'facility_name' => $v->facility?->name,
                'occurred_at'   => $v->created_at->toIso8601String(),
                'summary'       => 'Clinical visit — ' . ($v->visit_type ?? 'outpatient'),
            ]);
        }

        // Lab orders (resulted)
        $labs = LabOrder::where('patient_id', $patientId)
            ->where('status', 'resulted')
            ->with('facility:id,name')
            ->latest('resulted_at')
            ->take($limit)
            ->get();

        foreach ($labs as $l) {
            $events->push([
                'event_type'    => 'lab_result',
                'id'            => $l->id,
                'facility_name' => $l->facility?->name,
                'occurred_at'   => $l->resulted_at?->toIso8601String() ?? $l->ordered_at->toIso8601String(),
                'summary'       => 'Lab result — ' . $l->test_name,
            ]);
        }

        // Prescriptions
        $prescriptions = Prescription::where('patient_id', $patientId)
            ->with(['facility:id,name', 'items'])
            ->latest('prescribed_at')
            ->take($limit)
            ->get();

        foreach ($prescriptions as $p) {
            $events->push([
                'event_type'    => 'prescription',
                'id'            => $p->id,
                'facility_name' => $p->facility?->name,
                'occurred_at'   => $p->prescribed_at->toIso8601String(),
                'summary'       => 'Prescription — ' . $p->items->count() . ' item(s)',
            ]);
        }

        // Sort all events newest-first and take up to $limit
        $timeline = $events->sortByDesc('occurred_at')->take($limit)->values();

        return response()->json(['timeline' => $timeline]);
    }

    // -------------------------------------------------------------------------

    private function resolvePatientId(Request $request): string
    {
        if ($request->has('_patient_id')) {
            return $request->input('_patient_id');
        }
        return Patient::value('id') ?? 'demo';
    }
}
