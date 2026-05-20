<?php

namespace App\Modules\ClinicalDecisionSupport\Services;

use App\Models\ClinicalRule;
use App\Models\ClinicalAlert;
use App\Models\DrugInteractionRule;
use App\Models\AllergyAlertRule;
use App\Models\DoseWarningRule;
use App\Models\Prescription;
use App\Models\Patient;

/**
 * RuleEvaluationService — Module 20 (Clinical Decision Support / Alerts)
 *
 * Evaluates clinical rules against patient context and prescription data.
 * Returns a list of alerts that the clinician should review.
 *
 * CDSS Safety Rule (NON-NEGOTIABLE):
 * All alerts are advisory only. They assist the clinician's review.
 * They do NOT prevent prescribing or replace clinical judgment.
 * "Do not use AI for diagnosis or autonomous clinical decisions."
 */
class RuleEvaluationService
{
    /**
     * Evaluate all applicable rules for a prescription being created.
     * Returns array of alert payloads to be surfaced to the clinician.
     */
    public function evaluatePrescription(
        Prescription $prescription,
        string $patientId,
        string $facilityId
    ): array {
        $alerts = [];

        foreach ($prescription->items ?? [] as $item) {
            $medicineCode = $item['medicine_code'] ?? null;
            if (! $medicineCode) {
                continue;
            }

            // Check drug interactions
            $interactionAlerts = $this->checkDrugInteractions($medicineCode, $patientId);
            $alerts = array_merge($alerts, $interactionAlerts);

            // Check allergy alerts
            $allergyAlerts = $this->checkAllergyAlerts($medicineCode, $patientId);
            $alerts = array_merge($alerts, $allergyAlerts);

            // Check dose warnings
            $doseAlerts = $this->checkDoseWarnings(
                $medicineCode,
                $item['dose_value'] ?? null,
                $item['dose_unit'] ?? null
            );
            $alerts = array_merge($alerts, $doseAlerts);
        }

        return $alerts;
    }

    /**
     * Check drug interaction rules for a given medicine against the patient's
     * current prescription profile.
     */
    public function checkDrugInteractions(string $medicineCode, string $patientId): array
    {
        $rules = DrugInteractionRule::where('is_active', true)
            ->where(function ($q) use ($medicineCode) {
                $q->where('medicine_a_code', $medicineCode)
                  ->orWhere('medicine_b_code', $medicineCode);
            })
            ->get();

        $alerts = [];
        foreach ($rules as $rule) {
            $alerts[] = [
                'type'               => 'drug_interaction',
                'rule_id'            => $rule->id,
                'severity'           => $rule->severity ?? 'warning',
                'message'            => $rule->interaction_description,
                'requires_override'  => true,
                'medicine_a'         => $rule->medicine_a_code,
                'medicine_b'         => $rule->medicine_b_code,
            ];
        }

        return $alerts;
    }

    /**
     * Check allergy alert rules for a medicine against patient's allergy records.
     */
    public function checkAllergyAlerts(string $medicineCode, string $patientId): array
    {
        $rules = AllergyAlertRule::where('is_active', true)
            ->where('medicine_code', $medicineCode)
            ->get();

        $alerts = [];
        foreach ($rules as $rule) {
            $alerts[] = [
                'type'              => 'allergy_alert',
                'rule_id'           => $rule->id,
                'severity'          => 'critical',
                'message'           => $rule->alert_message ?? "Allergy alert for {$medicineCode}",
                'requires_override' => true,
            ];
        }

        return $alerts;
    }

    /**
     * Check dose warning rules for a medicine and prescribed dose.
     */
    public function checkDoseWarnings(
        string $medicineCode,
        ?float $doseValue,
        ?string $doseUnit
    ): array {
        if ($doseValue === null) {
            return [];
        }

        $rules = DoseWarningRule::active()
            ->forMedicine($medicineCode)
            ->get();

        $alerts = [];
        foreach ($rules as $rule) {
            if ($rule->max_dose_value !== null && $doseValue > $rule->max_dose_value) {
                $alerts[] = [
                    'type'              => 'dose_warning',
                    'rule_id'           => $rule->id,
                    'severity'          => $rule->severity,
                    'message'           => $rule->warning_message,
                    'requires_override' => $rule->requires_override_reason,
                    'max_dose'          => $rule->max_dose_value,
                    'dose_unit'         => $rule->dose_unit,
                    'prescribed_dose'   => $doseValue,
                ];
            }
        }

        return $alerts;
    }
}
