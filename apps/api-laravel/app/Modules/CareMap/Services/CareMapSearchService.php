<?php

namespace App\Modules\CareMap\Services;

use App\Models\CareFacility;
use Illuminate\Support\Facades\DB;

class CareMapSearchService
{
    /**
     * Search for care facilities based on latitude, longitude, distance, type, and status rules.
     */
    public function searchNearby(array $params)
    {
        $lat = $params['latitude'] ?? null;
        $lon = $params['longitude'] ?? null;
        $radius = $params['radius'] ?? 50; // in km
        $type = $params['facility_type'] ?? null;
        $insurance = $params['insurance_name'] ?? null;
        $emergency = $params['emergency'] ?? false;
        
        $query = CareFacility::query();

        // 1. Exclude suspended or inactive listings unless admin requests them
        $query->where('listing_status', 'active');

        // 2. Hide unverified listings if strict compliance is toggled
        if (!($params['include_unverified'] ?? true)) {
            $query->where('verification_status', '!=', 'unverified');
        }

        // 3. Filter by type
        if ($type) {
            $query->where('facility_type', $type);
        }

        // 4. Emergency Filter
        if ($emergency) {
            $query->where('facility_type', 'hospital')
                  ->where('emergency_contact', '!=', '');
        }

        // 5. Insurance Network mapping filter
        if ($insurance) {
            $query->whereHas('insurances', function ($q) use ($insurance) {
                $q->where('insurance_name', 'like', "%{$insurance}%")
                  ->where('status', 'active');
            });
        }

        // 6. Geospatial Haversine calculation if coordinates are provided
        if ($lat !== null && $lon !== null) {
            $query->whereNotNull('latitude')
                  ->whereNotNull('longitude');

            if (DB::getDriverName() === 'sqlite') {
                $results = $query->with(['services', 'hours', 'insurances'])->get();
                $filtered = [];
                foreach ($results as $facility) {
                    $distance = $this->calculatePhpDistance($lat, $lon, $facility->latitude, $facility->longitude);
                    if ($distance <= $radius) {
                        $facility->distance = $distance;
                        $filtered[] = $facility;
                    }
                }
                usort($filtered, function ($a, $b) {
                    return $a->distance <=> $b->distance;
                });
                return collect($filtered);
            }

            // Haversine formula
            $query->select('*')
                ->selectRaw(
                    "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                    [$lat, $lon, $lat]
                );

            $query->havingRaw("distance <= ?", [$radius]);
            $query->orderBy('distance');
        } else {
            $query->orderBy('facility_name');
        }

        return $query->with(['services', 'hours', 'insurances'])->get();
    }

    private function calculatePhpDistance($lat1, $lon1, $lat2, $lon2)
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

    /**
     * Emergency specific search - prioritizes 24/7 emergency centers
     */
    public function searchEmergency(array $params)
    {
        $params['emergency'] = true;
        return $this->searchNearby($params);
    }
}

