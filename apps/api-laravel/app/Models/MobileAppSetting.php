<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MobileAppSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id',
        'push_appointments',
        'push_lab_results',
        'push_prescriptions',
        'push_billing',
        'push_consent_requests',
        'preferred_language',
        'preferred_theme',
        'biometric_login_enabled',
        'extra_preferences',
    ];

    protected $casts = [
        'push_appointments'    => 'boolean',
        'push_lab_results'     => 'boolean',
        'push_prescriptions'   => 'boolean',
        'push_billing'         => 'boolean',
        'push_consent_requests'=> 'boolean',
        'biometric_login_enabled' => 'boolean',
        'extra_preferences'    => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get or create settings for a patient.
     */
    public static function forPatient(string $patientId): self
    {
        return self::firstOrCreate(['patient_id' => $patientId]);
    }
}
