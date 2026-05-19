<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'visit_id',
        'prescribed_by',
        'status',
        'notes',
        'prescribed_at',
        'dispensed_at',
        'expires_at',
    ];

    protected $casts = [
        'prescribed_at' => 'datetime',
        'dispensed_at'  => 'datetime',
        'expires_at'    => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'dispensed'            => 'success',
            'active'               => 'info',
            'partially_dispensed'  => 'warning',
            'expired'              => 'default',
            'cancelled'            => 'default',
            default                => 'info',
        };
    }
}
