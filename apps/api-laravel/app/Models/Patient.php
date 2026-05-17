<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use \App\Traits\IsDemoRecord;
    use HasFactory, HasUuids;

    protected $fillable = [
        'health_id',
        'country_code',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'is_dob_estimated',
        'sex',
        'phone_number',
        'address',
        'emergency_contact',
        'identity_status',
        'verification_status',
        'verified_by_facility_id',
        'verified_at',
        'is_demo',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_dob_estimated' => 'boolean',
        'emergency_contact' => 'array',
        'verified_at' => 'datetime',
    ];

    public function identifiers()
    {
        return $this->hasMany(PatientIdentifier::class);
    }
}
