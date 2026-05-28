<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsurancePlan extends Model
{
    use HasUuids, HasFactory;
    use \App\Traits\HasFacilityScope;

    protected $fillable = [
        'insurance_provider_id',
        'name',
        'plan_code',
        'plan_type',
        'requires_preauthorization',
        'cashless_available',
        'copay_percentage',
        'covered_services',
        'status',
    ];

    protected $casts = [
        'requires_preauthorization' => 'boolean',
        'cashless_available' => 'boolean',
        'copay_percentage' => 'decimal:2',
    ];

    public function provider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }

    public function policies()
    {
        return $this->hasMany(PatientInsurancePolicy::class);
    }
}
