<?php

namespace App\Modules\Inventory\Services;

use App\Models\BloodInventory;
use Illuminate\Support\Collection;

class BloodInventoryService
{
    public function list(string $facilityId, array $filters = []): Collection
    {
        $query = BloodInventory::where('facility_id', $facilityId)
            ->orderBy('blood_group')
            ->orderBy('component');

        if (!empty($filters['blood_group'])) {
            $query->where('blood_group', $filters['blood_group']);
        }
        if (!empty($filters['component'])) {
            $query->where('component', $filters['component']);
        }

        return $query->get();
    }

    public function upsertUnit(string $facilityId, array $data): BloodInventory
    {
        // Upsert by facility + blood_group + component
        $existing = BloodInventory::where('facility_id', $facilityId)
            ->where('blood_group', $data['blood_group'])
            ->where('component', $data['component'])
            ->first();

        if ($existing) {
            $existing->update(array_merge($data, [
                'last_stock_update' => now(),
            ]));
            return $existing;
        }

        return BloodInventory::create(array_merge($data, [
            'facility_id'       => $facilityId,
            'last_stock_update' => now(),
        ]));
    }

    public function adjustUnits(string $itemId, int $delta, string $direction = 'add'): BloodInventory
    {
        $item = BloodInventory::findOrFail($itemId);

        if ($direction === 'add') {
            $item->available_units = max(0, $item->available_units + $delta);
        } else {
            $item->available_units = max(0, $item->available_units - $delta);
        }

        $item->last_stock_update = now();
        $item->save();
        return $item;
    }

    public function setFlags(string $itemId, array $flags): BloodInventory
    {
        $allowed = ['is_expired', 'is_quarantined', 'is_unsafe'];
        $update  = array_intersect_key($flags, array_flip($allowed));
        $item    = BloodInventory::findOrFail($itemId);
        $item->update($update);
        return $item;
    }

    public function summary(string $facilityId): array
    {
        $items = BloodInventory::where('facility_id', $facilityId)->get();
        return [
            'total_units'    => $items->sum('available_units'),
            'groups_covered' => $items->where('available_units', '>', 0)->pluck('blood_group')->unique()->count(),
            'expired'        => $items->where('is_expired', true)->count(),
            'unsafe'         => $items->where('is_unsafe', true)->count(),
            'quarantined'    => $items->where('is_quarantined', true)->count(),
        ];
    }
}
