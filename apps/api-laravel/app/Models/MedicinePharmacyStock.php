<?php

namespace App\Models;

use App\Enums\PharmacyStockStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Availability + price of one catalog Medicine at one pharmacy listing
 * (care_facilities row of facility_type = 'pharmacy').
 *
 * Reported stock, not dispensed-from stock: `stock_status` may legitimately be
 * Unknown and must never be rendered as available in that case.
 */
class MedicinePharmacyStock extends Model
{
    use HasUuids;

    protected $table = 'medicine_pharmacy_stocks';

    protected $fillable = [
        'medicine_id',
        'care_facility_id',
        'stock_status',
        'packs_available',
        'pack_size',
        'unit_price',
        'currency',
        'reservation_enabled',
        'source_system',
        'last_stocked_at',
        'last_reported_at',
    ];

    protected $casts = [
        'stock_status'        => PharmacyStockStatus::class,
        'packs_available'     => 'integer',
        'unit_price'          => 'float',
        'reservation_enabled' => 'boolean',
        'last_stocked_at'     => 'datetime',
        'last_reported_at'    => 'datetime',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function careFacility(): BelongsTo
    {
        return $this->belongsTo(CareFacility::class, 'care_facility_id');
    }

    /** Listings a patient could actually collect from today. */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereIn('stock_status', [
            PharmacyStockStatus::InStock->value,
            PharmacyStockStatus::LowStock->value,
        ]);
    }

    /** Whether this listing may back a new reservation right now. */
    public function isReservable(): bool
    {
        return $this->reservation_enabled && $this->stock_status->isReservable();
    }
}
