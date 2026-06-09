<?php

namespace App\Models;

use App\Services\FacilityCodeGenerator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CareFacility extends Model
{
    use HasUuids;

    protected $table = 'care_facilities';

    /**
     * Auto-generate a facility_code (OP-[REGION]-FID-[XXXX]) on creation.
     *
     * Three-layer uniqueness guarantee:
     *
     *  Layer 1+2 — FacilityCodeGenerator::generate() checks the DB inside a
     *              PostgreSQL advisory lock so concurrent requests cannot both
     *              claim the same code.
     *
     *  Layer 3   — If a race condition still somehow produces a duplicate
     *              (e.g. a direct DB insert bypassing Eloquent), the DB UNIQUE
     *              constraint throws UniqueConstraintViolationException. We catch
     *              it here, regenerate, and retry the save — the facility record
     *              is never lost due to a code collision.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->facility_code)) {
                $model->facility_code = FacilityCodeGenerator::generate(
                    $model->region ?? 'XX'
                );
            }
        });
    }

    /**
     * Override save() to catch facility_code unique constraint violations and
     * transparently regenerate the code before retrying — Layer 3 safety net.
     *
     * @param  array<string,mixed> $options
     */
    public function save(array $options = []): bool
    {
        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return parent::save($options);
            } catch (UniqueConstraintViolationException $e) {
                // Only retry if the violation is on facility_code.
                // All other unique violations (e.g. license_number) should propagate.
                if (! str_contains($e->getMessage(), 'facility_code')) {
                    throw $e;
                }

                if ($attempt === $maxAttempts) {
                    Log::error('CareFacility: could not assign a unique facility_code after retries', [
                        'region'   => $this->region,
                        'attempts' => $maxAttempts,
                    ]);
                    throw $e;
                }

                Log::warning('CareFacility: facility_code collision on save — regenerating', [
                    'attempt'         => $attempt,
                    'colliding_code'  => $this->facility_code,
                    'region'          => $this->region,
                ]);

                // Regenerate and retry
                $this->facility_code = FacilityCodeGenerator::generate(
                    $this->region ?? 'XX'
                );
            }
        }

        return false; // unreachable but satisfies return type
    }

    protected $fillable = [
        'facility_code',
        'partner_id',
        'organization_id',
        'facility_id',
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
