<?php
$file = 'apps/api-laravel/app/Modules/EncounterManagement/Services/ConsultationService.php';
$content = file_get_contents($file);

$newMethod = <<<'METHOD'

    public function recordDiagnosis(array $data, ?string $actorId = null): Diagnosis
    {
        DB::beginTransaction();
        try {
            $diagnosis = Diagnosis::create([
                'patient_id' => $data['patient_id'],
                'visit_id' => $data['visit_id'],
                'provider_id' => $actorId,
                'code_system' => $data['code_system'] ?? null,
                'code' => $data['code'] ?? null,
                'display_name' => $data['display_name'],
                'status' => $data['status'] ?? 'active',
                'is_primary' => $data['is_primary'] ?? true,
            ]);

            AuditEvent::create([
                'actor_id' => $actorId,
                'patient_id' => $data['patient_id'],
                'encounter_id' => $data['visit_id'],
                'action_type' => 'create',
                'resource_type' => 'diagnosis',
                'resource_id' => $diagnosis->id,
            ]);

            DB::commit();
            return $diagnosis;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
METHOD;

$content = preg_replace('/}\s*$/', $newMethod, $content);
file_put_contents($file, $content);
