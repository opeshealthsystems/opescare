<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BloodAvailability extends Model
{
    use HasUuids;

    protected $table = 'blood_availability';

    protected $fillable = [
        'facility_id',
        'blood_group',
        'component_type',
        'units_available_range',
        'availability_status',
        'freshness_status',
        'emergency_contact',
        'last_updated_at',
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(CareFacility::class, 'facility_id');
    }
}
