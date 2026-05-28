<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory, HasUuids;
    use \App\Traits\HasFacilityScope;

    protected $fillable = [
        'prescription_id',
        'drug_name',
        'drug_code',
        'dose',
        'frequency',
        'route',
        'duration_days',
        'quantity',
        'status',
        'dispensed_at',
        'dispense_notes',
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function isDispensed(): bool
    {
        return $this->status === 'dispensed';
    }
}
