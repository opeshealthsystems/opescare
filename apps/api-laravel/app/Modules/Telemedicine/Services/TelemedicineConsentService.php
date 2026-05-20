<?php

namespace App\Modules\Telemedicine\Services;

use App\Models\Teleconsultation;
use App\Models\TelemedicineConsent;

/**
 * TelemedicineConsentService — Module 18 (Telemedicine)
 *
 * Manages informed consent lifecycle for telemedicine sessions.
 * Consent must be obtained before a session begins.
 * Patients may revoke consent before the session starts.
 */
class TelemedicineConsentService
{
    /**
     * Record patient consent for a teleconsultation.
     */
    public function grantConsent(
        Teleconsultation $consultation,
        string $patientId,
        string $consentMethod,
        string $consentTextVersion,
        ?string $witnessedBy = null
    ): TelemedicineConsent {
        return TelemedicineConsent::create([
            'teleconsultation_id'  => $consultation->id,
            'patient_id'           => $patientId,
            'consented'            => true,
            'consent_method'       => $consentMethod,
            'consent_text_version' => $consentTextVersion,
            'witnessed_by'         => $witnessedBy,
            'consented_at'         => now(),
        ]);
    }

    /**
     * Revoke consent before session starts.
     */
    public function revokeConsent(TelemedicineConsent $consent): void
    {
        $consent->revoke();
    }

    /**
     * Check whether valid consent exists for a consultation.
     */
    public function hasValidConsent(Teleconsultation $consultation): bool
    {
        return $consultation->consent !== null && $consultation->consent->isValid();
    }

    /**
     * Block consultation start if consent is missing.
     * Returns true if the session can proceed.
     */
    public function canProceed(Teleconsultation $consultation): bool
    {
        return $this->hasValidConsent($consultation);
    }
}
