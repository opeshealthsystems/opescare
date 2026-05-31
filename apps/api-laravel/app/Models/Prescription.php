<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory, HasUuids;
    use \App\Traits\HasFacilityScope;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'visit_id',
        'prescribed_by',
        'status',
        'notes',
        'dispensing_pharmacy_name',
        'dispensing_pharmacy_address',
        'dispensing_pharmacy_phone',
        'dispensing_pharmacy_fax',
        'pharmacy_routing_status',
        'pharmacy_routing_sent_at',
        'pharmacy_confirmed_at',
        'prescribed_at',
        'dispensed_at',
        'expires_at',
        'pharmacy_route_id',
    ];

    protected $casts = [
        'prescribed_at'            => 'datetime',
        'dispensed_at'             => 'datetime',
        'expires_at'               => 'datetime',
        'pharmacy_routing_sent_at' => 'datetime',
        'pharmacy_confirmed_at'    => 'datetime',
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
