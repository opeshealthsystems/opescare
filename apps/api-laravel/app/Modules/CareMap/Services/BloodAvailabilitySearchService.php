<?php

namespace App\Modules\CareMap\Services;

use App\Models\BloodAvailability;

class BloodAvailabilitySearchService
{
    /**
     * Search for blood banks/hospitals with reported blood availability
     */
    public function searchBlood($bloodGroup, $componentType = 'whole_blood', $lat = null, $lon = null, $radius = 50)
    {
        $query = BloodAvailability::where('blood_group', $bloodGroup)
            ->where('component_type', $componentType)
            ->where('availability_status', 'available')
            ->with('facility');

        $matches = $query->get();
        $facilities = [];

        foreach ($matches as $match) {
            $facility = $match->facility;
            
            if ($lat !== null && $lon !== null && $facility->latitude && $facility->longitude) {
                $distance = $this->calculateDistance($lat, $lon, $facility->latitude, $facility->longitude);
                if ($distance > $radius) {
                    continue;
                }
                $facility->distance = $distance;
            } else {
                $facility->distance = null;
            }

            $match->unsetRelation('facility');
            $facility->matched_blood = $match;
            $facilities[] = $facility;
        }

        usort($facilities, function ($a, $b) {
            if ($a->distance !== null && $b->distance !== null) {
                return $a->distance <=> $b->distance;
            }
            return strcmp($b->matched_blood->freshness_status, $a->matched_blood->freshness_status);
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
