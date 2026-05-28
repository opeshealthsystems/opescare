<?php

namespace App\Services\Billing;

use App\Models\InsurancePlan;
use App\Models\PatientInsurancePolicy;

/**
 * Co-pay and Co-insurance Calculation Service
 *
 * Calculates the patient's share of a bill based on their insurance plan.
 * Does not modify any existing invoice or payment record — returns calculations only.
 */
class CopayCalculationService
{
    /**
     * Calculate patient out-of-pocket cost for a service amount.
     *
     * @param  string  $patientInsurancePolicyId
     * @param  float   $serviceAmount  Total billed amount for the service
     * @param  string  $serviceType    consultation|laboratory|pharmacy|imaging|procedure|other
     * @return array   {copay, coinsurance_amount, patient_total, insurer_total, breakdown}
     */
    public function calculate(
        string $patientInsurancePolicyId,
        float  $serviceAmount,
        string $serviceType = 'consultation'
    ): array {
        $policy = PatientInsurancePolicy::with('insurancePlan')
            ->findOrFail($patientInsurancePolicyId);

        $plan = $policy->insurancePlan;

        if (!$plan) {
            // No plan — patient pays 100%
            return $this->fullPayResult($serviceAmount);
        }

        return $this->computeWithPlan($plan, $serviceAmount, $serviceType);
    }

    /**
     * Calculate from a plan ID directly (e.g. for eligibility pre-check).
     */
    public function calculateForPlan(
        string $insurancePlanId,
        float  $serviceAmount,
        string $serviceType = 'consultation'
    ): array {
        $plan = InsurancePlan::findOrFail($insurancePlanId);
        return $this->computeWithPlan($plan, $serviceAmount, $serviceType);
    }

    private function computeWithPlan(InsurancePlan $plan, float $amount, string $serviceType): array
    {
        // Flat co-pay (fixed amount per visit, regardless of service cost)
        $flatCopay = $this->getServiceCopay($plan, $serviceType);

        // Co-insurance: patient pays copay_percentage % of remaining after copay
        $afterCopay        = max(0, $amount - $flatCopay);
        $copayPct          = (float) ($plan->copay_percentage ?? 0);
        $coinsuranceAmount = round($afterCopay * ($copayPct / 100), 2);

        $patientTotal = round($flatCopay + $coinsuranceAmount, 2);
        $insurerTotal = round($amount - $patientTotal, 2);

        return [
            'service_amount'     => $amount,
            'copay'              => $flatCopay,
            'coinsurance_rate'   => $copayPct,
            'coinsurance_amount' => $coinsuranceAmount,
            'patient_total'      => $patientTotal,
            'insurer_total'      => max(0, $insurerTotal),
            'breakdown'          => [
                'service_type' => $serviceType,
                'plan_name'    => $plan->name ?? null,
                'has_copay'    => $flatCopay > 0,
                'has_coinsurance' => $copayPct > 0,
            ],
        ];
    }

    private function getServiceCopay(InsurancePlan $plan, string $serviceType): float
    {
        // Check for service-type-specific copay amounts stored as JSON
        // Falls back to generic copay_amount if present
        $specificCopays = $plan->service_copays ?? [];
        if (is_array($specificCopays) && isset($specificCopays[$serviceType])) {
            return (float) $specificCopays[$serviceType];
        }
        return (float) ($plan->copay_amount ?? 0);
    }

    private function fullPayResult(float $amount): array
    {
        return [
            'service_amount'     => $amount,
            'copay'              => 0.0,
            'coinsurance_rate'   => 100.0,
            'coinsurance_amount' => $amount,
            'patient_total'      => $amount,
            'insurer_total'      => 0.0,
            'breakdown'          => [
                'service_type'    => 'all',
                'plan_name'       => null,
                'has_copay'       => false,
                'has_coinsurance' => true,
                'note'            => 'No active insurance plan — patient pays full amount',
            ],
        ];
    }
}
