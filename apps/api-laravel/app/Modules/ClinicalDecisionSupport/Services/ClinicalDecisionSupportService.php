<?php

namespace App\Modules\ClinicalDecisionSupport\Services;

use App\Models\AlertOverride;
use App\Models\AllergyAlertRule;
use App\Models\ClinicalAlert;
use App\Models\ClinicalReminder;
use App\Models\DrugInteractionRule;
use App\Models\LabAlertRule;
use App\Models\Patient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Clinical Decision Support Service
 *
 * IMPORTANT: Clinical alerts are decision-support tools only.
 * They do not replace professional clinical judgment.
 *
 * This service generates advisory alerts only. It never prevents a clinician
 * from proceeding — overrides are always permitted with a documented reason
 * (except where facility policy sets non-overridable critical alerts).
 */
class ClinicalDecisionSupportService
{
    /**
     * Run all applicable CDSS checks for a patient in a visit context.
     * Returns array of newly fired alert IDs.
     */
    public function runChecksForVisit(
        string $facilityId,
        string $patientId,
        string $visitId,
        array  $context,   // {drug_codes:[], lab_results:[], allergies:[], is_pregnant:bool, dob:string}
        string $triggeredBy = 'system'
    ): array {
        $fired = [];

        $patient = Patient::find($patientId);
        if (!$patient) return [];

        // 1. Allergy checks
        $allergyAlerts = $this->checkAllergies(
            $facilityId, $patientId, $visitId,
            $context['drug_codes'] ?? [],
            $context['allergies'] ?? [],
            $triggeredBy
        );
        $fired = array_merge($fired, $allergyAlerts);

        // 2. Drug interaction checks
        $interactionAlerts = $this->checkDrugInteractions(
            $facilityId, $patientId, $visitId,
            $context['drug_codes'] ?? [],
            $triggeredBy
        );
        $fired = array_merge($fired, $interactionAlerts);

        // 3. Duplicate prescription checks
        $dupeAlerts = $this->checkDuplicatePrescriptions(
            $facilityId, $patientId, $visitId,
            $context['drug_codes'] ?? [],
            $triggeredBy
        );
        $fired = array_merge($fired, $dupeAlerts);

        // 4. Lab value checks
        $labAlerts = $this->checkLabValues(
            $facilityId, $patientId, $visitId,
            $context['lab_results'] ?? [],
            $triggeredBy
        );
        $fired = array_merge($fired, $labAlerts);

        // 5. Age-based warnings
        if ($patient->date_of_birth ?? null) {
            $ageAlerts = $this->checkAgeWarnings(
                $facilityId, $patientId, $visitId,
                $patient, $context['drug_codes'] ?? [], $triggeredBy
            );
            $fired = array_merge($fired, $ageAlerts);
        }

        // 6. Pregnancy warnings
        if ($context['is_pregnant'] ?? false) {
            $pregnancyAlerts = $this->checkPregnancyWarnings(
                $facilityId, $patientId, $visitId,
                $context['drug_codes'] ?? [], $triggeredBy
            );
            $fired = array_merge($fired, $pregnancyAlerts);
        }

        return $fired;
    }

    /**
     * Check patient allergies against prescribed drugs.
     */
    public function checkAllergies(
        string $facilityId, string $patientId, string $visitId,
        array $drugCodes, array $patientAllergens, string $triggeredBy
    ): array {
        if (empty($drugCodes) || empty($patientAllergens)) return [];

        $fired = [];
        foreach ($drugCodes as $drugCode) {
            $rules = AllergyAlertRule::findForDrug($drugCode);
            foreach ($rules as $rule) {
                // Check direct allergen match
                $directMatch = in_array($rule->allergen_code, $patientAllergens, true);
                // Check cross-reactivity group match
                $crossMatch = $rule->cross_reactivity_group
                    && in_array($rule->cross_reactivity_group, $patientAllergens, true);

                if ($directMatch || $crossMatch) {
                    // Avoid duplicate active alerts for same drug/allergen in this visit
                    $exists = ClinicalAlert::where('visit_id', $visitId)
                        ->where('alert_type', 'allergy')
                        ->where('status', 'active')
                        ->whereJsonContains('context_data->drug_code', $drugCode)
                        ->whereJsonContains('context_data->allergen_code', $rule->allergen_code)
                        ->exists();

                    if (!$exists) {
                        $alert = ClinicalAlert::create([
                            'facility_id'    => $facilityId,
                            'patient_id'     => $patientId,
                            'visit_id'       => $visitId,
                            'rule_id'        => null,
                            'alert_type'     => 'allergy',
                            'severity'       => $rule->severity,
                            'alert_message'  => $rule->alert_message,
                            'recommendation' => 'Consider alternative medication or confirm allergy status before prescribing.',
                            'context_data'   => [
                                'drug_code'    => $drugCode,
                                'drug_name'    => $rule->drug_name,
                                'allergen_code'=> $rule->allergen_code,
                                'allergen_name'=> $rule->allergen_name,
                                'cross_match'  => $crossMatch,
                            ],
                            'status'         => 'active',
                            'triggered_by'   => $triggeredBy,
                            'triggered_at'   => now(),
                        ]);
                        $fired[] = $alert->id;
                    }
                }
            }
        }
        return $fired;
    }

    /**
     * Check all prescribed drug combinations for interactions.
     */
    public function checkDrugInteractions(
        string $facilityId, string $patientId, string $visitId,
        array $drugCodes, string $triggeredBy
    ): array {
        if (count($drugCodes) < 2) return [];

        $fired = [];
        $checked = [];

        foreach ($drugCodes as $i => $codeA) {
            foreach ($drugCodes as $j => $codeB) {
                if ($j <= $i) continue; // avoid duplicate pairs
                $pairKey = implode('|', array_unique([$codeA, $codeB]));
                if (isset($checked[$pairKey])) continue;
                $checked[$pairKey] = true;

                $rule = DrugInteractionRule::checkPair($codeA, $codeB);
                if ($rule) {
                    $severity = match($rule->severity) {
                        'contraindicated', 'major' => 'critical',
                        'moderate' => 'warning',
                        default    => 'info',
                    };

                    $exists = ClinicalAlert::where('visit_id', $visitId)
                        ->where('alert_type', 'drug_interaction')
                        ->where('status', 'active')
                        ->whereJsonContains('context_data->drug_a_code', $codeA)
                        ->whereJsonContains('context_data->drug_b_code', $codeB)
                        ->exists();

                    if (!$exists) {
                        $alert = ClinicalAlert::create([
                            'facility_id'    => $facilityId,
                            'patient_id'     => $patientId,
                            'visit_id'       => $visitId,
                            'alert_type'     => 'drug_interaction',
                            'severity'       => $severity,
                            'alert_message'  => "Drug interaction: {$rule->drug_a_name} + {$rule->drug_b_name}. " . $rule->interaction_description,
                            'recommendation' => $rule->management,
                            'context_data'   => [
                                'drug_a_code' => $codeA,
                                'drug_a_name' => $rule->drug_a_name,
                                'drug_b_code' => $codeB,
                                'drug_b_name' => $rule->drug_b_name,
                                'interaction_severity' => $rule->severity,
                                'clinical_effect' => $rule->clinical_effect,
                            ],
                            'status'         => 'active',
                            'triggered_by'   => $triggeredBy,
                            'triggered_at'   => now(),
                        ]);
                        $fired[] = $alert->id;
                    }
                }
            }
        }
        return $fired;
    }

    /**
     * Check for duplicate prescriptions (same drug already prescribed in active visits).
     */
    public function checkDuplicatePrescriptions(
        string $facilityId, string $patientId, string $visitId,
        array $drugCodes, string $triggeredBy
    ): array {
        if (empty($drugCodes)) return [];

        $fired = [];

        // Check if patient has active prescriptions for the same drug
        foreach ($drugCodes as $drugCode) {
            $duplicate = DB::table('prescription_items')
                ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
                ->where('prescriptions.patient_id', $patientId)
                ->where('prescriptions.status', 'active')
                ->whereNot('prescriptions.visit_id', $visitId)
                ->where(function ($q) use ($drugCode) {
                    $q->where('prescription_items.drug_code', $drugCode)
                      ->orWhere('prescription_items.drug_name', 'like', '%' . $drugCode . '%');
                })
                ->first();

            if ($duplicate) {
                $exists = ClinicalAlert::where('visit_id', $visitId)
                    ->where('alert_type', 'duplicate_rx')
                    ->where('status', 'active')
                    ->whereJsonContains('context_data->drug_code', $drugCode)
                    ->exists();

                if (!$exists) {
                    $alert = ClinicalAlert::create([
                        'facility_id'    => $facilityId,
                        'patient_id'     => $patientId,
                        'visit_id'       => $visitId,
                        'alert_type'     => 'duplicate_rx',
                        'severity'       => 'warning',
                        'alert_message'  => "Duplicate prescription: patient has an active prescription for '{$drugCode}'.",
                        'recommendation' => 'Review current prescriptions. Consider stopping the existing prescription or adjusting dosing.',
                        'context_data'   => ['drug_code' => $drugCode],
                        'status'         => 'active',
                        'triggered_by'   => $triggeredBy,
                        'triggered_at'   => now(),
                    ]);
                    $fired[] = $alert->id;
                }
            }
        }
        return $fired;
    }

    /**
     * Check lab result values against reference ranges.
     * $labResults = [{test_code, value, unit}]
     */
    public function checkLabValues(
        string $facilityId, string $patientId, string $visitId,
        array $labResults, string $triggeredBy
    ): array {
        if (empty($labResults)) return [];

        $fired = [];

        foreach ($labResults as $result) {
            $testCode = $result['test_code'] ?? null;
            $value    = isset($result['value']) ? (float)$result['value'] : null;
            if (!$testCode || $value === null) continue;

            $rule = LabAlertRule::where('lab_test_code', $testCode)->where('is_active', true)->first();
            if (!$rule) continue;

            $severity = $rule->evaluateValue($value);
            if (!$severity) continue;

            $alertType = $severity === 'critical' ? 'critical_lab' : 'abnormal_lab';

            $exists = ClinicalAlert::where('visit_id', $visitId)
                ->where('alert_type', $alertType)
                ->where('status', 'active')
                ->whereJsonContains('context_data->test_code', $testCode)
                ->exists();

            if (!$exists) {
                $direction = $value < ($rule->normal_low ?? PHP_FLOAT_MAX) ? 'LOW' : 'HIGH';
                $refRange  = ($rule->normal_low ?? '?') . ' – ' . ($rule->normal_high ?? '?') . ' ' . $rule->unit;

                $alert = ClinicalAlert::create([
                    'facility_id'    => $facilityId,
                    'patient_id'     => $patientId,
                    'visit_id'       => $visitId,
                    'alert_type'     => $alertType,
                    'severity'       => $severity,
                    'alert_message'  => "{$rule->lab_test_name}: {$value} {$rule->unit} ({$direction}) — Reference: {$refRange}",
                    'recommendation' => $severity === 'critical'
                        ? 'Critical value — immediate clinical review required.'
                        : 'Abnormal value — review in clinical context.',
                    'context_data'   => [
                        'test_code'  => $testCode,
                        'test_name'  => $rule->lab_test_name,
                        'value'      => $value,
                        'unit'       => $rule->unit,
                        'direction'  => $direction,
                        'ref_low'    => $rule->normal_low,
                        'ref_high'   => $rule->normal_high,
                    ],
                    'status'         => 'active',
                    'triggered_by'   => $triggeredBy,
                    'triggered_at'   => now(),
                ]);
                $fired[] = $alert->id;
            }
        }
        return $fired;
    }

    /**
     * Age-based drug warnings (paediatric/geriatric).
     */
    public function checkAgeWarnings(
        string $facilityId, string $patientId, string $visitId,
        Patient $patient, array $drugCodes, string $triggeredBy
    ): array {
        if (empty($drugCodes) || !$patient->date_of_birth) return [];

        $ageYears = (int) now()->diffInYears($patient->date_of_birth);
        $fired    = [];

        // Geriatric warnings (≥ 65 years): Beers Criteria drugs
        $beersDrugs = ['DIAZEPAM', 'AMITRIPTYLINE', 'DIPHENHYDRAMINE', 'OXYBUTYNIN', 'CHLORPHENIRAMINE'];

        if ($ageYears >= 65) {
            foreach ($drugCodes as $code) {
                if (in_array(strtoupper($code), $beersDrugs, true)) {
                    $exists = ClinicalAlert::where('visit_id', $visitId)
                        ->where('alert_type', 'age_warning')
                        ->where('status', 'active')
                        ->whereJsonContains('context_data->drug_code', $code)
                        ->exists();

                    if (!$exists) {
                        $alert = ClinicalAlert::create([
                            'facility_id'    => $facilityId,
                            'patient_id'     => $patientId,
                            'visit_id'       => $visitId,
                            'alert_type'     => 'age_warning',
                            'severity'       => 'warning',
                            'alert_message'  => "Potentially inappropriate in elderly: {$code} (Beers Criteria). Patient age: {$ageYears} years.",
                            'recommendation' => 'Consider safer alternatives. If use is necessary, use lowest effective dose with increased monitoring.',
                            'context_data'   => ['drug_code' => $code, 'patient_age' => $ageYears, 'criteria' => 'Beers'],
                            'status'         => 'active',
                            'triggered_by'   => $triggeredBy,
                            'triggered_at'   => now(),
                        ]);
                        $fired[] = $alert->id;
                    }
                }
            }
        }

        // Paediatric warnings (< 12 years): aspirin, tetracycline
        $paedContraindicated = ['ASPIRIN', 'TETRACYCLINE', 'DOXYCYCLINE', 'FLUOROQUINOLONE', 'CIPROFLOXACIN'];
        if ($ageYears < 12) {
            foreach ($drugCodes as $code) {
                if (in_array(strtoupper($code), $paedContraindicated, true)) {
                    $exists = ClinicalAlert::where('visit_id', $visitId)
                        ->where('alert_type', 'age_warning')
                        ->where('status', 'active')
                        ->whereJsonContains('context_data->drug_code', $code)
                        ->exists();
                    if (!$exists) {
                        $alert = ClinicalAlert::create([
                            'facility_id'    => $facilityId,
                            'patient_id'     => $patientId,
                            'visit_id'       => $visitId,
                            'alert_type'     => 'age_warning',
                            'severity'       => 'critical',
                            'alert_message'  => "Contraindicated in children: {$code}. Patient age: {$ageYears} years.",
                            'recommendation' => 'Do not prescribe this drug to paediatric patients without specialist consultation.',
                            'context_data'   => ['drug_code' => $code, 'patient_age' => $ageYears, 'criteria' => 'Paediatric'],
                            'status'         => 'active',
                            'triggered_by'   => $triggeredBy,
                            'triggered_at'   => now(),
                        ]);
                        $fired[] = $alert->id;
                    }
                }
            }
        }

        return $fired;
    }

    /**
     * Pregnancy drug warnings.
     */
    public function checkPregnancyWarnings(
        string $facilityId, string $patientId, string $visitId,
        array $drugCodes, string $triggeredBy
    ): array {
        if (empty($drugCodes)) return [];

        // FDA Category X / known teratogens
        $pregnancyContraindicated = ['WARFARIN', 'ISOTRETINOIN', 'THALIDOMIDE', 'METHOTREXATE', 'VALPROATE', 'CARBAMAZEPINE', 'NSAID', 'IBUPROFEN', 'NAPROXEN'];
        $fired = [];

        foreach ($drugCodes as $code) {
            if (in_array(strtoupper($code), $pregnancyContraindicated, true)) {
                $exists = ClinicalAlert::where('visit_id', $visitId)
                    ->where('alert_type', 'pregnancy_warning')
                    ->where('status', 'active')
                    ->whereJsonContains('context_data->drug_code', $code)
                    ->exists();

                if (!$exists) {
                    $alert = ClinicalAlert::create([
                        'facility_id'    => $facilityId,
                        'patient_id'     => $patientId,
                        'visit_id'       => $visitId,
                        'alert_type'     => 'pregnancy_warning',
                        'severity'       => 'critical',
                        'alert_message'  => "Pregnancy risk: {$code} is contraindicated or requires caution in pregnant patients.",
                        'recommendation' => 'Confirm pregnancy status. If confirmed, consider alternative medications. Consult obstetrics if necessary.',
                        'context_data'   => ['drug_code' => $code, 'flag' => 'pregnancy_category_x'],
                        'status'         => 'active',
                        'triggered_by'   => $triggeredBy,
                        'triggered_at'   => now(),
                    ]);
                    $fired[] = $alert->id;
                }
            }
        }
        return $fired;
    }

    /**
     * Acknowledge an alert (clinician has reviewed it).
     */
    public function acknowledgeAlert(string $alertId, string $staffId): ClinicalAlert
    {
        $alert = ClinicalAlert::findOrFail($alertId);

        if ($alert->status === 'active') {
            $alert->update([
                'status'           => 'acknowledged',
                'acknowledged_by'  => $staffId,
                'acknowledged_at'  => now(),
            ]);
        }

        return $alert->fresh();
    }

    /**
     * Override an alert with a required documented reason.
     */
    public function overrideAlert(
        string $alertId,
        string $staffId,
        string $reason,
        string $category = 'other'
    ): AlertOverride {
        /** @var ClinicalAlert $alert */
        $alert = ClinicalAlert::findOrFail($alertId);

        DB::transaction(function () use ($alert, $staffId, $reason, $category) {
            $alert->update(['status' => 'overridden']);

            AlertOverride::create([
                'alert_id'          => $alert->id,
                'patient_id'        => $alert->patient_id,
                'visit_id'          => $alert->visit_id,
                'overridden_by'     => $staffId,
                'override_reason'   => $reason,
                'override_category' => $category,
                'overridden_at'     => now(),
            ]);
        });

        return AlertOverride::where('alert_id', $alertId)->latest('overridden_at')->first();
    }

    /**
     * Get active alerts for a patient visit.
     */
    public function getActiveAlertsForVisit(string $visitId): Collection
    {
        return ClinicalAlert::where('visit_id', $visitId)
            ->where('status', 'active')
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->orderBy('triggered_at')
            ->get();
    }

    /**
     * Get all alerts for a patient (history).
     */
    public function getAlertsForPatient(string $patientId, int $limit = 50): Collection
    {
        return ClinicalAlert::where('patient_id', $patientId)
            ->with('latestOverride')
            ->orderBy('triggered_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Dismiss an info-level alert.
     */
    public function dismissAlert(string $alertId, string $staffId): void
    {
        ClinicalAlert::where('id', $alertId)->where('severity', 'info')->update([
            'status'          => 'dismissed',
            'acknowledged_by' => $staffId,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Get unacknowledged critical alert count for facility dashboard.
     */
    public function getCriticalUnacknowledgedCount(string $facilityId): int
    {
        return ClinicalAlert::where('facility_id', $facilityId)
            ->where('severity', 'critical')
            ->where('status', 'active')
            ->where('triggered_at', '>=', now()->subHours(24))
            ->count();
    }
}
