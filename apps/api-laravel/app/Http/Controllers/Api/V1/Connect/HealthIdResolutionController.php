<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\AuditLogger;
use App\Services\Identity\HealthIdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Health ID Resolution — the "find or create" interoperability endpoint.
 *
 * External HIS systems (e.g. OpesCare HIS) call this endpoint when they have
 * a patient who may or may not already have an OpesCare Health ID.
 *
 * Flow:
 *  1. If health_id provided → look up patient. Return summary.
 *  2. If demographics provided → search by name + DOB + country.
 *     a. Found → return existing Health ID + summary.
 *     b. Not found → auto-create patient + generate Health ID → return with status=created.
 */
class HealthIdResolutionController extends Controller
{
    public function __construct(private HealthIdGeneratorService $generator) {}

    public function resolve(Request $request): JsonResponse
    {
        $healthId    = $request->input('health_id');
        $firstName   = $request->input('first_name');
        $lastName    = $request->input('last_name');
        $dob         = $request->input('date_of_birth');     // YYYY-MM-DD
        $countryCode = strtoupper($request->input('country_code', 'CM'));
        $sex         = $request->input('sex');
        $phone       = $request->input('phone_number');
        $purpose     = $request->input('purpose', 'external_system_integration');
        $externalRef = $request->input('external_reference'); // caller's own patient ID

        // ── Validate: need health_id OR demographics ──────────────────────────
        $hasHealthId    = !empty($healthId);
        $hasDemographics = !empty($firstName) && !empty($lastName) && !empty($dob);

        if (!$hasHealthId && !$hasDemographics) {
            return response()->json([
                'status'    => 'error',
                'error_code'=> 'MISSING_LOOKUP_CRITERIA',
                'message'   => 'Provide either health_id OR (first_name, last_name, date_of_birth).',
            ], 422);
        }

        // ── Attempt lookup ────────────────────────────────────────────────────
        $patient = null;

        if ($hasHealthId) {
            $patient = Patient::withoutGlobalScope('isolate_demo')
                ->where('health_id', $healthId)
                ->first();
        }

        if (!$patient && $hasDemographics) {
            $patient = Patient::withoutGlobalScope('isolate_demo')
                ->where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->whereDate('date_of_birth', $dob)
                ->where('country_code', $countryCode)
                ->first();
        }

        // ── Found: return existing Health ID ──────────────────────────────────
        if ($patient) {
            AuditLogger::log($request, 'health_id_resolved', 'patient', $patient->id, $patient->id, false, null,
                ['purpose' => $purpose, 'external_reference' => $externalRef],
                ['health_id' => $patient->health_id]
            );

            return response()->json([
                'status'      => 'found',
                'health_id'   => $patient->health_id,
                'patient'     => $this->summary($patient),
                'next_action' => 'use_health_id',
            ], 200);
        }

        // ── Not found: auto-create + generate Health ID ───────────────────────
        if (!$hasDemographics) {
            // health_id was provided but not found — cannot create without demographics
            return response()->json([
                'status'    => 'not_found',
                'error_code'=> 'HEALTH_ID_NOT_FOUND',
                'message'   => 'No patient with that Health ID exists in OpesCare. Provide demographics to register.',
            ], 404);
        }

        $newHealthId = $this->generator->generate($countryCode);

        $patient = Patient::create([
            'health_id'           => $newHealthId,
            'first_name'          => $firstName,
            'last_name'           => $lastName,
            'date_of_birth'       => $dob,
            'country_code'        => $countryCode,
            'sex'                 => $sex,
            'phone_number'        => $phone,
            'verification_status' => 'pending',
            'identity_status'     => 'unverified',
            'is_demo'             => (bool) config('demo.enabled', false),
        ]);

        AuditLogger::log($request, 'health_id_auto_created', 'patient', $patient->id, $patient->id, false, null,
            ['purpose' => $purpose, 'external_reference' => $externalRef],
            ['health_id' => $newHealthId]
        );

        return response()->json([
            'status'      => 'created',
            'health_id'   => $newHealthId,
            'patient'     => $this->summary($patient),
            'message'     => 'New OpesCare Health ID registered for this patient.',
            'next_action' => 'push_records',
        ], 201);
    }

    /** Verify a Health ID format and existence without creating. */
    public function verify(Request $request, string $healthId): JsonResponse
    {
        if (!$this->generator->isValid($healthId)) {
            return response()->json([
                'status'    => 'invalid',
                'error_code'=> 'HEALTH_ID_INVALID_FORMAT',
                'message'   => 'The Health ID format or checksum is invalid.',
            ], 422);
        }

        $patient = Patient::withoutGlobalScope('isolate_demo')
            ->where('health_id', $healthId)
            ->first();

        if (!$patient) {
            return response()->json([
                'status'    => 'not_found',
                'error_code'=> 'HEALTH_ID_NOT_FOUND',
                'message'   => 'Health ID is well-formed but not registered in OpesCare.',
            ], 404);
        }

        AuditLogger::log($request, 'health_id_verified', 'patient', $patient->id, $patient->id);

        return response()->json([
            'status'    => 'valid',
            'health_id' => $patient->health_id,
            'patient'   => $this->summary($patient),
        ], 200);
    }

    private function summary(Patient $patient): array
    {
        return [
            'display_name'        => trim($patient->first_name . ' ' . $patient->last_name),
            'sex'                 => $patient->sex,
            'year_of_birth'       => $patient->date_of_birth?->year,
            'country_code'        => $patient->country_code,
            'verification_status' => $patient->verification_status ?? 'pending',
            'identity_status'     => $patient->identity_status   ?? 'unverified',
        ];
    }
}
