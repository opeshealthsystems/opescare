<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class Patient extends Model
{
    use \App\Traits\IsDemoRecord;
    use HasFactory, HasUuids;

    protected $fillable = [
        'health_id',
        'cnamgs_id',
        'national_id_number',
        'country_code',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'is_dob_estimated',
        'sex',
        'blood_group',
        'phone_number',
        'email',
        'address',
        'emergency_contact',
        'identity_status',
        'verification_status',
        'verified_by_facility_id',
        'verified_at',
        'pin_hash',
        'privacy_preferences',
        'cnamgs_verified_at',
        'national_id_type',
    ];

    // is_demo is intentionally excluded from $fillable.
    // Demo status is managed exclusively via forceFill() or direct DB assignment in migrations/seeders.
    // This prevents attackers from mass-assigning demo mode to real patient records via API.

    // PII ENCRYPTION NOTE:
    // - date_of_birth → encrypted cast (AES-256-CBC via APP_KEY); getDateOfBirthAttribute
    //   accessor restores Carbon behaviour so callers using ->toDateString(), ->year etc. work.
    // - phone_number → encrypted via setPhoneNumberAttribute mutator (NOT cast, to also
    //   maintain the phone_number_hash lookup column); findByPhone() enables DB lookup.
    // - address → encrypted cast (AES-256-CBC via APP_KEY)
    // - pin_hash → bcrypt hash, NOT encrypted cast (already a one-way hash, not recoverable)
    // - health_id → NOT encrypted (it is a searchable lookup key, not sensitive in isolation)
    protected $casts = [
        // date_of_birth and address are NOT listed here — their encryption is handled
        // entirely by dedicated get/set accessor+mutator pairs below, matching the
        // phone_number pattern.  Listing them in $casts causes Eloquent to call the
        // encrypted cast before old-style accessors (Laravel 13 priority order), which
        // means a DecryptException from a key-mismatch crashes before our try/catch runs.
        // Removing the cast lets our accessor/mutator own the full encrypt→decrypt cycle.
        'is_dob_estimated'   => 'boolean',
        'emergency_contact'  => 'array',
        'privacy_preferences' => 'array',
        'verified_at'        => 'datetime',
        'cnamgs_verified_at' => 'datetime',
    ];

    /**
     * Return date_of_birth as a Carbon instance.
     *
     * The 'encrypted' cast stores the value as an encrypted blob, but Eloquent calls
     * old-style accessors before applying casts.  We therefore manually decrypt here
     * and then parse to Carbon, so all callers using ->toDateString(), ->year,
     * ->diffInYears() etc. continue to work without modification.
     */
    public function getDateOfBirthAttribute(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        // If the value looks like an encrypted payload (base64-JSON), decrypt it first.
        $raw = (string) $value;
        if (str_starts_with($raw, 'eyJ')) {
            try {
                $raw = Crypt::decryptString($raw);
            } catch (\Throwable) {
                // Value was already plain text (e.g. during factory->make before save)
            }
        }

        return Carbon::parse($raw);
    }

    /**
     * Decrypt phone_number on read (manually encrypted via setPhoneNumberAttribute).
     */
    public function getPhoneNumberAttribute(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = (string) $value;
        if (str_starts_with($raw, 'eyJ')) {
            try {
                return Crypt::decryptString($raw);
            } catch (\Throwable) {
                // Value was plain text (e.g. pre-encryption legacy data)
                return $raw;
            }
        }

        return $raw;
    }

    /**
     * Decrypt address on read.
     *
     * The 'encrypted' cast throws DecryptException when the ciphertext was produced
     * with a different APP_KEY (e.g. a newly generated key in dev, or a key rotation
     * without re-encrypting old rows). Guard identically to phone_number: return the
     * raw value when decryption fails so the page renders rather than crashing.
     */
    public function getAddressAttribute(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = (string) $value;
        if (str_starts_with($raw, 'eyJ')) {
            try {
                return Crypt::decryptString($raw);
            } catch (\Throwable) {
                // Ciphertext unreadable with current APP_KEY — return null rather than crash.
                return null;
            }
        }

        return $raw;
    }

    /**
     * Encrypt address on write.
     */
    public function setAddressAttribute(?string $value): void
    {
        $this->attributes['address'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    /**
     * Return date_of_birth as a Carbon instance, handling encrypted storage.
     * Removed from $casts so this accessor fires before any encryption attempt.
     */
    public function setDateOfBirthAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['date_of_birth'] = null;
            return;
        }
        // Accept Carbon, DateTime, or date string — normalize to Y-m-d then encrypt
        $date = $value instanceof \Carbon\Carbon ? $value : \Carbon\Carbon::parse($value);
        $this->attributes['date_of_birth'] = Crypt::encryptString($date->format('Y-m-d'));
    }

    /**
     * When phone_number is set, automatically maintain phone_number_hash for DB lookups.
     * phone_number itself is encrypted (cannot be queried), so the hash enables O(1) lookup
     * by phone number (e.g. mobile auth login) without exposing the plaintext.
     *
     * Hash algorithm: HMAC-SHA256 keyed with APP_KEY (hex-encoded, 64 chars).
     */
    public function setPhoneNumberAttribute(?string $value): void
    {
        $this->attributes['phone_number'] = $value !== null
            ? Crypt::encryptString($value)
            : null;

        $this->attributes['phone_number_hash'] = $value !== null
            ? hash_hmac('sha256', $value, config('app.key'))
            : null;
    }

    /**
     * Compute an HMAC-SHA256 hash of a phone number for DB lookup.
     * Must match the algorithm used in setPhoneNumberAttribute().
     */
    public static function phoneHash(string $phone): string
    {
        return hash_hmac('sha256', $phone, config('app.key'));
    }

    /**
     * Find a patient by phone number using the keyed hash (efficient, encrypted-safe lookup).
     */
    public static function findByPhone(string $phone): ?static
    {
        return static::where('phone_number_hash', static::phoneHash($phone))->first();
    }

    public function identifiers()
    {
        return $this->hasMany(PatientIdentifier::class);
    }

    public function allergies()
    {
        return $this->hasMany(AllergyRecord::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function vitals()
    {
        return $this->hasMany(TriageVitalSign::class)->orderByDesc('created_at');
    }

    public function labResults()
    {
        return $this->hasMany(LabResult::class)->orderByDesc('created_at');
    }

    public function immunizations()
    {
        return $this->hasMany(ImmunizationRecord::class);
    }

    public function carePlans()
    {
        return $this->hasMany(CarePlan::class);
    }

    public function surveys()
    {
        return $this->hasMany(PatientSurvey::class);
    }
}
