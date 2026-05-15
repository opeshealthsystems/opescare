<?php

namespace App\Modules\Triage\Services;

use App\Models\TriageRecord;
use App\Models\VitalSign;
use App\Models\AuditEvent;
use Exception;
use Illuminate\Support\Facades\DB;

class TriageService
{
    /**
     * Records triage and vital signs. Validates critical values.
     */
    public function recordTriage(array $data, ?string $actorId = null): TriageRecord
    {
        DB::beginTransaction();
        try {
            $triage = TriageRecord::create([
                'visit_id' => $data['visit_id'],
                'nurse_id' => $data['nurse_id'] ?? $actorId,
                'presenting_complaint' => $data['presenting_complaint'] ?? null,
                'pain_score' => $data['pain_score'] ?? null,
                'pregnancy_status' => $data['pregnancy_status'] ?? null,
                'acuity_score' => $data['acuity_score'] ?? null,
            ]);

            if (isset($data['vitals'])) {
                // Validation against impossible or dangerous units
                $vitalsData = $data['vitals'];

                if (isset($vitalsData['temperature']) && ($vitalsData['temperature'] < 20 || $vitalsData['temperature'] > 45)) {
                    throw new Exception("Temperature value is out of logical human bounds (Celsius).");
                }

                $vitalSigns = VitalSign::create(array_merge($vitalsData, [
                    'triage_record_id' => $triage->id,
                ]));

                // Extremely basic critical alert generation placeholder
                if (isset($vitalsData['oxygen_saturation']) && $vitalsData['oxygen_saturation'] < 90) {
                    $triage->update(['acuity_score' => 'critical']);
                }
            }

            AuditEvent::create([
                'actor_id' => $actorId ?? $data['nurse_id'] ?? null,
                'facility_id' => $data['facility_id'] ?? null,
                'patient_id' => $data['patient_id'] ?? null,
                'encounter_id' => $data['visit_id'],
                'action_type' => 'create',
                'resource_type' => 'triage_record',
                'resource_id' => $triage->id,
                'reason' => 'Triage recorded',
            ]);

            DB::commit();
            return $triage;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
