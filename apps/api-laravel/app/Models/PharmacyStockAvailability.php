<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PharmacyStockAvailability extends Model
{
    use HasUuids;
    use \App\Traits\HasFacilityScope;

    protected $table = 'pharmacy_stock_availability';

    protected $fillable = [
        'facility_id',
        'medicine_name',
        'generic_name',
        'brand_name',
        'strength',
        'form',
        'local_medicine_code',
        'gtin',
        'availability_status',
        'quantity_available_range',
        'price',
        'currency',
        'reservation_enabled',
        'source_system',
        'freshness_status',
        'last_updated_at',
    ];

    protected $casts = [
        'price' => 'float',
        'reservation_enabled' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(CareFacility::class, 'facility_id');
    }
}
