<?php

namespace App\Modules\Academy\Services;

use App\Models\Course;
use App\Models\SimulationAttempt;

class SimulationService
{
    /**
     * Start a simulation.
     */
    public function startSimulation(string $userId, string $courseId, string $type): SimulationAttempt
    {
        $course = Course::findOrFail($courseId);

        return SimulationAttempt::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'simulation_type' => $type,
            'score' => 0,
            'status' => 'failed',
            'mistakes_json' => [],
            'started_at' => now(),
            'completed_at' => null
        ]);
    }

    /**
     * Complete simulation session by checking actions against critical medical constraints.
     */
    public function submitSimulation(string $attemptId, array $actions): SimulationAttempt
    {
        $attempt = SimulationAttempt::findOrFail($attemptId);
        $mistakes = [];
        $score = 100;

        foreach ($actions as $action) {
            // Constraint 1: Selecting the wrong patient record
            if (($action['type'] ?? '') === 'select_patient' && !($action['is_correct_patient'] ?? true)) {
                $mistakes[] = [
                    'rule' => 'WRONG_PATIENT_RECORD',
                    'message_en' => 'Selected the wrong patient record during the simulation clinical challenge.',
                    'message_fr' => 'Sélection d’un dossier patient erroné au cours du défi clinique de simulation.',
                    'severity' => 'critical'
                ];
                $score = 0; // Immediate critical failure
            }

            // Constraint 2: Writing prescriptions as a student
            if (($action['type'] ?? '') === 'write_prescription' && ($action['is_student'] ?? false)) {
                $mistakes[] = [
                    'rule' => 'STUDENT_PRESCRIBING_VIOLATION',
                    'message_en' => 'Unsupervised student attempted to write a clinical prescription.',
                    'message_fr' => 'Un étudiant non supervisé a tenté de rédiger une ordonnance clinique.',
                    'severity' => 'critical'
                ];
                $score = 0; // Immediate critical failure
            }

            // Constraint 3: Misusing emergency overrides
            if (($action['type'] ?? '') === 'emergency_override' && !($action['has_valid_emergency_justification'] ?? true)) {
                $mistakes[] = [
                    'rule' => 'EMERGENCY_OVERRIDE_MISUSE',
                    'message_en' => 'Triggered emergency access override without statutory emergency grounds or valid reasoning.',
                    'message_fr' => 'Déclenchement d’une dérogation d’accès d’urgence sans motifs d’urgence légaux ni justification valide.',
                    'severity' => 'critical'
                ];
                $score = 0; // Immediate critical failure
            }

            // Constraint 4: Releasing unauthorized or un-anonymized public health/lab values
            if (($action['type'] ?? '') === 'release_public_health_report' && !($action['is_anonymized'] ?? false)) {
                $mistakes[] = [
                    'rule' => 'PUBLIC_HEALTH_DATA_LEAK',
                    'message_en' => 'Released public health data containing raw un-anonymized patient details.',
                    'message_fr' => 'Divulgation de données de santé publique contenant des détails bruts non anonymisés sur les patients.',
                    'severity' => 'critical'
                ];
                $score = 0; // Immediate critical failure
            }

            // Minor mistakes
            if (($action['type'] ?? '') === 'vitals_entry' && !($action['is_within_normal_ranges_checked'] ?? true)) {
                $mistakes[] = [
                    'rule' => 'VITALS_RANGE_IGNORANCE',
                    'message_en' => 'Entered out-of-range vitals without confirming trigger warnings.',
                    'message_fr' => 'Saisie de constantes vitales hors normes sans confirmation des alertes.',
                    'severity' => 'minor'
                ];
                $score = max(0, $score - 10);
            }
        }

        $course = $attempt->course;
        $status = ($score >= $course->passing_score) ? 'passed' : 'failed';

        $attempt->update([
            'score' => $score,
            'status' => $status,
            'mistakes_json' => $mistakes,
            'completed_at' => now()
        ]);

        return $attempt;
    }
}
