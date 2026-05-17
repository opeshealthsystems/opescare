<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CareFacility extends Model
{
    use HasUuids;

    protected $table = 'care_facilities';

    protected $fillable = [
        'partner_id',
        'organization_id',
        'facility_name',
        'facility_type',
        'ownership_type',
        'license_number',
        'license_status',
        'verification_status',
        'listing_status',
        'country_code',
        'region',
        'city',
        'address',
        'latitude',
        'longitude',
        'geocoding_accuracy',
        'phone_primary',
        'phone_secondary',
        'email',
        'website',
        'emergency_contact',
        'description',
        'logo_path',
        'cover_image_path',
        'integration_status',
        'last_verified_at',
        'last_profile_update_at',
        'last_availability_update_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'last_verified_at' => 'datetime',
        'last_profile_update_at' => 'datetime',
        'last_availability_update_at' => 'datetime',
    ];

    public function services()
    {
        return $this->hasMany(CareFacilityService::class, 'facility_id');
    }

    public function hours()
    {
        return $this->hasMany(CareFacilityHour::class, 'facility_id');
    }

    public function insurances()
    {
        return $this->hasMany(CareFacilityInsurance::class, 'facility_id');
    }

    public function pharmacyStock()
    {
        return $this->hasMany(PharmacyStockAvailability::class, 'facility_id');
    }

    public function labTests()
    {
        return $this->hasMany(LabTestAvailability::class, 'facility_id');
    }

    public function bloodAvailability()
    {
        return $this->hasMany(BloodAvailability::class, 'facility_id');
    }

    public function claims()
    {
        return $this->hasMany(FacilityClaim::class, 'facility_id');
    }

    public function reports()
    {
        return $this->hasMany(FacilityReport::class, 'facility_id');
    }

    public function audits()
    {
        return $this->hasMany(FacilityUpdateAudit::class, 'facility_id');
    }
}
