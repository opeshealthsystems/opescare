<?php

namespace App\Modules\CareMap\Services;

use App\Models\PharmacyStockAvailability;
use App\Models\CareFacility;

class PharmacyStockSearchService
{
    /**
     * Search for pharmacies having reported stock for a specific medicine
     */
    public function searchMedicine($medicineQuery, $lat = null, $lon = null, $radius = 50)
    {
        $query = PharmacyStockAvailability::where('medicine_name', 'like', "%{$medicineQuery}%")
            ->orWhere('generic_name', 'like', "%{$medicineQuery}%")
            ->orWhere('brand_name', 'like', "%{$medicineQuery}%");

        $stockMatches = $query->with('facility')->get();
        $facilities = [];

        foreach ($stockMatches as $match) {
            $facility = $match->facility;
            
            // Apply coordinates filter
            if ($lat !== null && $lon !== null && $facility->latitude && $facility->longitude) {
                // Haversine formula calculation
                $distance = $this->calculateDistance($lat, $lon, $facility->latitude, $facility->longitude);
                if ($distance > $radius) {
                    continue;
                }
                $facility->distance = $distance;
            } else {
                $facility->distance = null;
            }

            $match->unsetRelation('facility');
            $facility->matched_stock = $match;
            $facilities[] = $facility;
        }

        // Sort by distance if available, else by freshness
        usort($facilities, function ($a, $b) {
            if ($a->distance !== null && $b->distance !== null) {
                return $a->distance <=> $b->distance;
            }
            return strcmp($b->matched_stock->freshness_status, $a->matched_stock->freshness_status);
        });

        return $facilities;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
